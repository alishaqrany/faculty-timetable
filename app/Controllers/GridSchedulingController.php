<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/Timetable.php';
require_once APP_ROOT . '/app/Models/MemberCourse.php';
require_once APP_ROOT . '/app/Models/Classroom.php';
require_once APP_ROOT . '/app/Models/Session.php';
require_once APP_ROOT . '/app/Models/Member.php';
require_once APP_ROOT . '/app/Models/Department.php';
require_once APP_ROOT . '/app/Models/Level.php';
require_once APP_ROOT . '/app/Models/AcademicYear.php';
require_once APP_ROOT . '/app/Models/Semester.php';
require_once APP_ROOT . '/app/Services/SchedulingService.php';
require_once APP_ROOT . '/app/Services/PriorityService.php';
require_once APP_ROOT . '/app/Services/AuditService.php';

use App\Models\{Timetable, MemberCourse, Classroom, Session, Member, Department, Level, AcademicYear, Semester};
use App\Services\{SchedulingService, PriorityService, AuditService};

class GridSchedulingController extends \Controller
{
    public function index(): void
    {
        $this->authorize('scheduling.view');

        $user = $this->authUser();
        $memberId = $this->session->memberId();
        $isAdmin = ($user['role_slug'] ?? '') === 'admin';
        $isDeptHead = ($user['role_slug'] ?? '') === 'dept_head';
        $canAdmin = can('scheduling.admin');
        $canSchedule = $isAdmin || ($memberId && PriorityService::canMemberRegister($memberId));

        $departments = Department::active();
        $levels = Level::active();
        $members = Member::active();
        $classrooms = Classroom::active();
        $sessions = Session::active();
        $academicYears = AcademicYear::where(['is_active' => 1], 'start_date DESC');
        $semesters = Semester::allWithYear();

        // Build day order
        $days = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];

        // Get unique sessions (time slots)
        $timeSlots = \Database::getInstance()->fetchAll(
            "SELECT MIN(session_id) AS session_id,
                    session_name, start_time, end_time
             FROM sessions
             WHERE is_active = 1
             GROUP BY session_name, start_time, end_time
             ORDER BY start_time"
        );

        // User's own courses for self-scheduling
        $myCourses = $memberId ? MemberCourse::forMember($memberId) : [];

        $this->render('grid-scheduling.index', [
            'departments'   => $departments,
            'levels'        => $levels,
            'members'       => $members,
            'classrooms'    => $classrooms,
            'sessions'      => $sessions,
            'academicYears' => $academicYears,
            'semesters'     => $semesters,
            'days'          => $days,
            'timeSlots'     => $timeSlots,
            'myCourses'     => $myCourses,
            'canSchedule'   => $canSchedule,
            'canAdmin'      => $canAdmin,
            'isAdmin'       => $isAdmin,
            'memberId'      => $memberId,
        ]);
    }

    /**
     * AJAX: Get grid data for a department/level combination.
     */
    public function gridData(): void
    {
        $this->authorize('scheduling.view');

        $filters = [
            'department_id'    => $this->request->input('department_id'),
            'level_id'         => $this->request->input('level_id'),
            'member_id'        => $this->request->input('member_id'),
            'classroom_id'     => $this->request->input('classroom_id'),
            'academic_year_id' => $this->request->input('academic_year_id'),
            'semester_id'      => $this->request->input('semester_id'),
        ];

        $hasFilter = !empty(array_filter($filters));
        if (!$hasFilter) {
            $this->json(['success' => true, 'hasFilter' => false, 'grid' => [], 'entries' => []]);
        }

        $entries = Timetable::allWithDetails($filters);

        // Build grid: day -> slot_key -> entries[]
        $grid = [];
        foreach ($entries as $entry) {
            $slotKey = ($entry['session_name'] ?? '') . '|' . ($entry['start_time'] ?? '') . '|' . ($entry['end_time'] ?? '');
            $day = $entry['day'] ?? '';
            if ($day && $slotKey !== '||') {
                $grid[$day][$slotKey][] = [
                    'timetable_id'   => $entry['timetable_id'],
                    'subject_name'   => $entry['subject_name'],
                    'member_name'    => $entry['member_name'],
                    'section_name'   => $entry['section_name'],
                    'classroom_name' => $entry['classroom_name'],
                    'member_id'      => $entry['member_id'],
                    'session_id'     => $entry['session_id'] ?? null,
                ];
            }
        }

        $this->json([
            'success'   => true,
            'hasFilter' => $hasFilter,
            'grid'      => $grid,
            'entries'   => $entries,
            'count'     => count($entries),
        ]);
    }

    /**
     * Store a new scheduling entry (AJAX).
     */
    public function store(): void
    {
        $this->authorize('scheduling.manage');
        $this->validateCsrf();

        $data = $this->request->validate([
            'member_course_id' => 'required|integer',
            'classroom_id'     => 'required|integer',
            'session_id'       => 'required|integer',
        ]);

        if ($data === false) {
            $this->json(['success' => false, 'message' => 'بيانات غير صالحة'], 422);
        }

        $user = $this->authUser();
        $isAdmin = ($user['role_slug'] ?? '') === 'admin';
        $memberId = $this->session->memberId();

        // Verify ownership or admin privilege
        $mc = MemberCourse::find((int)$data['member_course_id']);
        if (!$mc) {
            $this->json(['success' => false, 'message' => 'تكليف غير موجود'], 404);
        }

        $canAdmin = can('scheduling.admin');
        if (!$canAdmin && (!$memberId || (int)$mc['member_id'] !== (int)$memberId)) {
            $this->json(['success' => false, 'message' => 'غير مسموح'], 403);
        }

        if (!$isAdmin && !$canAdmin && $memberId && !PriorityService::canMemberRegister($memberId)) {
            $this->json(['success' => false, 'message' => 'ليس دورك حالياً في نظام الأولوية'], 403);
        }

        // Atomic: check conflicts + insert in single transaction
        $data['created_by'] = $this->session->userId();
        $data['status'] = 'مسودة';

        $result = SchedulingService::storeEntry($data);

        if (!$result['success']) {
            $this->json([
                'success'   => false,
                'message'   => 'تعارض: ' . implode(' | ', $result['conflicts']),
                'conflicts' => $result['conflicts'],
            ], 409);
        }

        AuditService::log('GRID_CREATE', 'timetable', $result['timetable_id'], null, $data);

        $this->json(['success' => true, 'message' => 'تم إضافة المحاضرة بنجاح ✓', 'timetable_id' => $result['timetable_id']]);
    }

    /**
     * Delete a scheduling entry (AJAX).
     */
    public function destroy(string $id): void
    {
        $this->authorize('scheduling.manage');
        $this->validateCsrf();

        $entry = \Database::getInstance()->fetch(
            "SELECT t.*, mc.member_id FROM timetable t
             JOIN member_courses mc ON t.member_course_id = mc.member_course_id
             WHERE t.timetable_id = ?",
            [(int)$id]
        );

        if (!$entry) {
            $this->json(['success' => false, 'message' => 'غير موجود'], 404);
        }

        $user = $this->authUser();
        $isAdmin = ($user['role_slug'] ?? '') === 'admin';
        $canAdmin = can('scheduling.admin');
        $memberId = $this->session->memberId();

        if (!$isAdmin && !$canAdmin && (int)$entry['member_id'] !== (int)$memberId) {
            $this->json(['success' => false, 'message' => 'غير مسموح'], 403);
        }

        Timetable::destroy((int)$id);
        AuditService::log('GRID_DELETE', 'timetable', (int)$id, $entry);

        $this->json(['success' => true, 'message' => 'تم حذف المحاضرة بنجاح ✓']);
    }
}

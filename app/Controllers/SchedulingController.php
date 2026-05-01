<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/Timetable.php';
require_once APP_ROOT . '/app/Models/MemberCourse.php';
require_once APP_ROOT . '/app/Models/Classroom.php';
require_once APP_ROOT . '/app/Models/Session.php';
require_once APP_ROOT . '/app/Models/AcademicYear.php';
require_once APP_ROOT . '/app/Models/Semester.php';
require_once APP_ROOT . '/app/Services/SchedulingService.php';
require_once APP_ROOT . '/app/Services/PriorityService.php';
require_once APP_ROOT . '/app/Services/AuditService.php';
require_once APP_ROOT . '/app/Services/NotificationService.php';

use App\Models\{Timetable, MemberCourse, Classroom, Session, AcademicYear, Semester};
use App\Services\{SchedulingService, AuditService, NotificationService, PriorityService};

class SchedulingController extends \Controller
{
    private function canManageEntry(int $timetableId): ?array
    {
        return \Database::getInstance()->fetch(
            "SELECT t.*, mc.member_id FROM timetable t
             JOIN member_courses mc ON t.member_course_id = mc.member_course_id
             WHERE t.timetable_id = ?",
            [$timetableId]
        );
    }

    public function index(): void
    {
        $this->authorize('scheduling.view');

        $user = $this->authUser();
        $memberId = $this->session->memberId();
        $isAdmin = ($user['role_slug'] ?? '') === 'admin';

        // Check if user can schedule via priority system
        $canSchedule = $isAdmin || ($memberId && PriorityService::canMemberRegister($memberId));

        // Get priority state for the view
        $priorityState = PriorityService::getCurrentState();
        $academicYears = AcademicYear::where(['is_active' => 1], 'start_date DESC');
        $semesters = Semester::allWithYear();
        $filters = [
            'academic_year_id' => $this->request->input('academic_year_id'),
            'semester_id'      => $this->request->input('semester_id'),
        ];

        // Get user's courses
        $myCourses = $memberId ? MemberCourse::forMember($memberId) : [];
        if (!empty($filters['academic_year_id'])) {
            $myCourses = array_values(array_filter($myCourses, static function ($course) use ($filters) {
                return (int)($course['academic_year_id'] ?? 0) === (int)$filters['academic_year_id'];
            }));
        }
        if (!empty($filters['semester_id'])) {
            $myCourses = array_values(array_filter($myCourses, static function ($course) use ($filters) {
                return (int)($course['semester_id'] ?? 0) === (int)$filters['semester_id'];
            }));
        }
        $classrooms = Classroom::active();
        $sessions = Session::active();

        // Get user's scheduled entries
        $myEntries = [];
        if ($memberId) {
            $myEntries = Timetable::allWithDetails([
                'member_id'        => $memberId,
                'academic_year_id' => $filters['academic_year_id'],
                'semester_id'      => $filters['semester_id'],
            ]);
        }

        $this->render('scheduling.index', [
            'canSchedule'   => $canSchedule,
            'myCourses'     => $myCourses,
            'classrooms'    => $classrooms,
            'sessions'      => $sessions,
            'myEntries'     => $myEntries,
            'priorityState' => $priorityState,
            'isAdmin'       => $isAdmin,
            'academicYears' => $academicYears,
            'semesters'     => $semesters,
            'filters'       => $filters,
        ]);
    }

    public function store(): void
    {
        $this->authorize('scheduling.manage');
        $this->validateCsrf();

        $user = $this->authUser();
        $isAdmin = ($user['role_slug'] ?? '') === 'admin';
        $memberId = $this->session->memberId();

        if (!$isAdmin && $memberId && !\App\Services\PriorityService::canMemberRegister($memberId)) {
            $this->redirect('/scheduling', 'غير مسموح لك بالتسجيل حالياً بناءً على نظام الأولوية', 'error');
        }

        $data = $this->request->validate([
            'member_course_id' => 'required|integer',
            'classroom_id'     => 'required|integer',
            'session_id'       => 'required|integer',
        ]);
        if ($data === false) $this->redirect('/scheduling', 'يرجى تصحيح الأخطاء', 'error');

        $memberId = $this->session->memberId();
        $user = $this->authUser();
        $isAdmin = ($user['role_slug'] ?? '') === 'admin';

        // Backend priority check — prevent bypass via direct POST
        if (!$isAdmin && $memberId && !PriorityService::canMemberRegister($memberId)) {
            $this->redirect('/scheduling', 'ليس دورك حالياً في نظام الأولوية. لا يمكنك التسجيل الآن.', 'error');
        }

        // Verify ownership
        $mc = MemberCourse::find((int)$data['member_course_id']);
        if (!$mc || $mc['member_id'] != $memberId) {
            $this->redirect('/scheduling', 'غير مسموح: هذا التكليف لا يخصك', 'error');
        }

        // Check conflicts
        $conflicts = SchedulingService::checkConflicts(
            (int)$data['classroom_id'],
            (int)$data['session_id'],
            (int)$data['member_course_id']
        );

        if ($conflicts) {
            $msg = 'تعارض في التسكين: ' . implode(' | ', $conflicts);
            $this->redirect('/scheduling', $msg, 'error');
        }

        $data['created_by'] = $this->session->userId();
        $data['status'] = 'مسودة';
        $id = Timetable::create($data);

        AuditService::log('CREATE', 'timetable', $id, null, $data);

        $this->redirect('/scheduling', 'تم إضافة المحاضرة في الجدول بنجاح ✓');
    }

    public function edit(string $id): void
    {
        $this->authorize('scheduling.manage');

        $entry = \Database::getInstance()->fetch(
            "SELECT t.*, mc.member_id FROM timetable t
             JOIN member_courses mc ON t.member_course_id = mc.member_course_id
             WHERE t.timetable_id = ?",
            [(int)$id]
        );
        if (!$entry) $this->redirect('/scheduling', 'الإدخال غير موجود', 'error');

        // Verify ownership (unless admin)
        $user = $this->authUser();
        if (($user['role_slug'] ?? '') !== 'admin' && $entry['member_id'] != $this->session->memberId()) {
            $this->redirect('/scheduling', 'غير مسموح', 'error');
        }

        $memberId = $entry['member_id'];
        $myCourses = MemberCourse::forMember($memberId);
        $classrooms = Classroom::active();
        $sessions = Session::active();

        $this->render('scheduling.edit', [
            'entry'      => $entry,
            'myCourses'  => $myCourses,
            'classrooms' => $classrooms,
            'sessions'   => $sessions,
        ]);
    }

    public function update(string $id): void
    {
        $this->authorize('scheduling.manage');
        $this->validateCsrf();

        $entry = $this->canManageEntry((int)$id);
        if (!$entry) $this->redirect('/scheduling', 'الإدخال غير موجود', 'error');

        $user = $this->authUser();
        $isAdmin = ($user['role_slug'] ?? '') === 'admin';
        $memberId = $this->session->memberId();

        if (!$isAdmin && (int)$entry['member_id'] !== (int)$memberId) {
            $this->redirect('/scheduling', 'غير مسموح', 'error');
        }

        if (!$isAdmin && $memberId && !PriorityService::canMemberRegister($memberId)) {
            $this->redirect('/scheduling', 'ليس دورك حالياً في نظام الأولوية. لا يمكنك تعديل المواعيد الآن.', 'error');
        }

        $data = $this->request->validate([
            'member_course_id' => 'required|integer',
            'classroom_id'     => 'required|integer',
            'session_id'       => 'required|integer',
        ]);
        if ($data === false) $this->redirect("/scheduling/{$id}/edit", 'يرجى تصحيح الأخطاء', 'error');

        if (!$isAdmin) {
            $mc = MemberCourse::find((int)$data['member_course_id']);
            if (!$mc || (int)$mc['member_id'] !== (int)$this->session->memberId()) {
                $this->redirect('/scheduling', 'غير مسموح: هذا التكليف لا يخصك', 'error');
            }
        }

        // Check conflicts (excluding current entry)
        $conflicts = SchedulingService::checkConflicts(
            (int)$data['classroom_id'],
            (int)$data['session_id'],
            (int)$data['member_course_id'],
            (int)$id
        );

        if ($conflicts) {
            $msg = 'تعارض في التسكين: ' . implode(' | ', $conflicts);
            $this->redirect("/scheduling/{$id}/edit", $msg, 'error');
        }

        Timetable::updateById((int)$id, $data);
        AuditService::log('UPDATE', 'timetable', (int)$id, $entry, $data);

        $this->redirect('/scheduling', 'تم تحديث المحاضرة في الجدول بنجاح ✓');
    }

    public function destroy(string $id): void
    {
        $this->authorize('scheduling.manage');
        $this->validateCsrf();

        $entry = $this->canManageEntry((int)$id);
        if (!$entry) $this->redirect('/scheduling', 'الإدخال غير موجود', 'error');

        $user = $this->authUser();
        $isAdmin = ($user['role_slug'] ?? '') === 'admin';
        $memberId = $this->session->memberId();

        if (!$isAdmin && (int)$entry['member_id'] !== (int)$memberId) {
            $this->redirect('/scheduling', 'غير مسموح', 'error');
        }

        if (!$isAdmin && $memberId && !PriorityService::canMemberRegister($memberId)) {
            $this->redirect('/scheduling', 'ليس دورك حالياً في نظام الأولوية. لا يمكنك حذف المواعيد الآن.', 'error');
        }

        Timetable::destroy((int)$id);
        AuditService::log('DELETE', 'timetable', (int)$id, $entry);

        $this->redirect('/scheduling', 'تم حذف المحاضرة من الجدول بنجاح ✓');
    }

    /**
     * AJAX: Return filtered scheduling data as JSON.
     */
    public function ajaxFilter(): void
    {
        $this->authorize('scheduling.view');

        $memberId = $this->session->memberId();
        $filters = [
            'academic_year_id' => $this->request->input('academic_year_id'),
            'semester_id'      => $this->request->input('semester_id'),
        ];

        // Get user's courses filtered
        $myCourses = $memberId ? MemberCourse::forMember($memberId) : [];
        if (!empty($filters['academic_year_id'])) {
            $myCourses = array_values(array_filter($myCourses, static function ($course) use ($filters) {
                return (int)($course['academic_year_id'] ?? 0) === (int)$filters['academic_year_id'];
            }));
        }
        if (!empty($filters['semester_id'])) {
            $myCourses = array_values(array_filter($myCourses, static function ($course) use ($filters) {
                return (int)($course['semester_id'] ?? 0) === (int)$filters['semester_id'];
            }));
        }

        // Get user's scheduled entries filtered
        $myEntries = [];
        if ($memberId) {
            $myEntries = Timetable::allWithDetails([
                'member_id'        => $memberId,
                'academic_year_id' => $filters['academic_year_id'],
                'semester_id'      => $filters['semester_id'],
            ]);
        }

        $this->json([
            'success'   => true,
            'count'     => count($myEntries),
            'myCourses' => $myCourses,
            'myEntries' => $myEntries,
            'filters'   => $filters,
        ]);
    }

    public function passRole(): void
    {
        $this->authorize('scheduling.pass_role');
        $this->validateCsrf();

        $memberId = $this->session->memberId();
        if (!$memberId) $this->redirect('/scheduling', 'خطأ في الجلسة', 'error');

        $mode = PriorityService::getMode();

        if ($mode === PriorityService::MODE_DISABLED) {
            // Legacy mode — use old ranking-based pass
            $success = SchedulingService::passRole($memberId);
        } else {
            // Priority system is active — revoke current user's direct registration
            // and let admin advance groups/depts from the priority panel
            PriorityService::revokeDirectRegistration($memberId);
            $success = true;
        }

        if ($success) {
            AuditService::log('PASS_ROLE', 'scheduling', $memberId);
            $this->redirect('/scheduling', 'تم تمرير الدور بنجاح ✓');
        } else {
            $this->redirect('/scheduling', 'حدث خطأ أثناء تمرير الدور', 'error');
        }
    }
}

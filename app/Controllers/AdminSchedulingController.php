<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/Timetable.php';
require_once APP_ROOT . '/app/Models/MemberCourse.php';
require_once APP_ROOT . '/app/Models/Classroom.php';
require_once APP_ROOT . '/app/Models/Session.php';
require_once APP_ROOT . '/app/Models/Member.php';
require_once APP_ROOT . '/app/Models/Department.php';
require_once APP_ROOT . '/app/Models/AcademicYear.php';
require_once APP_ROOT . '/app/Models/Semester.php';
require_once APP_ROOT . '/app/Services/SchedulingService.php';
require_once APP_ROOT . '/app/Services/AuditService.php';
require_once APP_ROOT . '/app/Services/NotificationService.php';

use App\Models\{Timetable, MemberCourse, Classroom, Session, Member, Department, AcademicYear, Semester};
use App\Services\{SchedulingService, AuditService, NotificationService};

class AdminSchedulingController extends \Controller
{
    /**
     * Get the list of members this admin can manage.
     */
    private function getAllowedMembers(): array
    {
        $user = $this->authUser();
        $roleSlug = $user['role_slug'] ?? '';

        if ($roleSlug === 'admin') {
            return Member::allWithDetails('fm.member_name ASC');
        }

        if ($roleSlug === 'dept_head' && !empty($user['department_id'])) {
            return \Database::getInstance()->fetchAll(
                "SELECT fm.*, d.department_name
                 FROM faculty_members fm
                 LEFT JOIN departments d ON fm.department_id = d.department_id
                 WHERE fm.department_id = ? AND fm.is_active = 1
                 ORDER BY fm.member_name ASC",
                [$user['department_id']]
            );
        }

        return [];
    }

    /**
     * Check if admin can manage a specific member.
     */
    private function canManageMember(int $memberId): bool
    {
        $user = $this->authUser();
        $roleSlug = $user['role_slug'] ?? '';

        if ($roleSlug === 'admin') return true;

        if ($roleSlug === 'dept_head' && !empty($user['department_id'])) {
            $member = Member::find($memberId);
            return $member && (int)$member['department_id'] === (int)$user['department_id'];
        }

        return false;
    }

    public function index(): void
    {
        $this->authorize('scheduling.admin');

        $user = $this->authUser();
        $departments = Department::active();
        $members = $this->getAllowedMembers();
        $classrooms = Classroom::active();
        $sessions = Session::active();
        $academicYears = AcademicYear::where(['is_active' => 1], 'start_date DESC');
        $semesters = Semester::allWithYear();

        $this->render('admin-scheduling.index', [
            'departments'   => $departments,
            'members'       => $members,
            'classrooms'    => $classrooms,
            'sessions'      => $sessions,
            'academicYears' => $academicYears,
            'semesters'     => $semesters,
            'isAdmin'       => ($user['role_slug'] ?? '') === 'admin',
        ]);
    }

    /**
     * AJAX: Get courses for a specific member.
     */
    public function memberCourses(string $id): void
    {
        $this->authorize('scheduling.admin');
        $memberId = (int)$id;

        if (!$this->canManageMember($memberId)) {
            $this->json(['success' => false, 'message' => 'غير مسموح'], 403);
        }

        $courses = MemberCourse::forMember($memberId);

        $this->json([
            'success' => true,
            'courses' => $courses,
        ]);
    }

    /**
     * AJAX: Get scheduled entries for a specific member.
     */
    public function memberEntries(string $id): void
    {
        $this->authorize('scheduling.admin');
        $memberId = (int)$id;

        if (!$this->canManageMember($memberId)) {
            $this->json(['success' => false, 'message' => 'غير مسموح'], 403);
        }

        $entries = Timetable::allWithDetails(['member_id' => $memberId]);

        $this->json([
            'success' => true,
            'entries' => $entries,
        ]);
    }

    /**
     * Store a single scheduling entry on behalf of a member.
     */
    public function store(): void
    {
        $this->authorize('scheduling.admin');
        $this->validateCsrf();

        $data = $this->request->validate([
            'member_course_id' => 'required|integer',
            'classroom_id'     => 'required|integer',
            'session_id'       => 'required|integer',
        ]);
        if ($data === false) {
            if ($this->request->isAjax()) {
                $this->json(['success' => false, 'message' => 'بيانات غير صالحة'], 422);
            }
            $this->redirect('/admin-scheduling', 'يرجى تصحيح الأخطاء', 'error');
        }

        // Verify this member_course belongs to a member the admin can manage
        $mc = MemberCourse::find((int)$data['member_course_id']);
        if (!$mc) {
            $msg = 'تكليف التدريس غير موجود';
            if ($this->request->isAjax()) $this->json(['success' => false, 'message' => $msg], 404);
            $this->redirect('/admin-scheduling', $msg, 'error');
        }

        if (!$this->canManageMember((int)$mc['member_id'])) {
            $msg = 'غير مسموح بالتسكين لهذا العضو';
            if ($this->request->isAjax()) $this->json(['success' => false, 'message' => $msg], 403);
            $this->redirect('/admin-scheduling', $msg, 'error');
        }

        // Check conflicts
        $conflicts = SchedulingService::checkConflicts(
            (int)$data['classroom_id'],
            (int)$data['session_id'],
            (int)$data['member_course_id']
        );

        if ($conflicts) {
            $msg = 'تعارض في التسكين: ' . implode(' | ', $conflicts);
            if ($this->request->isAjax()) $this->json(['success' => false, 'message' => $msg, 'conflicts' => $conflicts], 409);
            $this->redirect('/admin-scheduling', $msg, 'error');
        }

        $data['created_by'] = $this->session->userId();
        $data['status'] = 'مسودة';
        $id = Timetable::create($data);

        AuditService::log('ADMIN_CREATE', 'timetable', $id, null, array_merge($data, ['on_behalf_of' => $mc['member_id']]));

        // Notify the member
        $memberUser = \Database::getInstance()->fetch(
            "SELECT u.id FROM users u WHERE u.member_id = ?",
            [$mc['member_id']]
        );
        if ($memberUser) {
            NotificationService::send(
                $memberUser['id'],
                'تم تسكين محاضرة لك',
                'قام المسؤول بتسكين محاضرة في الجدول نيابة عنك.',
                'info',
                '/scheduling'
            );
        }

        if ($this->request->isAjax()) {
            $this->json(['success' => true, 'message' => 'تم إضافة المحاضرة بنجاح ✓', 'timetable_id' => $id]);
        }
        $this->redirect('/admin-scheduling', 'تم إضافة المحاضرة في الجدول بنجاح ✓');
    }

    /**
     * Bulk store multiple entries.
     */
    public function bulkStore(): void
    {
        $this->authorize('scheduling.admin');
        $this->validateCsrf();

        $entries = $this->request->input('entries', []);
        if (!is_array($entries) || empty($entries)) {
            $this->json(['success' => false, 'message' => 'لا توجد بيانات للتسكين'], 422);
        }

        $results = ['added' => 0, 'failed' => 0, 'errors' => []];

        foreach ($entries as $entry) {
            $mcId = (int)($entry['member_course_id'] ?? 0);
            $classroomId = (int)($entry['classroom_id'] ?? 0);
            $sessionId = (int)($entry['session_id'] ?? 0);

            if (!$mcId || !$classroomId || !$sessionId) {
                $results['failed']++;
                $results['errors'][] = 'بيانات ناقصة';
                continue;
            }

            $mc = MemberCourse::find($mcId);
            if (!$mc || !$this->canManageMember((int)$mc['member_id'])) {
                $results['failed']++;
                $results['errors'][] = 'غير مسموح بالتسكين لهذا العضو';
                continue;
            }

            $conflicts = SchedulingService::checkConflicts($classroomId, $sessionId, $mcId);
            if ($conflicts) {
                $results['failed']++;
                $results['errors'][] = implode(' | ', $conflicts);
                continue;
            }

            $data = [
                'member_course_id' => $mcId,
                'classroom_id' => $classroomId,
                'session_id' => $sessionId,
                'created_by' => $this->session->userId(),
                'status' => 'مسودة',
            ];
            Timetable::create($data);
            $results['added']++;
        }

        AuditService::log('ADMIN_BULK_CREATE', 'timetable', null, null, $results);

        $this->json([
            'success' => true,
            'message' => "تم تسكين {$results['added']} محاضرة بنجاح" . ($results['failed'] > 0 ? " ({$results['failed']} فشلت)" : ''),
            'results' => $results,
        ]);
    }

    /**
     * Delete a scheduling entry.
     */
    public function destroy(string $id): void
    {
        $this->authorize('scheduling.admin');
        $this->validateCsrf();

        $entry = \Database::getInstance()->fetch(
            "SELECT t.*, mc.member_id FROM timetable t
             JOIN member_courses mc ON t.member_course_id = mc.member_course_id
             WHERE t.timetable_id = ?",
            [(int)$id]
        );

        if (!$entry) {
            if ($this->request->isAjax()) $this->json(['success' => false, 'message' => 'غير موجود'], 404);
            $this->redirect('/admin-scheduling', 'الإدخال غير موجود', 'error');
        }

        if (!$this->canManageMember((int)$entry['member_id'])) {
            if ($this->request->isAjax()) $this->json(['success' => false, 'message' => 'غير مسموح'], 403);
            $this->redirect('/admin-scheduling', 'غير مسموح', 'error');
        }

        Timetable::destroy((int)$id);
        AuditService::log('ADMIN_DELETE', 'timetable', (int)$id, $entry);

        if ($this->request->isAjax()) {
            $this->json(['success' => true, 'message' => 'تم حذف المحاضرة بنجاح ✓']);
        }
        $this->redirect('/admin-scheduling', 'تم حذف المحاضرة بنجاح ✓');
    }
}

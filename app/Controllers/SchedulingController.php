<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/Timetable.php';
require_once APP_ROOT . '/app/Models/MemberCourse.php';
require_once APP_ROOT . '/app/Models/Classroom.php';
require_once APP_ROOT . '/app/Models/Session.php';
require_once APP_ROOT . '/app/Services/SchedulingService.php';
require_once APP_ROOT . '/app/Services/AuditService.php';
require_once APP_ROOT . '/app/Services/NotificationService.php';

use App\Models\{Timetable, MemberCourse, Classroom, Session};
use App\Services\{SchedulingService, AuditService, NotificationService};

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

        // Check if user has scheduling turn
        $userRecord = \Database::getInstance()->fetch(
            "SELECT registration_status FROM users WHERE id = ?",
            [$this->session->userId()]
        );
        $canSchedule = ($userRecord['registration_status'] ?? 0) == 1;

        // Get user's courses
        $myCourses = $memberId ? MemberCourse::forMember($memberId) : [];
        $classrooms = Classroom::active();
        $sessions = Session::active();

        // Get user's scheduled entries
        $myEntries = [];
        if ($memberId) {
            $myEntries = Timetable::allWithDetails(['member_id' => $memberId]);
        }

        $this->render('scheduling.index', [
            'canSchedule' => $canSchedule,
            'myCourses'   => $myCourses,
            'classrooms'  => $classrooms,
            'sessions'    => $sessions,
            'myEntries'   => $myEntries,
        ]);
    }

    public function store(): void
    {
        $this->authorize('scheduling.manage');
        $this->validateCsrf();

        $data = $this->request->validate([
            'member_course_id' => 'required|integer',
            'classroom_id'     => 'required|integer',
            'session_id'       => 'required|integer',
        ]);
        if ($data === false) $this->redirect('/scheduling', 'يرجى تصحيح الأخطاء', 'error');

        // Verify ownership
        $mc = MemberCourse::find((int)$data['member_course_id']);
        if (!$mc || $mc['member_id'] != $this->session->memberId()) {
            $this->redirect('/scheduling', 'غير مسموح: هذا التكليف لا يخصك', 'error');
        }

        // Check conflicts
        $conflicts = SchedulingService::checkConflicts(
            (int)$data['classroom_id'],
            (int)$data['session_id'],
            (int)$data['member_course_id']
        );

        if ($conflicts) {
            $msg = 'تعارض في الجدولة: ' . implode(' | ', $conflicts);
            $this->redirect('/scheduling', $msg, 'error');
        }

        $data['created_by'] = $this->session->userId();
        $data['status'] = 'مسودة';
        $id = Timetable::create($data);

        AuditService::log('CREATE', 'timetable', $id, null, $data);

        $this->redirect('/scheduling', 'تم إضافة الحصة في الجدول بنجاح ✓');
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
        if (!$isAdmin && (int)$entry['member_id'] !== (int)$this->session->memberId()) {
            $this->redirect('/scheduling', 'غير مسموح', 'error');
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
            $msg = 'تعارض في الجدولة: ' . implode(' | ', $conflicts);
            $this->redirect("/scheduling/{$id}/edit", $msg, 'error');
        }

        Timetable::updateById((int)$id, $data);
        AuditService::log('UPDATE', 'timetable', (int)$id, $entry, $data);

        $this->redirect('/scheduling', 'تم تحديث الحصة في الجدول بنجاح ✓');
    }

    public function destroy(string $id): void
    {
        $this->authorize('scheduling.manage');
        $this->validateCsrf();

        $entry = $this->canManageEntry((int)$id);
        if (!$entry) $this->redirect('/scheduling', 'الإدخال غير موجود', 'error');

        $user = $this->authUser();
        if (($user['role_slug'] ?? '') !== 'admin' && (int)$entry['member_id'] !== (int)$this->session->memberId()) {
            $this->redirect('/scheduling', 'غير مسموح', 'error');
        }

        Timetable::destroy((int)$id);
        AuditService::log('DELETE', 'timetable', (int)$id, $entry);

        $this->redirect('/scheduling', 'تم حذف الحصة من الجدول بنجاح ✓');
    }

    public function passRole(): void
    {
        $this->authorize('scheduling.pass_role');
        $this->validateCsrf();

        $memberId = $this->session->memberId();
        if (!$memberId) $this->redirect('/scheduling', 'خطأ في الجلسة', 'error');

        $success = SchedulingService::passRole($memberId);
        if ($success) {
            AuditService::log('PASS_ROLE', 'scheduling', $memberId);
            $this->redirect('/scheduling', 'تم تمرير الدور بنجاح ✓');
        } else {
            $this->redirect('/scheduling', 'حدث خطأ أثناء تمرير الدور', 'error');
        }
    }
}

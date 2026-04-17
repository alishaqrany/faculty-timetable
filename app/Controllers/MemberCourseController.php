<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/MemberCourse.php';
require_once APP_ROOT . '/app/Models/Member.php';
require_once APP_ROOT . '/app/Models/Subject.php';
require_once APP_ROOT . '/app/Models/Section.php';
require_once APP_ROOT . '/app/Services/AuditService.php';

use App\Models\{MemberCourse, Member, Subject, Section};
use App\Services\AuditService;

class MemberCourseController extends \Controller
{
    public function index(): void
    {
        $this->authorize('membercourses.view');
        $courses = MemberCourse::allWithDetails();
        $this->render('membercourses.index', ['courses' => $courses]);
    }

    public function create(): void
    {
        $this->authorize('membercourses.create');
        $members = Member::active();
        $subjects = Subject::allWithDetails();
        $sections = Section::allWithDetails();
        $this->render('membercourses.create', [
            'members'  => $members,
            'subjects' => $subjects,
            'sections' => $sections,
        ]);
    }

    public function store(): void
    {
        $this->authorize('membercourses.create');
        $this->validateCsrf();

        $data = $this->request->validate([
            'member_id'  => 'required|integer',
            'subject_id' => 'required|integer',
            'section_id' => 'required|integer',
        ]);
        if ($data === false) $this->redirect('/member-courses/create', 'يرجى تصحيح الأخطاء', 'error');

        $id = MemberCourse::create($data);
        AuditService::log('CREATE', 'member_courses', $id, null, $data);

        $this->redirect('/member-courses', 'تم إنشاء تكليف التدريس بنجاح ✓');
    }

    public function edit(string $id): void
    {
        $this->authorize('membercourses.edit');
        $course = MemberCourse::findWithDetails((int)$id);
        if (!$course) $this->redirect('/member-courses', 'التكليف غير موجود', 'error');

        $members = Member::active();
        $subjects = Subject::allWithDetails();
        $sections = Section::allWithDetails();
        $this->render('membercourses.edit', [
            'course'   => $course,
            'members'  => $members,
            'subjects' => $subjects,
            'sections' => $sections,
        ]);
    }

    public function update(string $id): void
    {
        $this->authorize('membercourses.edit');
        $this->validateCsrf();

        $course = MemberCourse::find((int)$id);
        if (!$course) $this->redirect('/member-courses', 'التكليف غير موجود', 'error');

        $data = $this->request->validate([
            'member_id'  => 'required|integer',
            'subject_id' => 'required|integer',
            'section_id' => 'required|integer',
        ]);
        if ($data === false) $this->redirect("/member-courses/{$id}/edit", 'يرجى تصحيح الأخطاء', 'error');

        MemberCourse::updateById((int)$id, $data);
        AuditService::log('UPDATE', 'member_courses', (int)$id, $course, $data);

        $this->redirect('/member-courses', 'تم تحديث تكليف التدريس بنجاح ✓');
    }

    public function destroy(string $id): void
    {
        $this->authorize('membercourses.delete');
        $this->validateCsrf();

        $course = MemberCourse::find((int)$id);
        if (!$course) $this->redirect('/member-courses', 'التكليف غير موجود', 'error');

        MemberCourse::destroy((int)$id);
        AuditService::log('DELETE', 'member_courses', (int)$id, $course);

        $this->redirect('/member-courses', 'تم حذف تكليف التدريس بنجاح ✓');
    }
}

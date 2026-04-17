<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/MemberCourse.php';
require_once APP_ROOT . '/app/Models/Member.php';
require_once APP_ROOT . '/app/Models/Subject.php';
require_once APP_ROOT . '/app/Models/Division.php';
require_once APP_ROOT . '/app/Models/Section.php';
require_once APP_ROOT . '/app/Services/AuditService.php';

use App\Models\{MemberCourse, Member, Subject, Division, Section};
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
        // Get divisions for theory assignments and sections for practical
        $divisions = Division::allWithDetails();
        $sections = Section::allWithDetails();
        $this->render('membercourses.create', [
            'members'   => $members,
            'subjects'  => $subjects,
            'divisions' => $divisions,
            'sections'  => $sections,
        ]);
    }

    public function store(): void
    {
        $this->authorize('membercourses.create');
        $this->validateCsrf();

        $data = [
            'member_id'  => (int)$this->request->input('member_id'),
            'subject_id' => (int)$this->request->input('subject_id'),
            'assignment_type' => $this->request->input('assignment_type', 'نظري'),
        ];

        // Validate member and subject
        if (!$data['member_id'] || !$data['subject_id']) {
            $this->redirect('/member-courses/create', 'يرجى اختيار عضو هيئة التدريس والمقرر', 'error');
        }

        // Assignment: theory → division_id, practical → section_id
        if ($data['assignment_type'] === 'نظري') {
            $divisionIds = $_POST['division_id'] ?? [];
            if (!is_array($divisionIds)) {
                $divisionIds = [$divisionIds];
            }
            $divisionIds = array_filter(array_map('intval', $divisionIds));

            if (empty($divisionIds)) {
                $this->redirect('/member-courses/create', 'يرجى اختيار الشعبة للتدريس النظري', 'error');
            }

            $data['division_id'] = reset($divisionIds);
            $data['is_shared'] = count($divisionIds) > 1 ? 1 : 0;
            $data['section_id'] = null;
        } else {
            $data['section_id'] = (int)$this->request->input('section_id');
            $data['division_id'] = null;
            $data['is_shared'] = 0;
            if (!$data['section_id']) {
                $this->redirect('/member-courses/create', 'يرجى اختيار السكشن للتدريس العملي', 'error');
            }
        }

        $id = MemberCourse::create($data);
        
        // Save division relations
        if ($data['assignment_type'] === 'نظري' && !empty($divisionIds)) {
            foreach ($divisionIds as $divId) {
                \Database::getInstance()->execute(
                    "INSERT INTO member_course_shared_divisions (member_course_id, division_id) VALUES (?, ?)",
                    [$id, $divId]
                );
            }
        }

        AuditService::log('CREATE', 'member_courses', $id, null, $data);

        $this->redirect('/member-courses', 'تم إنشاء تكليف التدريس بنجاح ✓');
    }

    public function edit(string $id): void
    {
        $this->authorize('membercourses.edit');
        $course = MemberCourse::findWithDetails((int)$id);
        if (!$course) $this->redirect('/member-courses', 'التكليف غير موجود', 'error');

        $sharedDivisions = [];
        if (($course['is_shared'] ?? 0) == 1) {
            $sharedRecs = \Database::getInstance()->fetchAll("SELECT division_id FROM member_course_shared_divisions WHERE member_course_id = ?", [$id]);
            $sharedDivisions = array_column($sharedRecs, 'division_id');
        } elseif ($course['division_id']) {
            $sharedDivisions = [$course['division_id']];
        }

        $members = Member::active();
        $subjects = Subject::allWithDetails();
        $divisions = Division::allWithDetails();
        $sections = Section::allWithDetails();
        $this->render('membercourses.edit', [
            'course'    => $course,
            'sharedDivisions' => $sharedDivisions,
            'members'   => $members,
            'subjects'  => $subjects,
            'divisions' => $divisions,
            'sections'  => $sections,
        ]);
    }

    public function update(string $id): void
    {
        $this->authorize('membercourses.edit');
        $this->validateCsrf();

        $course = MemberCourse::find((int)$id);
        if (!$course) $this->redirect('/member-courses', 'التكليف غير موجود', 'error');

        $data = [
            'member_id'  => (int)$this->request->input('member_id'),
            'subject_id' => (int)$this->request->input('subject_id'),
            'assignment_type' => $this->request->input('assignment_type', 'نظري'),
        ];

        if (!$data['member_id'] || !$data['subject_id']) {
            $this->redirect("/member-courses/{$id}/edit", 'يرجى اختيار عضو هيئة التدريس والمقرر', 'error');
        }

        // Assignment: theory → division_id, practical → section_id
        if ($data['assignment_type'] === 'نظري') {
            $divisionIds = $_POST['division_id'] ?? [];
            if (!is_array($divisionIds)) {
                $divisionIds = [$divisionIds];
            }
            $divisionIds = array_filter(array_map('intval', $divisionIds));

            if (empty($divisionIds)) {
                $this->redirect("/member-courses/{$id}/edit", 'يرجى اختيار الشعبة للتدريس النظري', 'error');
            }

            $data['division_id'] = reset($divisionIds);
            $data['is_shared'] = count($divisionIds) > 1 ? 1 : 0;
            $data['section_id'] = null;
        } else {
            $data['section_id'] = (int)$this->request->input('section_id');
            $data['division_id'] = null;
            $data['is_shared'] = 0;
            if (!$data['section_id']) {
                $this->redirect("/member-courses/{$id}/edit", 'يرجى اختيار السكشن للتدريس العملي', 'error');
            }
        }

        MemberCourse::updateById((int)$id, $data);

        // Update shared divisions
        \Database::getInstance()->execute("DELETE FROM member_course_shared_divisions WHERE member_course_id = ?", [$id]);
        if ($data['assignment_type'] === 'نظري' && isset($divisionIds) && !empty($divisionIds)) {
            foreach ($divisionIds as $divId) {
                \Database::getInstance()->execute(
                    "INSERT INTO member_course_shared_divisions (member_course_id, division_id) VALUES (?, ?)",
                    [$id, $divId]
                );
            }
        }

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

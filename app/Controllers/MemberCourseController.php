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

        $subject = Subject::find((int)$data['subject_id']);
        if (!$subject) {
            $this->redirect('/member-courses/create', 'المقرر غير موجود', 'error');
        }

        if (!$this->isAssignmentTypeCompatible($subject, (string)$data['assignment_type'])) {
            $this->redirect('/member-courses/create', 'نوع التكليف لا يتوافق مع نوع المقرر المختار', 'error');
        }

        if ($data['assignment_type'] === 'نظري') {
            $existingTheory = $this->findTheoryAssignmentForSubject((int)$data['subject_id']);
            if ($existingTheory) {
                $memberName = trim((string)($existingTheory['member_name'] ?? 'عضو هيئة تدريس'));
                $this->redirect('/member-courses/create', "هذا المقرر مسند نظريًا بالفعل إلى {$memberName}. النظري يجب أن يكون لشخص واحد فقط.", 'error');
            }
        }

        // Assignment logic based on type and scope
        $divisionIds = [];
        if ($data['assignment_type'] === 'نظري') {
            $divisionIds = $_POST['division_id'] ?? [];
            if (!is_array($divisionIds)) {
                $divisionIds = [$divisionIds];
            }
            $divisionIds = array_filter(array_map('intval', $divisionIds));

            if (empty($divisionIds)) {
                $this->redirect('/member-courses/create', 'يرجى اختيار الشعبة للتدريس النظري', 'error');
            }

            foreach ($divisionIds as $divisionId) {
                if (!$this->isDivisionMatchingSubject((int)$divisionId, $subject)) {
                    $this->redirect('/member-courses/create', 'الشعبة المختارة لا تتبع نفس القسم والفرقة الخاصة بالمقرر', 'error');
                }
            }

            $data['division_id'] = reset($divisionIds);
            $data['is_shared'] = count($divisionIds) > 1 ? 1 : 0;
            $data['section_id'] = null;
        } else {
            // Practical: check scope (section-level or division-level)
            $practicalScope = $this->request->input('practical_scope', 'section');
            if ($practicalScope === 'division') {
                $data['division_id'] = (int)$this->request->input('practical_division_id');
                $data['section_id'] = null;
                $data['is_shared'] = 0;
                if (!$data['division_id']) {
                    $this->redirect('/member-courses/create', 'يرجى اختيار الشعبة للتدريس العملي', 'error');
                }
                if (!$this->isDivisionMatchingSubject((int)$data['division_id'], $subject)) {
                    $this->redirect('/member-courses/create', 'الشعبة المختارة لا تتبع نفس القسم والفرقة الخاصة بالمقرر', 'error');
                }
            } else {
                $data['section_id'] = (int)$this->request->input('section_id');
                $data['division_id'] = null;
                $data['is_shared'] = 0;
                if (!$data['section_id']) {
                    $this->redirect('/member-courses/create', 'يرجى اختيار السكشن للتدريس العملي', 'error');
                }
                if (!$this->isSectionMatchingSubject((int)$data['section_id'], $subject)) {
                    $this->redirect('/member-courses/create', 'السكشن المختار لا يتبع نفس القسم والفرقة الخاصة بالمقرر', 'error');
                }
            }
        }

        $id = MemberCourse::create($data);
        
        // Save division relations for shared theory
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

        $subject = Subject::find((int)$data['subject_id']);
        if (!$subject) {
            $this->redirect("/member-courses/{$id}/edit", 'المقرر غير موجود', 'error');
        }

        if (!$this->isAssignmentTypeCompatible($subject, (string)$data['assignment_type'])) {
            $this->redirect("/member-courses/{$id}/edit", 'نوع التكليف لا يتوافق مع نوع المقرر المختار', 'error');
        }

        if ($data['assignment_type'] === 'نظري') {
            $existingTheory = $this->findTheoryAssignmentForSubject((int)$data['subject_id'], (int)$id);
            if ($existingTheory) {
                $memberName = trim((string)($existingTheory['member_name'] ?? 'عضو هيئة تدريس'));
                $this->redirect("/member-courses/{$id}/edit", "هذا المقرر مسند نظريًا بالفعل إلى {$memberName}. النظري يجب أن يكون لشخص واحد فقط.", 'error');
            }
        }

        // Assignment logic based on type and scope
        $divisionIds = [];
        if ($data['assignment_type'] === 'نظري') {
            $divisionIds = $_POST['division_id'] ?? [];
            if (!is_array($divisionIds)) {
                $divisionIds = [$divisionIds];
            }
            $divisionIds = array_filter(array_map('intval', $divisionIds));

            if (empty($divisionIds)) {
                $this->redirect("/member-courses/{$id}/edit", 'يرجى اختيار الشعبة للتدريس النظري', 'error');
            }

            foreach ($divisionIds as $divisionId) {
                if (!$this->isDivisionMatchingSubject((int)$divisionId, $subject)) {
                    $this->redirect("/member-courses/{$id}/edit", 'الشعبة المختارة لا تتبع نفس القسم والفرقة الخاصة بالمقرر', 'error');
                }
            }

            $data['division_id'] = reset($divisionIds);
            $data['is_shared'] = count($divisionIds) > 1 ? 1 : 0;
            $data['section_id'] = null;
        } else {
            // Practical: check scope (section-level or division-level)
            $practicalScope = $this->request->input('practical_scope', 'section');
            if ($practicalScope === 'division') {
                $data['division_id'] = (int)$this->request->input('practical_division_id');
                $data['section_id'] = null;
                $data['is_shared'] = 0;
                if (!$data['division_id']) {
                    $this->redirect("/member-courses/{$id}/edit", 'يرجى اختيار الشعبة للتدريس العملي', 'error');
                }
                if (!$this->isDivisionMatchingSubject((int)$data['division_id'], $subject)) {
                    $this->redirect("/member-courses/{$id}/edit", 'الشعبة المختارة لا تتبع نفس القسم والفرقة الخاصة بالمقرر', 'error');
                }
            } else {
                $data['section_id'] = (int)$this->request->input('section_id');
                $data['division_id'] = null;
                $data['is_shared'] = 0;
                if (!$data['section_id']) {
                    $this->redirect("/member-courses/{$id}/edit", 'يرجى اختيار السكشن للتدريس العملي', 'error');
                }
                if (!$this->isSectionMatchingSubject((int)$data['section_id'], $subject)) {
                    $this->redirect("/member-courses/{$id}/edit", 'السكشن المختار لا يتبع نفس القسم والفرقة الخاصة بالمقرر', 'error');
                }
            }
        }

        MemberCourse::updateById((int)$id, $data);

        // Update shared divisions
        \Database::getInstance()->execute("DELETE FROM member_course_shared_divisions WHERE member_course_id = ?", [$id]);
        if ($data['assignment_type'] === 'نظري' && !empty($divisionIds)) {
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

    private function isAssignmentTypeCompatible(array $subject, string $assignmentType): bool
    {
        $subjectType = trim((string)($subject['subject_type'] ?? 'نظري'));
        if ($subjectType === 'نظري وعملي') {
            return in_array($assignmentType, ['نظري', 'عملي'], true);
        }
        return $subjectType === $assignmentType;
    }

    private function isDivisionMatchingSubject(int $divisionId, array $subject): bool
    {
        if ($divisionId <= 0) {
            return false;
        }

        $division = Division::find($divisionId);
        if (!$division) {
            return false;
        }

        return (int)$division['department_id'] === (int)$subject['department_id']
            && (int)$division['level_id'] === (int)$subject['level_id'];
    }

    private function isSectionMatchingSubject(int $sectionId, array $subject): bool
    {
        if ($sectionId <= 0) {
            return false;
        }

        $section = Section::findWithDetails($sectionId);
        if (!$section) {
            return false;
        }

        $sectionDepartmentId = (int)($section['div_dept_id'] ?? $section['department_id'] ?? 0);
        $sectionLevelId = (int)($section['div_level_id'] ?? $section['level_id'] ?? 0);

        return $sectionDepartmentId === (int)$subject['department_id']
            && $sectionLevelId === (int)$subject['level_id'];
    }

    private function findTheoryAssignmentForSubject(int $subjectId, ?int $exceptMemberCourseId = null): ?array
    {
        if ($subjectId <= 0) {
            return null;
        }

        $sql = "SELECT mc.member_course_id, mc.member_id, fm.member_name
                FROM member_courses mc
                JOIN faculty_members fm ON fm.member_id = mc.member_id
                WHERE mc.subject_id = ? AND mc.assignment_type = 'نظري'";
        $params = [$subjectId];

        if ($exceptMemberCourseId !== null) {
            $sql .= " AND mc.member_course_id <> ?";
            $params[] = $exceptMemberCourseId;
        }

        $sql .= " LIMIT 1";

        return \Database::getInstance()->fetch($sql, $params);
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

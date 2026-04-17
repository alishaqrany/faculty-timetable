<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/Semester.php';
require_once APP_ROOT . '/app/Models/AcademicYear.php';
require_once APP_ROOT . '/app/Services/AuditService.php';

use App\Models\Semester;
use App\Models\AcademicYear;
use App\Services\AuditService;

class SemesterController extends \Controller
{
    public function index(): void
    {
        $this->authorize('semesters.view');
        $semesters = Semester::allWithYear();
        $this->render('semesters.index', ['semesters' => $semesters]);
    }

    public function create(): void
    {
        $this->authorize('semesters.manage');
        $years = AcademicYear::all('start_date DESC');
        $this->render('semesters.create', ['years' => $years]);
    }

    public function store(): void
    {
        $this->authorize('semesters.manage');
        $this->validateCsrf();

        $data = $this->request->validate([
            'semester_name'    => 'required|max:100',
            'academic_year_id' => 'required|numeric',
            'start_date'       => 'required',
            'end_date'         => 'required',
        ]);

        if ($data === false) {
            $this->redirect('/semesters/create', 'يرجى تصحيح الأخطاء', 'error');
        }

        $data['is_current'] = $this->request->input('is_current', 0) ? 1 : 0;
        $data['is_active'] = 1;

        if ($data['is_current']) {
            \Database::getInstance()->execute("UPDATE semesters SET is_current = 0");
        }

        $id = Semester::create($data);
        AuditService::log('CREATE', 'semesters', $id, null, $data);

        $this->redirect('/semesters', 'تم إنشاء الفصل الدراسي بنجاح ✓');
    }

    public function edit(string $id): void
    {
        $this->authorize('semesters.manage');
        $semester = Semester::find((int)$id);
        if (!$semester) $this->redirect('/semesters', 'الفصل الدراسي غير موجود', 'error');

        $years = AcademicYear::all('start_date DESC');
        $this->render('semesters.edit', ['semester' => $semester, 'years' => $years]);
    }

    public function update(string $id): void
    {
        $this->authorize('semesters.manage');
        $this->validateCsrf();

        $semester = Semester::find((int)$id);
        if (!$semester) $this->redirect('/semesters', 'الفصل الدراسي غير موجود', 'error');

        $data = $this->request->validate([
            'semester_name'    => 'required|max:100',
            'academic_year_id' => 'required|numeric',
            'start_date'       => 'required',
            'end_date'         => 'required',
        ]);

        if ($data === false) {
            $this->redirect("/semesters/{$id}/edit", 'يرجى تصحيح الأخطاء', 'error');
        }

        $data['is_active'] = $this->request->input('is_active', 1);
        Semester::updateById((int)$id, $data);
        AuditService::log('UPDATE', 'semesters', (int)$id, $semester, $data);

        $this->redirect('/semesters', 'تم تحديث الفصل الدراسي بنجاح ✓');
    }

    public function setCurrent(string $id): void
    {
        $this->authorize('semesters.manage');
        $this->validateCsrf();

        $semester = Semester::find((int)$id);
        if (!$semester) $this->redirect('/semesters', 'الفصل الدراسي غير موجود', 'error');

        Semester::setCurrent((int)$id);
        AuditService::log('UPDATE', 'semesters', (int)$id, $semester, ['is_current' => 1]);

        $this->redirect('/semesters', 'تم تعيين الفصل الدراسي الحالي ✓');
    }

    public function destroy(string $id): void
    {
        $this->authorize('semesters.manage');
        $this->validateCsrf();

        $semester = Semester::find((int)$id);
        if (!$semester) $this->redirect('/semesters', 'الفصل الدراسي غير موجود', 'error');

        // Check for linked timetable/member_courses entries
        $db = \Database::getInstance();
        $linked = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM timetable WHERE semester_id = ?", [(int)$id]
        );
        if ($linked > 0) {
            $this->redirect('/semesters', 'لا يمكن حذف فصل مرتبط بجداول زمنية', 'error');
        }

        Semester::destroy((int)$id);
        AuditService::log('DELETE', 'semesters', (int)$id, $semester);

        $this->redirect('/semesters', 'تم حذف الفصل الدراسي بنجاح ✓');
    }
}

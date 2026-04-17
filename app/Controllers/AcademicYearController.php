<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/AcademicYear.php';
require_once APP_ROOT . '/app/Models/Semester.php';
require_once APP_ROOT . '/app/Services/AuditService.php';

use App\Models\AcademicYear;
use App\Models\Semester;
use App\Services\AuditService;

class AcademicYearController extends \Controller
{
    public function index(): void
    {
        $this->authorize('academic_years.view');
        $years = AcademicYear::all('start_date DESC');
        // Attach semester count
        $db = \Database::getInstance();
        foreach ($years as &$y) {
            $y['semester_count'] = (int) $db->fetchColumn(
                "SELECT COUNT(*) FROM semesters WHERE academic_year_id = ?", [$y['id']]
            );
        }
        $this->render('academic-years.index', ['years' => $years]);
    }

    public function create(): void
    {
        $this->authorize('academic_years.manage');
        $this->render('academic-years.create');
    }

    public function store(): void
    {
        $this->authorize('academic_years.manage');
        $this->validateCsrf();

        $data = $this->request->validate([
            'year_name'  => 'required|max:50',
            'start_date' => 'required',
            'end_date'   => 'required',
        ]);

        if ($data === false) {
            $this->redirect('/academic-years/create', 'يرجى تصحيح الأخطاء', 'error');
        }

        $data['is_current'] = $this->request->input('is_current', 0) ? 1 : 0;
        $data['is_active'] = 1;

        // If marking as current, unset others
        if ($data['is_current']) {
            \Database::getInstance()->execute("UPDATE academic_years SET is_current = 0");
        }

        $id = AcademicYear::create($data);
        AuditService::log('CREATE', 'academic_years', $id, null, $data);

        $this->redirect('/academic-years', 'تم إنشاء السنة الأكاديمية بنجاح ✓');
    }

    public function edit(string $id): void
    {
        $this->authorize('academic_years.manage');
        $year = AcademicYear::find((int)$id);
        if (!$year) $this->redirect('/academic-years', 'السنة الأكاديمية غير موجودة', 'error');

        $semesters = Semester::forYear((int)$id);
        $this->render('academic-years.edit', ['year' => $year, 'semesters' => $semesters]);
    }

    public function update(string $id): void
    {
        $this->authorize('academic_years.manage');
        $this->validateCsrf();

        $year = AcademicYear::find((int)$id);
        if (!$year) $this->redirect('/academic-years', 'السنة الأكاديمية غير موجودة', 'error');

        $data = $this->request->validate([
            'year_name'  => 'required|max:50',
            'start_date' => 'required',
            'end_date'   => 'required',
        ]);

        if ($data === false) {
            $this->redirect("/academic-years/{$id}/edit", 'يرجى تصحيح الأخطاء', 'error');
        }

        $data['is_active'] = $this->request->input('is_active', 1);
        AcademicYear::updateById((int)$id, $data);
        AuditService::log('UPDATE', 'academic_years', (int)$id, $year, $data);

        $this->redirect('/academic-years', 'تم تحديث السنة الأكاديمية بنجاح ✓');
    }

    public function setCurrent(string $id): void
    {
        $this->authorize('academic_years.manage');
        $this->validateCsrf();

        $year = AcademicYear::find((int)$id);
        if (!$year) $this->redirect('/academic-years', 'السنة الأكاديمية غير موجودة', 'error');

        AcademicYear::setCurrent((int)$id);
        AuditService::log('UPDATE', 'academic_years', (int)$id, $year, ['is_current' => 1]);

        $this->redirect('/academic-years', 'تم تعيين السنة الأكاديمية الحالية ✓');
    }

    public function destroy(string $id): void
    {
        $this->authorize('academic_years.manage');
        $this->validateCsrf();

        $year = AcademicYear::find((int)$id);
        if (!$year) $this->redirect('/academic-years', 'السنة الأكاديمية غير موجودة', 'error');

        // Check for linked semesters
        $semCount = \Database::getInstance()->fetchColumn(
            "SELECT COUNT(*) FROM semesters WHERE academic_year_id = ?", [(int)$id]
        );
        if ($semCount > 0) {
            $this->redirect('/academic-years', 'لا يمكن حذف سنة أكاديمية مرتبطة بفصول دراسية', 'error');
        }

        AcademicYear::destroy((int)$id);
        AuditService::log('DELETE', 'academic_years', (int)$id, $year);

        $this->redirect('/academic-years', 'تم حذف السنة الأكاديمية بنجاح ✓');
    }
}

<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/Section.php';
require_once APP_ROOT . '/app/Models/Department.php';
require_once APP_ROOT . '/app/Models/Level.php';
require_once APP_ROOT . '/app/Services/AuditService.php';

use App\Models\{Section, Department, Level};
use App\Services\AuditService;

class SectionController extends \Controller
{
    public function index(): void
    {
        $this->authorize('sections.view');
        $sections = Section::allWithDetails();
        $this->render('sections.index', ['sections' => $sections]);
    }

    public function create(): void
    {
        $this->authorize('sections.create');
        $departments = Department::active();
        $levels = Level::active();
        $this->render('sections.create', ['departments' => $departments, 'levels' => $levels]);
    }

    public function store(): void
    {
        $this->authorize('sections.create');
        $this->validateCsrf();

        $data = $this->request->validate([
            'section_name'  => 'required|max:100',
            'department_id' => 'required|integer',
            'level_id'      => 'required|integer',
            'capacity'      => 'integer',
        ]);
        if ($data === false) $this->redirect('/sections/create', 'يرجى تصحيح الأخطاء', 'error');

        $data['is_active'] = 1;
        $id = Section::create($data);
        AuditService::log('CREATE', 'sections', $id, null, $data);

        $this->redirect('/sections', 'تم إنشاء الشعبة بنجاح ✓');
    }

    public function edit(string $id): void
    {
        $this->authorize('sections.edit');
        $section = Section::findWithDetails((int)$id);
        if (!$section) $this->redirect('/sections', 'الشعبة غير موجودة', 'error');

        $departments = Department::active();
        $levels = Level::active();
        $this->render('sections.edit', ['section' => $section, 'departments' => $departments, 'levels' => $levels]);
    }

    public function update(string $id): void
    {
        $this->authorize('sections.edit');
        $this->validateCsrf();

        $section = Section::find((int)$id);
        if (!$section) $this->redirect('/sections', 'الشعبة غير موجودة', 'error');

        $data = $this->request->validate([
            'section_name'  => 'required|max:100',
            'department_id' => 'required|integer',
            'level_id'      => 'required|integer',
            'capacity'      => 'integer',
        ]);
        if ($data === false) $this->redirect("/sections/{$id}/edit", 'يرجى تصحيح الأخطاء', 'error');

        $data['is_active'] = $this->request->input('is_active', 1);
        Section::updateById((int)$id, $data);
        AuditService::log('UPDATE', 'sections', (int)$id, $section, $data);

        $this->redirect('/sections', 'تم تحديث الشعبة بنجاح ✓');
    }

    public function destroy(string $id): void
    {
        $this->authorize('sections.delete');
        $this->validateCsrf();

        $section = Section::find((int)$id);
        if (!$section) $this->redirect('/sections', 'الشعبة غير موجودة', 'error');

        Section::destroy((int)$id);
        AuditService::log('DELETE', 'sections', (int)$id, $section);

        $this->redirect('/sections', 'تم حذف الشعبة بنجاح ✓');
    }
}

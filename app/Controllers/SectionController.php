<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/Section.php';
require_once APP_ROOT . '/app/Models/Division.php';
require_once APP_ROOT . '/app/Models/Department.php';
require_once APP_ROOT . '/app/Models/Level.php';
require_once APP_ROOT . '/app/Services/AuditService.php';

use App\Models\{Section, Division, Department, Level};
use App\Services\AuditService;

class SectionController extends \Controller
{
    public function index(): void
    {
        $this->authorize('sections.view');
        // Group sections by division for better display
        $sections = Section::allGroupedByDivision();
        $this->render('sections.index', ['groupedSections' => $sections]);
    }

    public function create(): void
    {
        $this->authorize('sections.create');
        $divisions = Division::active();
        $this->render('sections.create', [
            'divisions' => $divisions
        ]);
    }

    public function store(): void
    {
        $this->authorize('sections.create');
        $this->validateCsrf();

        $data = $this->request->validate([
            'section_name'  => 'required|max:100',
            'division_id'   => 'required|integer',
            'capacity'      => 'integer',
        ]);
        if ($data === false) $this->redirect('/sections/create', 'يرجى تصحيح الأخطاء', 'error');

        // Get department_id and level_id from division (for backwards compatibility)
        $division = Division::find((int)$data['division_id']);
        if ($division) {
            $data['department_id'] = $division['department_id'];
            $data['level_id'] = $division['level_id'];
        }

        $data['is_active'] = 1;
        $id = Section::create($data);
        AuditService::log('CREATE', 'sections', $id, null, $data);

        $this->redirect('/sections', 'تم إنشاء السكشن بنجاح ✓');
    }

    public function edit(string $id): void
    {
        $this->authorize('sections.edit');
        $section = Section::findWithDetails((int)$id);
        if (!$section) $this->redirect('/sections', 'السكشن غير موجود', 'error');

        $divisions = Division::active();
        
        $this->render('sections.edit', [
            'section' => $section, 
            'divisions' => $divisions
        ]);
    }

    public function update(string $id): void
    {
        $this->authorize('sections.edit');
        $this->validateCsrf();

        $section = Section::find((int)$id);
        if (!$section) $this->redirect('/sections', 'السكشن غير موجود', 'error');

        $data = $this->request->validate([
            'section_name'  => 'required|max:100',
            'division_id'   => 'required|integer',
            'capacity'      => 'integer',
        ]);
        if ($data === false) $this->redirect("/sections/{$id}/edit", 'يرجى تصحيح الأخطاء', 'error');

        // Get department_id and level_id from division (for backwards compatibility)
        $division = Division::find((int)$data['division_id']);
        if ($division) {
            $data['department_id'] = $division['department_id'];
            $data['level_id'] = $division['level_id'];
        }

        $data['is_active'] = $this->request->input('is_active', 1);
        Section::updateById((int)$id, $data);
        AuditService::log('UPDATE', 'sections', (int)$id, $section, $data);

        $this->redirect('/sections', 'تم تحديث السكشن بنجاح ✓');
    }

    public function destroy(string $id): void
    {
        $this->authorize('sections.delete');
        $this->validateCsrf();

        $section = Section::find((int)$id);
        if (!$section) $this->redirect('/sections', 'السكشن غير موجود', 'error');

        Section::destroy((int)$id);
        AuditService::log('DELETE', 'sections', (int)$id, $section);

        $this->redirect('/sections', 'تم حذف السكشن بنجاح ✓');
    }

    /**
     * API: Get sections by division (for AJAX)
     */
    public function byDivision(): void
    {
        $divisionId = (int)$this->request->input('division_id');
        $sections = Section::byDivision($divisionId);
        
        header('Content-Type: application/json');
        echo json_encode($sections);
        exit;
    }
}

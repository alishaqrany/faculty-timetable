<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/Division.php';
require_once APP_ROOT . '/app/Models/Section.php';
require_once APP_ROOT . '/app/Models/Department.php';
require_once APP_ROOT . '/app/Models/Level.php';
require_once APP_ROOT . '/app/Services/AuditService.php';

use App\Models\{Division, Section, Department, Level};
use App\Services\AuditService;

class DivisionController extends \Controller
{
    public function index(): void
    {
        $this->authorize('sections.view'); // Using sections permission for now
        $divisions = Division::allWithDetails();
        
        // Add sections count for each division
        foreach ($divisions as &$div) {
            $div['sections_count'] = Division::getSectionsCount($div['division_id']);
        }
        
        $this->render('divisions.index', ['divisions' => $divisions]);
    }

    public function create(): void
    {
        $this->authorize('sections.create');
        $departments = Department::active();
        $levels = Level::active();
        $this->render('divisions.create', [
            'departments' => $departments, 
            'levels' => $levels
        ]);
    }

    public function store(): void
    {
        $this->authorize('sections.create');
        $this->validateCsrf();

        $data = $this->request->validate([
            'division_name'  => 'required|max:100',
            'department_id'  => 'required|integer',
            'level_id'       => 'required|integer',
            'capacity'       => 'integer',
        ]);
        if ($data === false) $this->redirect('/divisions/create', 'يرجى تصحيح الأخطاء', 'error');

        $data['is_active'] = 1;
        $id = Division::create($data);
        AuditService::log('CREATE', 'divisions', $id, null, $data);

        $this->redirect('/divisions', 'تم إنشاء الشعبة بنجاح ✓');
    }

    public function edit(string $id): void
    {
        $this->authorize('sections.edit');
        $division = Division::findWithDetails((int)$id);
        if (!$division) $this->redirect('/divisions', 'الشعبة غير موجودة', 'error');

        $departments = Department::active();
        $levels = Level::active();
        $sections = Division::getSections((int)$id);
        
        $this->render('divisions.edit', [
            'division' => $division, 
            'departments' => $departments, 
            'levels' => $levels,
            'sections' => $sections
        ]);
    }

    public function update(string $id): void
    {
        $this->authorize('sections.edit');
        $this->validateCsrf();

        $division = Division::find((int)$id);
        if (!$division) $this->redirect('/divisions', 'الشعبة غير موجودة', 'error');

        $data = $this->request->validate([
            'division_name'  => 'required|max:100',
            'department_id'  => 'required|integer',
            'level_id'       => 'required|integer',
            'capacity'       => 'integer',
        ]);
        if ($data === false) $this->redirect("/divisions/{$id}/edit", 'يرجى تصحيح الأخطاء', 'error');

        $data['is_active'] = $this->request->input('is_active', 1);
        Division::updateById((int)$id, $data);
        AuditService::log('UPDATE', 'divisions', (int)$id, $division, $data);

        $this->redirect('/divisions', 'تم تحديث الشعبة بنجاح ✓');
    }

    public function destroy(string $id): void
    {
        $this->authorize('sections.delete');
        $this->validateCsrf();

        $division = Division::find((int)$id);
        if (!$division) $this->redirect('/divisions', 'الشعبة غير موجودة', 'error');

        // Check if division has sections
        $sectionsCount = Division::getSectionsCount((int)$id);
        if ($sectionsCount > 0) {
            $this->redirect('/divisions', "لا يمكن حذف شعبة بها {$sectionsCount} سكشن. احذف السكاشن أولاً.", 'error');
        }

        Division::destroy((int)$id);
        AuditService::log('DELETE', 'divisions', (int)$id, $division);

        $this->redirect('/divisions', 'تم حذف الشعبة بنجاح ✓');
    }

    /**
     * API: Get divisions by department and level (for AJAX)
     */
    public function byDepartmentLevel(): void
    {
        $deptId = (int)$this->request->input('department_id');
        $levelId = (int)$this->request->input('level_id');
        
        $divisions = Division::byDepartmentAndLevel($deptId, $levelId);
        
        header('Content-Type: application/json');
        echo json_encode($divisions);
        exit;
    }
}

<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/Department.php';
require_once APP_ROOT . '/app/Services/AuditService.php';

use App\Models\Department;
use App\Services\AuditService;

class DepartmentController extends \Controller
{
    public function index(): void
    {
        $this->authorize('departments.view');
        $departments = Department::all('department_name ASC');
        $this->render('departments.index', ['departments' => $departments]);
    }

    public function create(): void
    {
        $this->authorize('departments.create');
        $this->render('departments.create');
    }

    public function store(): void
    {
        $this->authorize('departments.create');
        $this->validateCsrf();

        $data = $this->request->validate([
            'department_name' => 'required|max:200',
            'department_code' => 'max:20',
            'description'     => 'max:500',
        ]);

        if ($data === false) {
            $this->redirect('/departments/create', 'يرجى تصحيح الأخطاء', 'error');
        }

        $data['is_active'] = 1;
        $id = Department::create($data);
        AuditService::log('CREATE', 'departments', $id, null, $data);

        $this->redirect('/departments', 'تم إنشاء القسم بنجاح ✓');
    }

    public function edit(string $id): void
    {
        $this->authorize('departments.edit');
        $department = Department::find((int)$id);
        if (!$department) $this->redirect('/departments', 'القسم غير موجود', 'error');

        $this->render('departments.edit', ['department' => $department]);
    }

    public function update(string $id): void
    {
        $this->authorize('departments.edit');
        $this->validateCsrf();

        $department = Department::find((int)$id);
        if (!$department) $this->redirect('/departments', 'القسم غير موجود', 'error');

        $data = $this->request->validate([
            'department_name' => 'required|max:200',
            'department_code' => 'max:20',
            'description'     => 'max:500',
        ]);

        if ($data === false) {
            $this->redirect("/departments/{$id}/edit", 'يرجى تصحيح الأخطاء', 'error');
        }

        $data['is_active'] = $this->request->input('is_active', 1);
        Department::updateById((int)$id, $data);
        AuditService::log('UPDATE', 'departments', (int)$id, $department, $data);

        $this->redirect('/departments', 'تم تحديث القسم بنجاح ✓');
    }

    public function destroy(string $id): void
    {
        $this->authorize('departments.delete');
        $this->validateCsrf();

        $department = Department::find((int)$id);
        if (!$department) $this->redirect('/departments', 'القسم غير موجود', 'error');

        Department::destroy((int)$id);
        AuditService::log('DELETE', 'departments', (int)$id, $department);

        $this->redirect('/departments', 'تم حذف القسم بنجاح ✓');
    }
}

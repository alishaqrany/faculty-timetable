<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/Subject.php';
require_once APP_ROOT . '/app/Models/Department.php';
require_once APP_ROOT . '/app/Models/Level.php';
require_once APP_ROOT . '/app/Services/AuditService.php';

use App\Models\{Subject, Department, Level};
use App\Services\AuditService;

class SubjectController extends \Controller
{
    public function index(): void
    {
        $this->authorize('subjects.view');
        $subjects = Subject::allWithDetails();
        $this->render('subjects.index', ['subjects' => $subjects]);
    }

    public function create(): void
    {
        $this->authorize('subjects.create');
        $departments = Department::active();
        $levels = Level::active();
        $this->render('subjects.create', ['departments' => $departments, 'levels' => $levels]);
    }

    public function store(): void
    {
        $this->authorize('subjects.create');
        $this->validateCsrf();

        $data = $this->request->validate([
            'subject_name'  => 'required|max:200',
            'subject_code'  => 'max:30',
            'department_id' => 'required|integer',
            'level_id'      => 'required|integer',
            'hours'         => 'integer',
            'credit_hours'  => 'integer',
            'subject_type'  => 'in:نظري,عملي,نظري وعملي',
        ]);
        if ($data === false) $this->redirect('/subjects/create', 'يرجى تصحيح الأخطاء', 'error');

        $data['is_active'] = 1;
        $id = Subject::create($data);
        AuditService::log('CREATE', 'subjects', $id, null, $data);

        $this->redirect('/subjects', 'تم إنشاء المقرر بنجاح ✓');
    }

    public function edit(string $id): void
    {
        $this->authorize('subjects.edit');
        $subject = Subject::findWithDetails((int)$id);
        if (!$subject) $this->redirect('/subjects', 'المقرر غير موجود', 'error');

        $departments = Department::active();
        $levels = Level::active();
        $this->render('subjects.edit', ['subject' => $subject, 'departments' => $departments, 'levels' => $levels]);
    }

    public function update(string $id): void
    {
        $this->authorize('subjects.edit');
        $this->validateCsrf();

        $subject = Subject::find((int)$id);
        if (!$subject) $this->redirect('/subjects', 'المقرر غير موجود', 'error');

        $data = $this->request->validate([
            'subject_name'  => 'required|max:200',
            'subject_code'  => 'max:30',
            'department_id' => 'required|integer',
            'level_id'      => 'required|integer',
            'hours'         => 'integer',
            'credit_hours'  => 'integer',
            'subject_type'  => 'in:نظري,عملي,نظري وعملي',
        ]);
        if ($data === false) $this->redirect("/subjects/{$id}/edit", 'يرجى تصحيح الأخطاء', 'error');

        $data['is_active'] = $this->request->input('is_active', 1);
        Subject::updateById((int)$id, $data);
        AuditService::log('UPDATE', 'subjects', (int)$id, $subject, $data);

        $this->redirect('/subjects', 'تم تحديث المقرر بنجاح ✓');
    }

    public function destroy(string $id): void
    {
        $this->authorize('subjects.delete');
        $this->validateCsrf();

        $subject = Subject::find((int)$id);
        if (!$subject) $this->redirect('/subjects', 'المقرر غير موجود', 'error');

        Subject::destroy((int)$id);
        AuditService::log('DELETE', 'subjects', (int)$id, $subject);

        $this->redirect('/subjects', 'تم حذف المقرر بنجاح ✓');
    }
}

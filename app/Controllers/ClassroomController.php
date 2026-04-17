<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/Classroom.php';
require_once APP_ROOT . '/app/Services/AuditService.php';

use App\Models\Classroom;
use App\Services\AuditService;

class ClassroomController extends \Controller
{
    public function index(): void
    {
        $this->authorize('classrooms.view');
        $classrooms = Classroom::all('classroom_name ASC');
        $this->render('classrooms.index', ['classrooms' => $classrooms]);
    }

    public function create(): void
    {
        $this->authorize('classrooms.create');
        $this->render('classrooms.create');
    }

    public function store(): void
    {
        $this->authorize('classrooms.create');
        $this->validateCsrf();

        $data = $this->request->validate([
            'classroom_name' => 'required|max:150',
            'capacity'       => 'integer',
            'classroom_type' => 'in:قاعة,معمل,مدرج,قاعة اجتماعات',
            'building'       => 'max:100',
            'floor'          => 'max:20',
        ]);
        if ($data === false) $this->redirect('/classrooms/create', 'يرجى تصحيح الأخطاء', 'error');

        $data['is_active'] = 1;
        $id = Classroom::create($data);
        AuditService::log('CREATE', 'classrooms', $id, null, $data);

        $this->redirect('/classrooms', 'تم إنشاء القاعة بنجاح ✓');
    }

    public function edit(string $id): void
    {
        $this->authorize('classrooms.edit');
        $classroom = Classroom::find((int)$id);
        if (!$classroom) $this->redirect('/classrooms', 'القاعة غير موجودة', 'error');

        $this->render('classrooms.edit', ['classroom' => $classroom]);
    }

    public function update(string $id): void
    {
        $this->authorize('classrooms.edit');
        $this->validateCsrf();

        $classroom = Classroom::find((int)$id);
        if (!$classroom) $this->redirect('/classrooms', 'القاعة غير موجودة', 'error');

        $data = $this->request->validate([
            'classroom_name' => 'required|max:150',
            'capacity'       => 'integer',
            'classroom_type' => 'in:قاعة,معمل,مدرج,قاعة اجتماعات',
            'building'       => 'max:100',
            'floor'          => 'max:20',
        ]);
        if ($data === false) $this->redirect("/classrooms/{$id}/edit", 'يرجى تصحيح الأخطاء', 'error');

        $data['is_active'] = $this->request->input('is_active', 1);
        Classroom::updateById((int)$id, $data);
        AuditService::log('UPDATE', 'classrooms', (int)$id, $classroom, $data);

        $this->redirect('/classrooms', 'تم تحديث القاعة بنجاح ✓');
    }

    public function destroy(string $id): void
    {
        $this->authorize('classrooms.delete');
        $this->validateCsrf();

        $classroom = Classroom::find((int)$id);
        if (!$classroom) $this->redirect('/classrooms', 'القاعة غير موجودة', 'error');

        Classroom::destroy((int)$id);
        AuditService::log('DELETE', 'classrooms', (int)$id, $classroom);

        $this->redirect('/classrooms', 'تم حذف القاعة بنجاح ✓');
    }
}

<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/Member.php';
require_once APP_ROOT . '/app/Models/Department.php';
require_once APP_ROOT . '/app/Models/User.php';
require_once APP_ROOT . '/app/Services/AuditService.php';

use App\Models\{Member, Department, User};
use App\Services\AuditService;

class MemberController extends \Controller
{
    public function index(): void
    {
        $this->authorize('members.view');
        $members = Member::allWithDetails();
        $this->render('members.index', ['members' => $members]);
    }

    public function create(): void
    {
        $this->authorize('members.create');
        $departments = Department::active();
        $degrees = \Database::getInstance()->fetchAll("SELECT * FROM academic_degrees ORDER BY sort_order");
        $this->render('members.create', ['departments' => $departments, 'degrees' => $degrees]);
    }

    public function store(): void
    {
        $this->authorize('members.create');
        $this->validateCsrf();

        $data = $this->request->validate([
            'member_name'   => 'required|max:200',
            'email'         => 'email|max:200',
            'phone'         => 'max:30',
            'department_id' => 'required|integer',
            'degree_id'     => 'integer',
            'join_date'     => 'date',
        ]);
        if ($data === false) $this->redirect('/members/create', 'يرجى تصحيح الأخطاء', 'error');

        $data['is_active'] = 1;
        $data['ranking'] = Member::count() + 1;
        $memberId = Member::create($data);

        // Auto-create user account
        $username = 'user' . $memberId;
        $password = bin2hex(random_bytes(3)); // 6-char hex password
        $facultyRoleId = \Database::getInstance()->fetchColumn("SELECT id FROM roles WHERE role_slug = 'faculty'");

        $userId = User::create([
            'username'  => $username,
            'password'  => password_hash($password, PASSWORD_DEFAULT),
            'member_id' => $memberId,
            'role_id'   => $facultyRoleId,
            'is_active' => 1,
        ]);

        AuditService::log('CREATE', 'members', $memberId, null, $data);

        $msg = "تم إنشاء العضو بنجاح ✓ | اسم المستخدم: $username | كلمة المرور: $password";
        $this->redirect('/members', $msg);
    }

    public function edit(string $id): void
    {
        $this->authorize('members.edit');
        $member = Member::findWithDetails((int)$id);
        if (!$member) $this->redirect('/members', 'العضو غير موجود', 'error');

        $departments = Department::active();
        $degrees = \Database::getInstance()->fetchAll("SELECT * FROM academic_degrees ORDER BY sort_order");
        $this->render('members.edit', ['member' => $member, 'departments' => $departments, 'degrees' => $degrees]);
    }

    public function update(string $id): void
    {
        $this->authorize('members.edit');
        $this->validateCsrf();

        $member = Member::find((int)$id);
        if (!$member) $this->redirect('/members', 'العضو غير موجود', 'error');

        $data = $this->request->validate([
            'member_name'   => 'required|max:200',
            'email'         => 'email|max:200',
            'phone'         => 'max:30',
            'department_id' => 'required|integer',
            'degree_id'     => 'integer',
            'join_date'     => 'date',
        ]);
        if ($data === false) $this->redirect("/members/{$id}/edit", 'يرجى تصحيح الأخطاء', 'error');

        $data['is_active'] = $this->request->input('is_active', 1);
        Member::updateById((int)$id, $data);
        AuditService::log('UPDATE', 'members', (int)$id, $member, $data);

        $this->redirect('/members', 'تم تحديث بيانات العضو بنجاح ✓');
    }

    public function destroy(string $id): void
    {
        $this->authorize('members.delete');
        $this->validateCsrf();

        $member = Member::find((int)$id);
        if (!$member) $this->redirect('/members', 'العضو غير موجود', 'error');

        Member::destroy((int)$id);
        AuditService::log('DELETE', 'members', (int)$id, $member);

        $this->redirect('/members', 'تم حذف العضو بنجاح ✓');
    }
}

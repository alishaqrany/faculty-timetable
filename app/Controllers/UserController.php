<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/User.php';
require_once APP_ROOT . '/app/Models/Role.php';
require_once APP_ROOT . '/app/Models/Member.php';
require_once APP_ROOT . '/app/Services/AuditService.php';

use App\Models\{User, Role, Member};
use App\Services\AuditService;

class UserController extends \Controller
{
    public function index(): void
    {
        $this->authorize('users.view');
        $users = User::allWithDetails();
        $this->render('users.index', ['users' => $users]);
    }

    public function create(): void
    {
        $this->authorize('users.create');
        $roles = Role::all('role_name ASC');
        $members = Member::active();
        $this->render('users.create', ['roles' => $roles, 'members' => $members]);
    }

    public function store(): void
    {
        $this->authorize('users.create');
        $this->validateCsrf();

        $data = $this->request->validate([
            'username' => 'required|max:100|unique:users,username',
            'password' => 'required|min:6',
            'email'    => 'email|max:200',
            'role_id'  => 'required|integer',
        ]);
        if ($data === false) $this->redirect('/users/create', 'يرجى تصحيح الأخطاء', 'error');

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['member_id'] = $this->request->input('member_id') ?: null;
        $data['is_active'] = 1;

        $id = User::create($data);
        AuditService::log('CREATE', 'users', $id);

        $this->redirect('/users', 'تم إنشاء المستخدم بنجاح ✓');
    }

    public function edit(string $id): void
    {
        $this->authorize('users.edit');
        $user = User::withRole((int)$id);
        if (!$user) $this->redirect('/users', 'المستخدم غير موجود', 'error');

        $roles = Role::all('role_name ASC');
        $members = Member::active();
        $this->render('users.edit', ['user' => $user, 'roles' => $roles, 'members' => $members]);
    }

    public function update(string $id): void
    {
        $this->authorize('users.edit');
        $this->validateCsrf();

        $user = User::find((int)$id);
        if (!$user) $this->redirect('/users', 'المستخدم غير موجود', 'error');

        $data = [
            'username'  => trim($this->request->input('username', '')),
            'email'     => trim($this->request->input('email', '')),
            'role_id'   => (int)$this->request->input('role_id'),
            'is_active' => (int)$this->request->input('is_active', 1),
            'member_id' => $this->request->input('member_id') ?: null,
        ];

        $newPassword = $this->request->input('password', '');
        if ($newPassword !== '') {
            $data['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        User::updateById((int)$id, $data);
        AuditService::log('UPDATE', 'users', (int)$id, $user, $data);

        $this->redirect('/users', 'تم تحديث المستخدم بنجاح ✓');
    }

    public function destroy(string $id): void
    {
        $this->authorize('users.delete');
        $this->validateCsrf();

        $user = User::find((int)$id);
        if (!$user) $this->redirect('/users', 'المستخدم غير موجود', 'error');

        User::destroy((int)$id);
        AuditService::log('DELETE', 'users', (int)$id, $user);

        $this->redirect('/users', 'تم حذف المستخدم بنجاح ✓');
    }
}

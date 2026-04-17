<?php
$this->layout('layouts.app');
$__page_title = 'إدارة المستخدمين';
$__breadcrumb = [['label' => 'المستخدمون']];
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">قائمة المستخدمين</h3>
        <?php if (can('users.create')): ?>
        <div class="card-tools">
            <a href="<?= url('/users/create') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus ml-1"></i> إضافة مستخدم</a>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped data-table">
            <thead>
                <tr><th>#</th><th>اسم المستخدم</th><th>العضو المرتبط</th><th>الدور</th><th>الحالة</th><th>آخر دخول</th><th>إجراءات</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $i => $u): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($u['username']) ?></td>
                    <td><?= e($u['member_name'] ?? '—') ?></td>
                    <td><span class="badge badge-primary"><?= e($u['role_name'] ?? '') ?></span></td>
                    <td><span class="badge badge-<?= $u['is_active'] ? 'success' : 'secondary' ?>"><?= $u['is_active'] ? 'فعّال' : 'معطّل' ?></span></td>
                    <td><?= e($u['last_login_at'] ?? '—') ?></td>
                    <td>
                        <?php if (can('users.edit')): ?>
                        <a href="<?= url("/users/{$u['id']}/edit") ?>" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a>
                        <?php endif; ?>
                        <?php if (can('users.delete')): ?>
                        <form method="POST" action="<?= url("/users/{$u['id']}/delete") ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger btn-xs btn-delete"><i class="fas fa-trash"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

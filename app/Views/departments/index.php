<?php
$this->layout('layouts.app');
$__page_title = 'إدارة الأقسام';
$__breadcrumb = [['label' => 'الأقسام']];
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">قائمة الأقسام</h3>
        <?php if (can('departments.create')): ?>
        <div class="card-tools">
            <a href="<?= url('/departments/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus ml-1"></i> إضافة قسم
            </a>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم القسم</th>
                    <th>رمز القسم</th>
                    <th>الوصف</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($departments as $i => $dept): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($dept['department_name']) ?></td>
                    <td><?= e($dept['department_code'] ?? '—') ?></td>
                    <td><?= e(str_limit($dept['description'] ?? '', 50)) ?></td>
                    <td>
                        <?php if ($dept['is_active']): ?>
                            <span class="badge badge-success">فعّال</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">معطّل</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (can('departments.edit')): ?>
                        <a href="<?= url("/departments/{$dept['department_id']}/edit") ?>" class="btn btn-warning btn-xs">
                            <i class="fas fa-edit"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (can('departments.delete')): ?>
                        <form method="POST" action="<?= url("/departments/{$dept['department_id']}/delete") ?>" class="d-inline">
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

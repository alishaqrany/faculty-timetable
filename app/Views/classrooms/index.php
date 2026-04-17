<?php
$this->layout('layouts.app');
$__page_title = 'إدارة القاعات';
$__breadcrumb = [['label' => 'القاعات']];
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">قائمة القاعات الدراسية</h3>
        <?php if (can('classrooms.create')): ?>
        <div class="card-tools">
            <a href="<?= url('/classrooms/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus ml-1"></i> إضافة قاعة
            </a>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped data-table">
            <thead>
                <tr><th>#</th><th>اسم القاعة</th><th>النوع</th><th>السعة</th><th>المبنى</th><th>الطابق</th><th>الحالة</th><th>إجراءات</th></tr>
            </thead>
            <tbody>
                <?php foreach ($classrooms as $i => $c): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($c['classroom_name']) ?></td>
                    <td><?= e($c['classroom_type'] ?? 'قاعة') ?></td>
                    <td><?= (int)($c['capacity'] ?? 0) ?></td>
                    <td><?= e($c['building'] ?? '—') ?></td>
                    <td><?= e($c['floor'] ?? '—') ?></td>
                    <td><span class="badge badge-<?= $c['is_active'] ? 'success' : 'secondary' ?>"><?= $c['is_active'] ? 'فعّال' : 'معطّل' ?></span></td>
                    <td>
                        <?php if (can('classrooms.edit')): ?>
                        <a href="<?= url("/classrooms/{$c['classroom_id']}/edit") ?>" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a>
                        <?php endif; ?>
                        <?php if (can('classrooms.delete')): ?>
                        <form method="POST" action="<?= url("/classrooms/{$c['classroom_id']}/delete") ?>" class="d-inline">
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

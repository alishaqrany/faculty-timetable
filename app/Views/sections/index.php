<?php
$this->layout('layouts.app');
$__page_title = 'إدارة الشعب والسكاشن';
$__breadcrumb = [['label' => 'الشعب والسكاشن']];
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">قائمة الشعب والسكاشن</h3>
        <?php if (can('sections.create')): ?>
        <div class="card-tools">
            <a href="<?= url('/sections/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus ml-1"></i> إضافة مجموعة
            </a>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped data-table">
            <thead>
                <tr><th>#</th><th>الاسم</th><th>النوع</th><th>الشعبة الأب</th><th>القسم</th><th>المستوى</th><th>السعة</th><th>الحالة</th><th>إجراءات</th></tr>
            </thead>
            <tbody>
                <?php foreach ($sections as $i => $s): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($s['section_name']) ?></td>
                    <td><?= e($s['section_type'] ?? 'شعبة') ?></td>
                    <td><?= e($s['parent_section_name'] ?? '—') ?></td>
                    <td><?= e($s['department_name'] ?? '') ?></td>
                    <td><?= e($s['level_name'] ?? '') ?></td>
                    <td><?= (int)($s['capacity'] ?? 0) ?></td>
                    <td><span class="badge badge-<?= $s['is_active'] ? 'success' : 'secondary' ?>"><?= $s['is_active'] ? 'فعّال' : 'معطّل' ?></span></td>
                    <td>
                        <?php if (can('sections.edit')): ?>
                        <a href="<?= url("/sections/{$s['section_id']}/edit") ?>" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a>
                        <?php endif; ?>
                        <?php if (can('sections.delete')): ?>
                        <form method="POST" action="<?= url("/sections/{$s['section_id']}/delete") ?>" class="d-inline">
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

<?php
$this->layout('layouts.app');
$__page_title = 'إدارة الفرق';
$__breadcrumb = [['label' => 'الفرق']];
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">قائمة الفرق</h3>
        <?php if (can('levels.create')): ?>
        <div class="card-tools">
            <form method="POST" action="<?= url('/levels/seed-defaults') ?>" class="d-inline" onsubmit="return confirm('سيتم إضافة الفرق الافتراضية غير الموجودة (الأولى إلى الرابعة). هل تريد المتابعة؟');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success btn-sm ml-1">
                    <i class="fas fa-magic ml-1"></i> إضافة الفرق
                </button>
            </form>
            <a href="<?= url('/levels/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus ml-1"></i> إضافة فرقة
            </a>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped data-table">
            <thead>
                <tr><th>#</th><th>اسم الفرقة</th><th>الرمز</th><th>الترتيب</th><th>الحالة</th><th>إجراءات</th></tr>
            </thead>
            <tbody>
                <?php foreach ($levels as $i => $level): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($level['level_name']) ?></td>
                    <td><?= e($level['level_code'] ?? '—') ?></td>
                    <td><?= (int)($level['sort_order'] ?? 0) ?></td>
                    <td><span class="badge badge-<?= $level['is_active'] ? 'success' : 'secondary' ?>"><?= $level['is_active'] ? 'فعّال' : 'معطّل' ?></span></td>
                    <td>
                        <?php if (can('levels.edit')): ?>
                        <a href="<?= url("/levels/{$level['level_id']}/edit") ?>" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a>
                        <?php endif; ?>
                        <?php if (can('levels.delete')): ?>
                        <form method="POST" action="<?= url("/levels/{$level['level_id']}/delete") ?>" class="d-inline">
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

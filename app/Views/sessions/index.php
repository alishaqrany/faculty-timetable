<?php
$this->layout('layouts.app');
$__page_title = 'الفترات الزمنية';
$__breadcrumb = [['label' => 'الفترات']];
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">جدول الفترات الزمنية</h3>
        <div class="card-tools">
            <?php if (can('sessions.create')): ?>
            <form method="POST" action="<?= url('/sessions/generate') ?>" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('سيتم إنشاء الفترات الافتراضية. متابعة؟')">
                    <i class="fas fa-magic ml-1"></i> إنشاء فترات افتراضية
                </button>
            </form>
            <a href="<?= url('/sessions/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus ml-1"></i> إضافة فترة
            </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped data-table">
            <thead>
                <tr><th>#</th><th>اليوم</th><th>اسم الفترة</th><th>من</th><th>إلى</th><th>المدة</th><th>الحالة</th><th>إجراءات</th></tr>
            </thead>
            <tbody>
                <?php foreach ($sessions as $i => $s): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($s['day']) ?></td>
                    <td><?= e($s['session_name'] ?? '') ?></td>
                    <td><?= e($s['start_time']) ?></td>
                    <td><?= e($s['end_time']) ?></td>
                    <td><?= (int)($s['duration'] ?? 0) ?> ساعة</td>
                    <td><span class="badge badge-<?= $s['is_active'] ? 'success' : 'secondary' ?>"><?= $s['is_active'] ? 'فعّال' : 'معطّل' ?></span></td>
                    <td>
                        <?php if (can('sessions.edit')): ?>
                        <a href="<?= url("/sessions/{$s['session_id']}/edit") ?>" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a>
                        <?php endif; ?>
                        <?php if (can('sessions.delete')): ?>
                        <form method="POST" action="<?= url("/sessions/{$s['session_id']}/delete") ?>" class="d-inline">
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

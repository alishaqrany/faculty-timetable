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
            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#generateModal">
                <i class="fas fa-magic ml-1"></i> إنشاء فترات افتراضية
            </button>
            <a href="<?= url('/sessions/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus ml-1"></i> إضافة فترة
            </a>
            <?php endif; ?>
            <?php if (can('sessions.delete')): ?>
            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteAllModal">
                <i class="fas fa-trash-alt ml-1"></i> حذف الكل
            </button>
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

<?php if (can('sessions.create')): ?>
<!-- Generate Sessions Modal -->
<div class="modal fade" id="generateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <form method="POST" action="<?= url('/sessions/generate') ?>">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-magic ml-1"></i> إنشاء فترات افتراضية</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">عدد الفترات في اليوم الدراسي</label>
                        <input type="number" name="sessions_per_day" class="form-control"
                               value="5" min="1" max="8" required>
                        <small class="text-muted">من 1 إلى 8 فترات (الافتراضي: 5)</small>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">مدة الفترة الواحدة</label>
                        <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                            <label class="btn btn-outline-secondary flex-fill">
                                <input type="radio" name="duration_hours" value="1"> ساعة
                            </label>
                            <label class="btn btn-outline-secondary flex-fill active">
                                <input type="radio" name="duration_hours" value="2" checked> ساعتان
                            </label>
                            <label class="btn btn-outline-secondary flex-fill">
                                <input type="radio" name="duration_hours" value="3"> 3 ساعات
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check ml-1"></i> إنشاء</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if (can('sessions.delete')): ?>
<!-- Delete All Sessions Modal -->
<div class="modal fade" id="deleteAllModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <form method="POST" action="<?= url('/sessions/destroy-all') ?>">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle ml-1"></i> حذف جميع الفترات</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body text-center">
                    <p class="mb-1">سيتم حذف <strong>جميع الفترات الزمنية</strong> نهائياً.</p>
                    <p class="text-warning mb-1"><i class="fas fa-exclamation-triangle ml-1"></i> سيتم حذف جميع سجلات الجدول الزمني المرتبطة بهذه الفترات أيضاً.</p>
                    <p class="text-danger mb-0">لا يمكن التراجع عن هذا الإجراء.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash ml-1"></i> حذف الكل</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php $this->section('scripts') ?>
<script>
$(function () {
    // Move modals to <body> to avoid AdminLTE content-wrapper z-index conflicts
    $('#generateModal, #deleteAllModal').appendTo('body');
});
</script>
<?php $this->endSection() ?>

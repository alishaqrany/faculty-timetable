<?php
$this->layout('layouts.app');
$__page_title = 'تعديل السنة الأكاديمية';
$__breadcrumb = [['label' => 'السنوات الأكاديمية', 'url' => url('/academic-years')], ['label' => 'تعديل']];
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">تعديل السنة الأكاديمية: <?= e($year['year_name']) ?></h3>
    </div>
    <form method="POST" action="<?= url("/academic-years/{$year['id']}") ?>">
        <?= csrf_field() ?>
        <div class="card-body">
            <div class="form-group">
                <label>اسم السنة الأكاديمية <span class="text-danger">*</span></label>
                <input type="text" name="year_name" class="form-control" value="<?= e($year['year_name']) ?>" required>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>تاريخ البداية <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" value="<?= e($year['start_date']) ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>تاريخ النهاية <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" class="form-control" value="<?= e($year['end_date']) ?>" required>
                    </div>
                </div>
            </div>
            <div class="form-check mb-3">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive"
                       <?= ($year['is_active'] ?? 1) ? 'checked' : '' ?>>
                <label class="form-check-label" for="isActive">مفعّلة</label>
            </div>

            <?php if (!empty($semesters)): ?>
            <hr>
            <h5>الفصول الدراسية المرتبطة</h5>
            <table class="table table-sm table-bordered">
                <thead><tr><th>الفصل</th><th>البداية</th><th>النهاية</th><th>الحالة</th></tr></thead>
                <tbody>
                <?php foreach ($semesters as $s): ?>
                <tr>
                    <td>
                        <?= e($s['semester_name']) ?>
                        <?php if ($s['is_current']): ?><span class="badge badge-success">الحالي</span><?php endif; ?>
                    </td>
                    <td><?= e($s['start_date']) ?></td>
                    <td><?= e($s['end_date']) ?></td>
                    <td><?= ($s['is_active'] ?? 1) ? '<span class="badge badge-success">مفعّل</span>' : '<span class="badge badge-secondary">معطّل</span>' ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> تحديث</button>
            <a href="<?= url('/academic-years') ?>" class="btn btn-secondary"><i class="fas fa-times ml-1"></i> إلغاء</a>
        </div>
    </form>
</div>

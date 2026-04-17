<?php
$this->layout('layouts.app');
$__page_title = 'إضافة فصل دراسي';
$__breadcrumb = [['label' => 'الفصول الدراسية', 'url' => url('/semesters')], ['label' => 'إضافة']];
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">إضافة فصل دراسي جديد</h3>
    </div>
    <form method="POST" action="<?= url('/semesters') ?>">
        <?= csrf_field() ?>
        <div class="card-body">
            <div class="form-group">
                <label>اسم الفصل الدراسي <span class="text-danger">*</span></label>
                <input type="text" name="semester_name" class="form-control" value="<?= e(old('semester_name')) ?>"
                       placeholder="مثال: الفصل الدراسي الأول" required>
            </div>
            <div class="form-group">
                <label>السنة الأكاديمية <span class="text-danger">*</span></label>
                <select name="academic_year_id" class="form-control select2" required>
                    <option value="">-- اختر السنة الأكاديمية --</option>
                    <?php foreach ($years as $y): ?>
                    <option value="<?= $y['id'] ?>" <?= old('academic_year_id') == $y['id'] ? 'selected' : '' ?>>
                        <?= e($y['year_name']) ?>
                        <?= $y['is_current'] ? '(الحالية)' : '' ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>تاريخ البداية <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" value="<?= e(old('start_date')) ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>تاريخ النهاية <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" class="form-control" value="<?= e(old('end_date')) ?>" required>
                    </div>
                </div>
            </div>
            <div class="form-check">
                <input type="checkbox" name="is_current" value="1" class="form-check-input" id="isCurrent">
                <label class="form-check-label" for="isCurrent">تعيين كفصل دراسي حالي</label>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
            <a href="<?= url('/semesters') ?>" class="btn btn-secondary"><i class="fas fa-times ml-1"></i> إلغاء</a>
        </div>
    </form>
</div>

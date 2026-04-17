<?php
$this->layout('layouts.app');
$__page_title = 'تعديل الفصل الدراسي';
$__breadcrumb = [['label' => 'الفصول الدراسية', 'url' => url('/semesters')], ['label' => 'تعديل']];
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">تعديل الفصل الدراسي: <?= e($semester['semester_name']) ?></h3>
    </div>
    <form method="POST" action="<?= url("/semesters/{$semester['id']}") ?>">
        <?= csrf_field() ?>
        <div class="card-body">
            <div class="form-group">
                <label>اسم الفصل الدراسي <span class="text-danger">*</span></label>
                <input type="text" name="semester_name" class="form-control" value="<?= e($semester['semester_name']) ?>" required>
            </div>
            <div class="form-group">
                <label>السنة الأكاديمية <span class="text-danger">*</span></label>
                <select name="academic_year_id" class="form-control select2" required>
                    <option value="">-- اختر السنة الأكاديمية --</option>
                    <?php foreach ($years as $y): ?>
                    <option value="<?= $y['id'] ?>" <?= $semester['academic_year_id'] == $y['id'] ? 'selected' : '' ?>>
                        <?= e($y['year_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>تاريخ البداية <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" value="<?= e($semester['start_date']) ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>تاريخ النهاية <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" class="form-control" value="<?= e($semester['end_date']) ?>" required>
                    </div>
                </div>
            </div>
            <div class="form-check mb-3">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive"
                       <?= ($semester['is_active'] ?? 1) ? 'checked' : '' ?>>
                <label class="form-check-label" for="isActive">مفعّل</label>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> تحديث</button>
            <a href="<?= url('/semesters') ?>" class="btn btn-secondary"><i class="fas fa-times ml-1"></i> إلغاء</a>
        </div>
    </form>
</div>

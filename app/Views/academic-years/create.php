<?php
$this->layout('layouts.app');
$__page_title = 'إضافة سنة أكاديمية';
$__breadcrumb = [['label' => 'السنوات الأكاديمية', 'url' => url('/academic-years')], ['label' => 'إضافة']];
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">إضافة سنة أكاديمية جديدة</h3>
    </div>
    <form method="POST" action="<?= url('/academic-years') ?>">
        <?= csrf_field() ?>
        <div class="card-body">
            <div class="form-group">
                <label>اسم السنة الأكاديمية <span class="text-danger">*</span></label>
                <input type="text" name="year_name" class="form-control" value="<?= e(old('year_name')) ?>"
                       placeholder="مثال: 2025-2026" required>
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
                <label class="form-check-label" for="isCurrent">تعيين كسنة أكاديمية حالية</label>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
            <a href="<?= url('/academic-years') ?>" class="btn btn-secondary"><i class="fas fa-times ml-1"></i> إلغاء</a>
        </div>
    </form>
</div>

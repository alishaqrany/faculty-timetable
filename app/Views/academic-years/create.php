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
                <label>السنة الأكاديمية (اختيار أو إنشاء) <span class="text-danger">*</span></label>
                <input type="text" name="year_range" class="form-control" value="<?= e(old('year_range', $suggested['year_range'] ?? '')) ?>"
                       list="year-range-options" placeholder="مثال: 2025-2026" pattern="\d{4}-\d{4}" required>
                <datalist id="year-range-options">
                    <?php foreach (($yearRangeOptions ?? []) as $opt): ?>
                    <option value="<?= e($opt) ?>"></option>
                    <?php endforeach; ?>
                </datalist>
                <small class="form-text text-muted">اختر من القائمة أو اكتب سنة جديدة بصيغة 2025-2026.</small>
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

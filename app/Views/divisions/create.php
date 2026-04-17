<?php
$this->layout('layouts.app');
$__page_title = 'إضافة شعبة';
$__breadcrumb = [['label' => 'الشُعب', 'url' => '/divisions'], ['label' => 'إضافة']];
?>

<div class="row"><div class="col-md-8">
    <div class="card card-primary">
        <div class="card-header"><h3 class="card-title">بيانات الشعبة الجديدة</h3></div>
        <form method="POST" action="<?= url('/divisions') ?>">
            <?= csrf_field() ?>
            <div class="card-body">
                <div class="form-group">
                    <label>اسم الشعبة <span class="text-danger">*</span></label>
                    <input type="text" name="division_name" class="form-control" value="<?= e(old('division_name')) ?>" required
                           placeholder="مثال: شعبة أ، شعبة ب، عامة">
                    <small class="text-muted">للفرقة بدون تقسيم استخدم "عامة"</small>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>القسم <span class="text-danger">*</span></label>
                            <select name="department_id" class="form-control select2" required>
                                <option value="">-- اختر القسم --</option>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?= $d['department_id'] ?>" <?= old('department_id') == $d['department_id'] ? 'selected' : '' ?>>
                                        <?= e($d['department_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>الفرقة <span class="text-danger">*</span></label>
                            <select name="level_id" class="form-control select2" required>
                                <option value="">-- اختر الفرقة --</option>
                                <?php foreach ($levels as $l): ?>
                                    <option value="<?= $l['level_id'] ?>" <?= old('level_id') == $l['level_id'] ? 'selected' : '' ?>>
                                        <?= e($l['level_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>السعة (عدد الطلاب)</label>
                    <input type="number" name="capacity" class="form-control" value="<?= e(old('capacity', '100')) ?>" min="1">
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                <a href="<?= url('/divisions') ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div></div>

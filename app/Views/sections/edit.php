<?php
$this->layout('layouts.app');
$__page_title = 'تعديل الشعبة';
$__breadcrumb = [['label' => 'الشُعب', 'url' => '/sections'], ['label' => 'تعديل']];
?>

<div class="row"><div class="col-md-8">
    <div class="card card-warning">
        <div class="card-header"><h3 class="card-title">تعديل بيانات الشعبة</h3></div>
        <form method="POST" action="<?= url("/sections/{$section['section_id']}") ?>">
            <?= csrf_field() ?>
            <div class="card-body">
                <div class="form-group">
                    <label>اسم الشعبة <span class="text-danger">*</span></label>
                    <input type="text" name="section_name" class="form-control" value="<?= e($section['section_name']) ?>" required>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>القسم <span class="text-danger">*</span></label>
                            <select name="department_id" class="form-control select2" required>
                                <option value="">-- اختر --</option>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?= $d['department_id'] ?>" <?= $section['department_id'] == $d['department_id'] ? 'selected' : '' ?>><?= e($d['department_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>المستوى <span class="text-danger">*</span></label>
                            <select name="level_id" class="form-control select2" required>
                                <option value="">-- اختر --</option>
                                <?php foreach ($levels as $l): ?>
                                    <option value="<?= $l['level_id'] ?>" <?= $section['level_id'] == $l['level_id'] ? 'selected' : '' ?>><?= e($l['level_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>السعة</label>
                    <input type="number" name="capacity" class="form-control" value="<?= (int)($section['capacity'] ?? 30) ?>" min="1">
                </div>
                <div class="form-group">
                    <label>الحالة</label>
                    <select name="is_active" class="form-control">
                        <option value="1" <?= $section['is_active'] ? 'selected' : '' ?>>فعّال</option>
                        <option value="0" <?= !$section['is_active'] ? 'selected' : '' ?>>معطّل</option>
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning"><i class="fas fa-save ml-1"></i> تحديث</button>
                <a href="<?= url('/sections') ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div></div>

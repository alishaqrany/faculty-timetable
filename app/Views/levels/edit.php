<?php
$this->layout('layouts.app');
$__page_title = 'تعديل الفرقة';
$__breadcrumb = [['label' => 'الفرق', 'url' => '/levels'], ['label' => 'تعديل']];
?>

<div class="row"><div class="col-md-8">
    <div class="card card-warning">
        <div class="card-header"><h3 class="card-title">تعديل بيانات الفرقة</h3></div>
        <form method="POST" action="<?= url("/levels/{$level['level_id']}") ?>">
            <?= csrf_field() ?>
            <div class="card-body">
                <div class="form-group">
                    <label>اسم الفرقة <span class="text-danger">*</span></label>
                    <input type="text" name="level_name" class="form-control" value="<?= e($level['level_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>الرمز</label>
                    <input type="text" name="level_code" class="form-control" value="<?= e($level['level_code'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>الترتيب</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= (int)($level['sort_order'] ?? 0) ?>">
                </div>
                <div class="form-group">
                    <label>الحالة</label>
                    <select name="is_active" class="form-control">
                        <option value="1" <?= $level['is_active'] ? 'selected' : '' ?>>فعّال</option>
                        <option value="0" <?= !$level['is_active'] ? 'selected' : '' ?>>معطّل</option>
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning"><i class="fas fa-save ml-1"></i> تحديث</button>
                <a href="<?= url('/levels') ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div></div>

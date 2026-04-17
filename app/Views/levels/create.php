<?php
$this->layout('layouts.app');
$__page_title = 'إضافة مستوى';
$__breadcrumb = [['label' => 'المستويات', 'url' => '/levels'], ['label' => 'إضافة']];
?>

<div class="row"><div class="col-md-8">
    <div class="card card-primary">
        <div class="card-header"><h3 class="card-title">بيانات المستوى الجديد</h3></div>
        <form method="POST" action="<?= url('/levels') ?>">
            <?= csrf_field() ?>
            <div class="card-body">
                <div class="form-group">
                    <label>اسم المستوى <span class="text-danger">*</span></label>
                    <input type="text" name="level_name" class="form-control" value="<?= e(old('level_name')) ?>" required>
                </div>
                <div class="form-group">
                    <label>الرمز</label>
                    <input type="text" name="level_code" class="form-control" value="<?= e(old('level_code')) ?>">
                </div>
                <div class="form-group">
                    <label>الترتيب</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= e(old('sort_order', '0')) ?>">
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                <a href="<?= url('/levels') ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div></div>

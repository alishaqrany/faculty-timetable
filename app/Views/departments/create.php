<?php
$this->layout('layouts.app');
$__page_title = 'إضافة قسم';
$__breadcrumb = [['label' => 'الأقسام', 'url' => '/departments'], ['label' => 'إضافة']];
?>

<div class="row">
    <div class="col-md-8">
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">بيانات القسم الجديد</h3></div>
            <form method="POST" action="<?= url('/departments') ?>">
                <?= csrf_field() ?>
                <div class="card-body">
                    <div class="form-group">
                        <label>اسم القسم <span class="text-danger">*</span></label>
                        <input type="text" name="department_name" class="form-control" value="<?= e(old('department_name')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>رمز القسم</label>
                        <input type="text" name="department_code" class="form-control" value="<?= e(old('department_code')) ?>">
                    </div>
                    <div class="form-group">
                        <label>الوصف</label>
                        <textarea name="description" class="form-control" rows="3"><?= e(old('description')) ?></textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                    <a href="<?= url('/departments') ?>" class="btn btn-secondary">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</div>

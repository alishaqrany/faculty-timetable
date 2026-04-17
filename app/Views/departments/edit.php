<?php
$this->layout('layouts.app');
$__page_title = 'تعديل القسم';
$__breadcrumb = [['label' => 'الأقسام', 'url' => '/departments'], ['label' => 'تعديل']];
?>

<div class="row">
    <div class="col-md-8">
        <div class="card card-warning">
            <div class="card-header"><h3 class="card-title">تعديل بيانات القسم</h3></div>
            <form method="POST" action="<?= url("/departments/{$department['department_id']}") ?>">
                <?= csrf_field() ?>
                <div class="card-body">
                    <div class="form-group">
                        <label>اسم القسم <span class="text-danger">*</span></label>
                        <input type="text" name="department_name" class="form-control"
                               value="<?= e($department['department_name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>رمز القسم</label>
                        <input type="text" name="department_code" class="form-control"
                               value="<?= e($department['department_code'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>الوصف</label>
                        <textarea name="description" class="form-control" rows="3"><?= e($department['description'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>الحالة</label>
                        <select name="is_active" class="form-control">
                            <option value="1" <?= $department['is_active'] ? 'selected' : '' ?>>فعّال</option>
                            <option value="0" <?= !$department['is_active'] ? 'selected' : '' ?>>معطّل</option>
                        </select>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save ml-1"></i> تحديث</button>
                    <a href="<?= url('/departments') ?>" class="btn btn-secondary">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</div>

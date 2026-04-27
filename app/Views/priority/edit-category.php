<?php
$this->layout('layouts.app');
$__page_title = 'تعديل التصنيف';
$__breadcrumb = [
    ['label' => 'إدارة الأولوية', 'url' => '/priority'],
    ['label' => 'التصنيفات', 'url' => '/priority/categories'],
    ['label' => 'تعديل'],
];
?>

<div class="card card-warning card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-edit ml-1"></i> تعديل التصنيف: <?= e($category['category_name']) ?></h3>
    </div>
    <form method="POST" action="<?= url("/priority/categories/{$category['id']}") ?>">
        <?= csrf_field() ?>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>اسم التصنيف <span class="text-danger">*</span></label>
                        <input type="text" name="category_name" class="form-control" value="<?= e($category['category_name']) ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>الوصف</label>
                        <input type="text" name="description" class="form-control" value="<?= e($category['description'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>الترتيب</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= $category['sort_order'] ?>" min="0">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>الحالة</label>
                        <select name="is_active" class="form-control">
                            <option value="1" <?= $category['is_active'] ? 'selected' : '' ?>>نشط</option>
                            <option value="0" <?= !$category['is_active'] ? 'selected' : '' ?>>معطل</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning"><i class="fas fa-save ml-1"></i> حفظ التعديلات</button>
            <a href="<?= url('/priority/categories') ?>" class="btn btn-secondary mr-2">إلغاء</a>
        </div>
    </form>
</div>

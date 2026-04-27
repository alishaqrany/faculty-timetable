<?php
$this->layout('layouts.app');
$__page_title = 'تعديل المجموعة';
$__breadcrumb = [
    ['label' => 'إدارة الأولوية', 'url' => '/priority'],
    ['label' => 'التصنيفات', 'url' => '/priority/categories'],
    ['label' => e($category['category_name']), 'url' => "/priority/categories/{$category['id']}/groups"],
    ['label' => 'تعديل المجموعة'],
];
?>

<div class="card card-warning card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-edit ml-1"></i> تعديل المجموعة: <?= e($group['group_name']) ?></h3>
    </div>
    <form method="POST" action="<?= url("/priority/groups/{$group['id']}") ?>">
        <?= csrf_field() ?>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>اسم المجموعة <span class="text-danger">*</span></label>
                        <input type="text" name="group_name" class="form-control" value="<?= e($group['group_name']) ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>ترتيب الأولوية</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= $group['sort_order'] ?>" min="0">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>الحالة</label>
                        <select name="is_active" class="form-control">
                            <option value="1" <?= $group['is_active'] ? 'selected' : '' ?>>نشط</option>
                            <option value="0" <?= !$group['is_active'] ? 'selected' : '' ?>>معطل</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning"><i class="fas fa-save ml-1"></i> حفظ التعديلات</button>
            <a href="<?= url("/priority/categories/{$category['id']}/groups") ?>" class="btn btn-secondary mr-2">إلغاء</a>
        </div>
    </form>
</div>

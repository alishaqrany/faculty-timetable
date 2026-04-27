<?php
$this->layout('layouts.app');
$__page_title = 'التصنيفات والمجموعات';
$__breadcrumb = [
    ['label' => 'إدارة الأولوية', 'url' => '/priority'],
    ['label' => 'التصنيفات'],
];
?>

<!-- Add Category -->
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-plus ml-1"></i> إنشاء تصنيف جديد</h3>
    </div>
    <form method="POST" action="<?= url('/priority/categories') ?>">
        <?= csrf_field() ?>
        <div class="card-body">
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <label>اسم التصنيف <span class="text-danger">*</span></label>
                        <input type="text" name="category_name" class="form-control" required placeholder="مثال: الدرجة العلمية">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label>الوصف</label>
                        <input type="text" name="description" class="form-control" placeholder="وصف اختياري">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>الترتيب</label>
                        <input type="number" name="sort_order" class="form-control" value="0" min="0">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> إنشاء</button>
        </div>
    </form>
</div>

<!-- Categories List -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-layer-group ml-1"></i> التصنيفات الحالية</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <?php if (!empty($categories)): ?>
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم التصنيف</th>
                    <th>الوصف</th>
                    <th>عدد المجموعات</th>
                    <th>الترتيب</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $i => $cat): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><strong><?= e($cat['category_name']) ?></strong></td>
                    <td><?= e($cat['description'] ?? '') ?></td>
                    <td><span class="badge badge-info"><?= $cat['group_count'] ?></span></td>
                    <td><?= $cat['sort_order'] ?></td>
                    <td>
                        <?php if ($cat['is_active']): ?>
                            <span class="badge badge-success">نشط</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">معطل</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= url("/priority/categories/{$cat['id']}/groups") ?>" class="btn btn-info btn-xs" title="المجموعات">
                            <i class="fas fa-users"></i> المجموعات
                        </a>
                        <a href="<?= url("/priority/categories/{$cat['id']}/edit") ?>" class="btn btn-warning btn-xs" title="تعديل">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="<?= url("/priority/categories/{$cat['id']}/delete") ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger btn-xs btn-delete"
                                    onclick="return confirm('سيتم حذف التصنيف وجميع مجموعاته. هل أنت متأكد؟')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-center text-muted p-4">لا توجد تصنيفات حالياً</p>
        <?php endif; ?>
    </div>
</div>

<?php
$this->layout('layouts.app');
$__page_title = 'مجموعات التصنيف: ' . e($category['category_name']);
$__breadcrumb = [
    ['label' => 'إدارة الأولوية', 'url' => '/priority'],
    ['label' => 'التصنيفات', 'url' => '/priority/categories'],
    ['label' => e($category['category_name'])],
];
?>

<!-- Add Group -->
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-plus ml-1"></i> إنشاء مجموعة جديدة في "<?= e($category['category_name']) ?>"</h3>
    </div>
    <form method="POST" action="<?= url('/priority/groups') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label>اسم المجموعة <span class="text-danger">*</span></label>
                        <input type="text" name="group_name" class="form-control" required placeholder="مثال: أستاذ، أستاذ مشارك...">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>ترتيب الأولوية <small class="text-muted">(الأصغر = أولوية أعلى)</small></label>
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

<!-- Groups List -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users ml-1"></i> المجموعات (مرتبة حسب الأولوية)</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <?php if (!empty($groups)): ?>
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>الأولوية</th>
                    <th>اسم المجموعة</th>
                    <th>عدد الأعضاء</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groups as $group): ?>
                <tr>
                    <td><span class="badge badge-primary"><?= $group['sort_order'] ?></span></td>
                    <td><strong><?= e($group['group_name']) ?></strong></td>
                    <td><span class="badge badge-info"><?= $group['member_count'] ?></span></td>
                    <td>
                        <?php if ($group['is_active']): ?>
                            <span class="badge badge-success">نشط</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">معطل</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= url("/priority/groups/{$group['id']}/members") ?>" class="btn btn-info btn-xs" title="الأعضاء">
                            <i class="fas fa-user-friends"></i> الأعضاء
                        </a>
                        <a href="<?= url("/priority/groups/{$group['id']}/edit") ?>" class="btn btn-warning btn-xs" title="تعديل">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="<?= url("/priority/groups/{$group['id']}/delete") ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger btn-xs btn-delete"
                                    onclick="return confirm('سيتم حذف المجموعة وجميع عضوياتها. هل أنت متأكد؟')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-center text-muted p-4">لا توجد مجموعات في هذا التصنيف</p>
        <?php endif; ?>
    </div>
</div>

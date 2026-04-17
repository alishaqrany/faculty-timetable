<?php
$this->layout('layouts.app');
$__page_title = 'أعضاء هيئة التدريس';
$__breadcrumb = [['label' => 'الأعضاء']];
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">قائمة أعضاء هيئة التدريس</h3>
        <?php if (can('members.create')): ?>
        <div class="card-tools">
            <a href="<?= url('/members/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus ml-1"></i> إضافة عضو
            </a>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped data-table">
            <thead>
                <tr>
                    <th>#</th><th>الاسم</th><th>القسم</th><th>الدرجة العلمية</th>
                    <th>البريد</th><th>الهاتف</th><th>الحالة</th><th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $i => $m): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($m['member_name']) ?></td>
                    <td><?= e($m['department_name'] ?? '—') ?></td>
                    <td><?= e($m['degree_name'] ?? $m['degree'] ?? '—') ?></td>
                    <td><?= e($m['email'] ?? '—') ?></td>
                    <td><?= e($m['phone'] ?? '—') ?></td>
                    <td><span class="badge badge-<?= $m['is_active'] ? 'success' : 'secondary' ?>"><?= $m['is_active'] ? 'فعّال' : 'معطّل' ?></span></td>
                    <td>
                        <?php if (can('members.edit')): ?>
                        <a href="<?= url("/members/{$m['member_id']}/edit") ?>" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a>
                        <?php endif; ?>
                        <?php if (can('members.delete')): ?>
                        <form method="POST" action="<?= url("/members/{$m['member_id']}/delete") ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger btn-xs btn-delete"><i class="fas fa-trash"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

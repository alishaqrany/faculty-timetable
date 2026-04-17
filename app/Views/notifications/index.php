<?php
$this->layout('layouts.app');
$__page_title = 'الإشعارات';
$__breadcrumb = [['label' => 'الإشعارات']];
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-bell ml-1"></i> الإشعارات</h3>
        <div class="card-tools">
            <form method="POST" action="<?= url('/notifications/read-all') ?>" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-check-double ml-1"></i> تعليم الكل كمقروء
                </button>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($notifications)): ?>
        <div class="list-group list-group-flush">
            <?php foreach ($notifications as $n): ?>
            <div class="list-group-item <?= !$n['is_read'] ? 'bg-light' : '' ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <?php if (!$n['is_read']): ?>
                            <span class="badge badge-primary ml-1">جديد</span>
                        <?php endif; ?>
                        <strong><?= e($n['title'] ?? '') ?></strong>
                        <p class="mb-0 text-muted"><?= e($n['message'] ?? '') ?></p>
                        <small class="text-muted"><?= e($n['created_at'] ?? '') ?></small>
                    </div>
                    <div class="btn-group">
                        <?php if (!$n['is_read']): ?>
                        <form method="POST" action="<?= url("/notifications/{$n['id']}/read") ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline-success btn-xs" title="تعليم كمقروء"><i class="fas fa-check"></i></button>
                        </form>
                        <?php endif; ?>
                        <?php if (!empty($n['link'])): ?>
                        <a href="<?= url($n['link']) ?>" class="btn btn-outline-info btn-xs" title="عرض"><i class="fas fa-eye"></i></a>
                        <?php endif; ?>
                        <form method="POST" action="<?= url("/notifications/{$n['id']}/delete") ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline-danger btn-xs btn-delete" title="حذف"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-center text-muted p-4">لا توجد إشعارات</p>
        <?php endif; ?>
    </div>
</div>

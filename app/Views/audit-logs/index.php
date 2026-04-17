<?php
$this->layout('layouts.app');
$__page_title = 'سجل المراجعة';
$__breadcrumb = [['label' => 'سجل المراجعة']];
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-history ml-1"></i> سجل المراجعة والتدقيق</h3>
    </div>

    <!-- Filters -->
    <div class="card-body border-bottom">
        <form method="GET" action="<?= url('/audit-logs') ?>" class="form-inline">
            <div class="form-group ml-2">
                <label class="ml-1">الموديول:</label>
                <input type="text" name="module" class="form-control form-control-sm" value="<?= e($filters['module'] ?? '') ?>" placeholder="مثال: departments">
            </div>
            <div class="form-group ml-2">
                <label class="ml-1">الإجراء:</label>
                <input type="text" name="action" class="form-control form-control-sm" value="<?= e($filters['action'] ?? '') ?>" placeholder="مثال: CREATE">
            </div>
            <button type="submit" class="btn btn-primary btn-sm ml-2"><i class="fas fa-search"></i></button>
            <a href="<?= url('/audit-logs') ?>" class="btn btn-secondary btn-sm mr-2">إعادة تعيين</a>
        </form>
    </div>

    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped">
            <thead>
                <tr><th>#</th><th>التاريخ</th><th>المستخدم</th><th>الإجراء</th><th>الموديول</th><th>المعرّف</th><th>تفاصيل</th></tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $i => $log): ?>
                <tr>
                    <td><?= $log['id'] ?></td>
                    <td><small><?= e($log['created_at'] ?? '') ?></small></td>
                    <td><?= e($log['username'] ?? $log['member_name'] ?? 'نظام') ?></td>
                    <td><span class="badge badge-info"><?= e($log['action']) ?></span></td>
                    <td><?= e($log['module']) ?></td>
                    <td><?= e($log['record_id'] ?? '—') ?></td>
                    <td>
                        <a href="<?= url("/audit-logs/{$log['id']}") ?>" class="btn btn-outline-primary btn-xs"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($pagination) && $pagination['last_page'] > 1): ?>
    <div class="card-footer">
        <?= pagination_html($pagination['current_page'], $pagination['last_page'], '/audit-logs?' . http_build_query(array_filter($filters))) ?>
    </div>
    <?php endif; ?>
</div>

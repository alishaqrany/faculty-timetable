<?php
$this->layout('layouts.app');
$__page_title = 'تفاصيل سجل المراجعة';
$__breadcrumb = [['label' => 'سجل المراجعة', 'url' => '/audit-logs'], ['label' => 'تفاصيل']];
?>

<div class="row"><div class="col-md-8">
    <div class="card card-info">
        <div class="card-header"><h3 class="card-title">سجل #<?= $log['id'] ?></h3></div>
        <div class="card-body">
            <table class="table">
                <tr><th style="width:30%">التاريخ</th><td><?= e($log['created_at'] ?? '') ?></td></tr>
                <tr><th>المستخدم</th><td><?= e($log['username'] ?? $log['member_name'] ?? (string)($log['user_id'] ?? 'نظام')) ?></td></tr>
                <tr><th>الإجراء</th><td><span class="badge badge-<?= e(audit_action_badge_class((string)($log['action'] ?? ''))) ?>"><?= e(audit_action_label((string)($log['action'] ?? ''))) ?></span></td></tr>
                <tr><th>الموديول</th><td><?= e(audit_module_label((string)($log['module'] ?? ''))) ?></td></tr>
                <tr><th>معرف السجل</th><td><?= e($log['record_id'] ?? '—') ?></td></tr>
                <tr><th>عنوان IP</th><td><?= e($log['ip_address'] ?? '') ?></td></tr>
            </table>

            <div class="alert alert-info mt-3 mb-0">
                <strong>الوصف المختصر:</strong>
                <span class="badge badge-light" style="font-size:.92rem; font-weight:500;">
                    <?= e(audit_log_summary($log)) ?>
                </span>
            </div>

            <?php if (!empty($log['old_values'])): ?>
            <h5 class="mt-3">القيم السابقة:</h5>
            <pre class="bg-light p-3 rounded" dir="ltr" style="max-height:300px;overflow:auto;"><?= e($log['old_values']) ?></pre>
            <?php endif; ?>

            <?php if (!empty($log['new_values'])): ?>
            <h5 class="mt-3">القيم الجديدة:</h5>
            <pre class="bg-light p-3 rounded" dir="ltr" style="max-height:300px;overflow:auto;"><?= e($log['new_values']) ?></pre>
            <?php endif; ?>
        </div>
        <div class="card-footer">
            <a href="<?= url('/audit-logs') ?>" class="btn btn-secondary"><i class="fas fa-arrow-right ml-1"></i> رجوع</a>
        </div>
    </div>
</div></div>

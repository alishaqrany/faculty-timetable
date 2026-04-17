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
                <tr><th>المستخدم</th><td><?= e($log['user_id']) ?></td></tr>
                <tr><th>الإجراء</th><td><span class="badge badge-info"><?= e($log['action']) ?></span></td></tr>
                <tr><th>الموديول</th><td><?= e($log['module']) ?></td></tr>
                <tr><th>معرف السجل</th><td><?= e($log['record_id'] ?? '—') ?></td></tr>
                <tr><th>عنوان IP</th><td><?= e($log['ip_address'] ?? '') ?></td></tr>
            </table>

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

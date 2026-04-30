<?php
$this->layout('layouts.app');
$__page_title = 'الحسابات التجريبية';
$__breadcrumb = [
    ['label' => 'الإعدادات', 'url' => '/settings'],
    ['label' => 'الحسابات التجريبية'],
];

$sampleImported = (bool) ($sampleImported ?? false);
$sampleImportedAt = $sampleImportedAt ?? null;
$samplePassword = $samplePassword ?? 'password';
$sampleAccounts = $sampleAccounts ?? [];
$sampleSummary = $sampleSummary ?? [];

$summaryCards = [
    ['label' => 'الفرق', 'value' => (int) ($sampleSummary['levels'] ?? 0), 'class' => 'info'],
    ['label' => 'الأقسام', 'value' => (int) ($sampleSummary['departments'] ?? 0), 'class' => 'primary'],
    ['label' => 'الشعب', 'value' => (int) ($sampleSummary['divisions'] ?? 0), 'class' => 'success'],
    ['label' => 'السكاشن', 'value' => (int) ($sampleSummary['sections'] ?? 0), 'class' => 'warning'],
    ['label' => 'الأعضاء والهيئة المعاونة', 'value' => (int) (($sampleSummary['faculty_members'] ?? 0) + ($sampleSummary['assistants'] ?? 0)), 'class' => 'secondary'],
    ['label' => 'الحسابات الجاهزة', 'value' => (int) ($sampleSummary['accounts'] ?? 0), 'class' => 'dark'],
];
?>

<div class="row">
    <div class="col-12">
        <?php if ($sampleImported): ?>
            <div class="alert alert-success">
                <h5 class="mb-2"><i class="fas fa-check-circle ml-1"></i> تم استيراد البيانات التجريبية</h5>
                <p class="mb-1">الحسابات التالية أصبحت جاهزة للدخول فوراً. كلمة المرور الموحدة لجميع هذه الحسابات هي <code><?= e((string) $samplePassword) ?></code>.</p>
                <?php if (!empty($sampleImportedAt)): ?>
                    <small>وقت آخر استيراد: <?= e((string) $sampleImportedAt) ?></small>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <h5 class="mb-2"><i class="fas fa-info-circle ml-1"></i> هذه صفحة معاينة للحسابات التجريبية</h5>
                <p class="mb-0">يمكنك استيراد البيانات التجريبية مباشرة من هذه الصفحة أو من صفحة الإعدادات، وبعدها ستصبح الحسابات التالية متاحة في قاعدة البيانات الحالية.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row mb-3">
    <?php foreach ($summaryCards as $card): ?>
        <div class="col-md-4 col-lg-2">
            <div class="small-box bg-<?= e($card['class']) ?>">
                <div class="inner">
                    <h3><?= (int) $card['value'] ?></h3>
                    <p><?= e($card['label']) ?></p>
                </div>
                <div class="icon"><i class="fas fa-chart-bar"></i></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card card-dark card-outline">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between" style="gap:.75rem;">
        <h3 class="card-title"><i class="fas fa-users ml-1"></i> الحسابات الجاهزة للتجربة</h3>
        <div class="d-flex flex-wrap" style="gap:.5rem;">
            <a href="<?= url('/settings') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-right ml-1"></i> العودة للإعدادات
            </a>
            <form method="POST" action="<?= url('/settings/data-transfer/import-sample') ?>" class="d-inline-block" onsubmit="return confirm('سيتم إعادة استيراد نفس البيانات التجريبية واستبدال البيانات التشغيلية الحالية. هل تريد المتابعة؟');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-dark btn-sm">
                    <i class="fas fa-file-import ml-1"></i> استيراد البيانات التجريبية الآن
                </button>
            </form>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم المستخدم</th>
                    <th>الدور</th>
                    <th>القسم</th>
                    <th>البريد الإلكتروني</th>
                    <th>كلمة المرور</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($sampleAccounts ?? []) as $index => $account): ?>
                    <tr>
                        <td><?= (int) ($index + 1) ?></td>
                        <td><code><?= e((string) ($account['username'] ?? '')) ?></code></td>
                        <td><?= e((string) ($account['role_label'] ?? '')) ?></td>
                        <td><?= e((string) ($account['department_name'] ?? '')) ?></td>
                        <td><?= e((string) ($account['email'] ?? '')) ?></td>
                        <td><code><?= e((string) $samplePassword) ?></code></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
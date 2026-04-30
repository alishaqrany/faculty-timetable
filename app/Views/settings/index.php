<?php
$this->layout('layouts.app');
$__page_title = 'الإعدادات';
$__breadcrumb = [['label' => 'الإعدادات']];
?>

<form method="POST" action="<?= url('/settings') ?>">
    <?= csrf_field() ?>

    <div class="row">
        <!-- General Settings -->
        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-cog ml-1"></i> الإعدادات العامة</h3></div>
                <div class="card-body">
                    <?php if (!empty($general)): ?>
                        <?php foreach ($general as $s): ?>
                        <div class="form-group">
                            <label><?= e($s['setting_key']) ?></label>
                            <input type="text" name="settings[<?= e($s['setting_key']) ?>]" class="form-control" value="<?= e($s['setting_value'] ?? '') ?>">
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">لا توجد إعدادات عامة</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Scheduling Settings -->
        <div class="col-md-6">
            <div class="card card-success card-outline">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-alt ml-1"></i> إعدادات التسكين</h3></div>
                <div class="card-body">
                    <?php if (!empty($scheduling)): ?>
                        <?php foreach ($scheduling as $s): ?>
                        <div class="form-group">
                            <label><?= e($s['setting_key']) ?></label>
                            <input type="text" name="settings[<?= e($s['setting_key']) ?>]" class="form-control" value="<?= e($s['setting_value'] ?? '') ?>">
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">لا توجد إعدادات تسكين</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save ml-1"></i> حفظ الإعدادات</button>
    </div>
</form>

<div class="row mt-2">
    <div class="col-md-6">
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-database ml-1"></i> النسخ الاحتياطي والاستعادة (SQL)</h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">تصدير أو استيراد جميع بيانات النظام بصيغة SQL (الأقسام، الشعب، الجلسات، الأعوام، الجداول، المستخدمين، الصلاحيات، وكل الجداول).</p>

                <a href="<?= url('/settings/data-transfer/export-sql') ?>" class="btn btn-outline-primary mb-3">
                    <i class="fas fa-download ml-1"></i> تصدير نسخة SQL كاملة
                </a>

                <form method="POST" action="<?= url('/settings/data-transfer/import-sql') ?>" enctype="multipart/form-data" onsubmit="return confirm('سيتم استيراد البيانات من ملف SQL. هل تريد المتابعة؟');">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label>استيراد ملف SQL</label>
                        <input type="file" name="sql_file" class="form-control-file" accept=".sql" required>
                    </div>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-file-import ml-1"></i> استيراد SQL
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-excel ml-1"></i> النسخ الاحتياطي والاستعادة (Excel)</h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">تصدير أو استيراد جميع بيانات النظام بصيغة Excel/XML متعددة الأوراق (ورقة لكل جدول).</p>

                <a href="<?= url('/settings/data-transfer/export-excel') ?>" class="btn btn-outline-success mb-3">
                    <i class="fas fa-download ml-1"></i> تصدير نسخة Excel كاملة
                </a>

                <form method="POST" action="<?= url('/settings/data-transfer/import-excel') ?>" enctype="multipart/form-data" onsubmit="return confirm('استيراد Excel سيستبدل البيانات الحالية. هل تريد المتابعة؟');">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label>استيراد ملف Excel/XML</label>
                        <input type="file" name="excel_file" class="form-control-file" accept=".xls,.xml" required>
                    </div>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-file-import ml-1"></i> استيراد Excel
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card card-secondary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-vial ml-1"></i> ملف بيانات تجريبي</h3>
    </div>
    <div class="card-body">
        <p class="text-muted mb-2">يمكنك تنزيل ملف SQL غنيّ للاختبار يحتوي على 4 فرق دراسية، 4 أقسام، 24 شعبة، 192 سكشن، 64 عضو هيئة تدريس/هيئة معاونة، 64 مقرراً، و24 صفاً جاهزاً في الجدول.</p>
        <p class="text-muted mb-3">يتضمن الملف أيضاً 8 حسابات تجريبية للأقسام بصلاحيات رئيس قسم وعضو هيئة تدريس، وكلمة المرور الموحدة لها هي <code>password</code>.</p>

        <div class="d-flex flex-wrap align-items-center" style="gap:.75rem;">
            <a href="<?= url('/settings/data-transfer/sample-sql') ?>" class="btn btn-outline-dark">
                <i class="fas fa-file-download ml-1"></i> تنزيل ملف البيانات التجريبية
            </a>

            <form method="POST" action="<?= url('/settings/data-transfer/import-sample') ?>" class="d-inline-block" onsubmit="return confirm('سيتم استيراد البيانات التجريبية مباشرة واستبدال البيانات التشغيلية الحالية مع الإبقاء على حسابات المدير. هل تريد المتابعة؟');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-dark">
                    <i class="fas fa-file-import ml-1"></i> استيراد البيانات التجريبية مباشرة
                </button>
            </form>

            <?php if (!empty($sampleDemoMeta['imported'])): ?>
                <a href="<?= url('/settings/data-transfer/sample-accounts') ?>" class="btn btn-outline-info">
                    <i class="fas fa-users ml-1"></i> عرض الحسابات التجريبية
                </a>
            <?php endif; ?>
        </div>

        <?php if (!empty($sampleDemoMeta['imported'])): ?>
            <div class="alert alert-info mt-3 mb-0">
                <strong>آخر استيراد تجريبي:</strong> <?= e((string) ($sampleDemoMeta['imported_at'] ?? '')) ?>
                <?php if (!empty($sampleDemoMeta['accounts_count'])): ?>
                    <span class="d-block d-md-inline mr-md-3">عدد الحسابات الجاهزة: <?= (int) $sampleDemoMeta['accounts_count'] ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card card-danger card-outline mt-4">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-eraser ml-1"></i> إعادة تهيئة النظام</h3>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">يحذف هذا الإجراء البيانات التشغيلية الحالية مثل الأقسام والمقررات والشعب والسكاشن والجداول والتكليفات والتنبيهات، ثم يعيد إنشاء القيم الأساسية اللازمة للتشغيل. سيتم الإبقاء على حسابات المدير والصلاحيات والإعدادات العامة.</p>

        <form method="POST" action="<?= url('/settings/data-transfer/reset-system') ?>" onsubmit="return confirm('سيتم مسح البيانات التشغيلية الحالية وإعادة النظام إلى حالة ابتدائية قابلة للاستخدام مع الإبقاء على حسابات المدير. هل تريد المتابعة؟');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-trash-restore ml-1"></i> إعادة التهيئة ومسح البيانات
            </button>
        </form>
    </div>
</div>

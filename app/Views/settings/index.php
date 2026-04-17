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
                <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-alt ml-1"></i> إعدادات الجدولة</h3></div>
                <div class="card-body">
                    <?php if (!empty($scheduling)): ?>
                        <?php foreach ($scheduling as $s): ?>
                        <div class="form-group">
                            <label><?= e($s['setting_key']) ?></label>
                            <input type="text" name="settings[<?= e($s['setting_key']) ?>]" class="form-control" value="<?= e($s['setting_value'] ?? '') ?>">
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">لا توجد إعدادات جدولة</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save ml-1"></i> حفظ الإعدادات</button>
    </div>
</form>

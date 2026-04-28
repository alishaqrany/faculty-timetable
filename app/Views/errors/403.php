<?php
$this->layout('layouts.app');
$__page_title = 'غير مصرح';
?>

<?php $this->section('content'); ?>
<div class="content-wrapper" style="margin-right: 0;">
    <div class="content">
        <div class="container-fluid">
            <div class="error-page" style="margin-top: 100px;">
                <h2 class="headline text-danger">403</h2>
                <div class="error-content pt-4">
                    <h3><i class="fas fa-exclamation-triangle text-danger"></i> غير مصرح بالوصول</h3>
                    <p>ليس لديك صلاحية للوصول إلى هذه الصفحة.</p>
                    <a href="<?= url($auth ? '/dashboard' : '/') ?>" class="btn btn-primary">
                        <i class="fas fa-home ml-1"></i> العودة للرئيسية
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>

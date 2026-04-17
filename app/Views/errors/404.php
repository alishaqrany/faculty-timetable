<?php $this->layout('layouts.app'); $__page_title = 'خطأ 404'; ?>

<div class="text-center py-5">
    <h1 class="display-1 text-muted">404</h1>
    <h3>الصفحة غير موجودة</h3>
    <p class="text-muted">الصفحة التي تبحث عنها غير موجودة أو تم نقلها.</p>
    <a href="<?= url('/') ?>" class="btn btn-primary mt-3"><i class="fas fa-home ml-1"></i> الرئيسية</a>
</div>

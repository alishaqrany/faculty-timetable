<?php
// flash_success, flash_error, flash_errors are injected by Controller::render()
?>
<?php if (!empty($flash_success)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    toastr.success('<?= e($flash_success) ?>');
});
</script>
<?php endif; ?>

<?php if (!empty($flash_error)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    toastr.error('<?= e($flash_error) ?>');
});
</script>
<?php endif; ?>

<?php if (!empty($flash_errors) && is_array($flash_errors)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0 pr-3">
        <?php foreach ($flash_errors as $err): ?>
            <li><?= e($err) ?></li>
        <?php endforeach; ?>
    </ul>
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
<?php endif; ?>

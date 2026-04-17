<?php $this->layout('layouts.auth'); ?>

<div class="card">
    <div class="card-body login-card-body">
        <p class="login-box-msg">سجّل دخولك للمتابعة</p>

        <?php if (!empty($flash_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= e($flash_error) ?>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($flash_success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= e($flash_success) ?>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('/login') ?>">
            <?= csrf_field() ?>

            <div class="input-group mb-3">
                <input type="text" name="username" class="form-control" placeholder="اسم المستخدم"
                       value="<?= e(old('username')) ?>" required autofocus>
                <div class="input-group-append">
                    <div class="input-group-text"><span class="fas fa-user"></span></div>
                </div>
            </div>

            <div class="input-group mb-3">
                <input type="password" name="password" class="form-control" placeholder="كلمة المرور" required>
                <div class="input-group-append">
                    <div class="input-group-text"><span class="fas fa-lock"></span></div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-sign-in-alt ml-1"></i> تسجيل الدخول
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

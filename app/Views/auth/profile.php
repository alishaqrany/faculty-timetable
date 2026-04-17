<?php
$this->layout('layouts.app');
$__page_title = 'الملف الشخصي';
?>

<div class="row">
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">معلومات الحساب</h3>
            </div>
            <form method="POST" action="<?= url('/profile') ?>">
                <?= csrf_field() ?>
                <div class="card-body">
                    <div class="form-group">
                        <label>اسم المستخدم</label>
                        <input type="text" class="form-control" value="<?= e($user['username']) ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>البريد الإلكتروني</label>
                        <input type="email" name="email" class="form-control" value="<?= e($user['email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>الدور</label>
                        <input type="text" class="form-control" value="<?= e($user['role_name'] ?? '') ?>" disabled>
                    </div>
                    <hr>
                    <h5>تغيير كلمة المرور</h5>
                    <div class="form-group">
                        <label>كلمة المرور الحالية</label>
                        <input type="password" name="current_password" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>كلمة المرور الجديدة</label>
                        <input type="password" name="new_password" class="form-control" minlength="6">
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>
</div>

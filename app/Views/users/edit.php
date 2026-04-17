<?php
$this->layout('layouts.app');
$__page_title = 'تعديل المستخدم';
$__breadcrumb = [['label' => 'المستخدمون', 'url' => '/users'], ['label' => 'تعديل']];
?>

<div class="row"><div class="col-md-8">
    <div class="card card-warning">
        <div class="card-header"><h3 class="card-title">تعديل بيانات المستخدم</h3></div>
        <form method="POST" action="<?= url("/users/{$user['id']}") ?>">
            <?= csrf_field() ?>
            <div class="card-body">
                <div class="form-group">
                    <label>اسم المستخدم <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control" value="<?= e($user['username']) ?>" required>
                </div>
                <div class="form-group">
                    <label>كلمة مرور جديدة <small class="text-muted">(اتركها فارغة إذا لا تريد تغييرها)</small></label>
                    <input type="password" name="password" class="form-control" minlength="6">
                </div>
                <div class="form-group">
                    <label>البريد الإلكتروني</label>
                    <input type="email" name="email" class="form-control" value="<?= e($user['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>الدور <span class="text-danger">*</span></label>
                    <select name="role_id" class="form-control select2" required>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= $user['role_id'] == $r['id'] ? 'selected' : '' ?>><?= e($r['role_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>عضو هيئة التدريس</label>
                    <select name="member_id" class="form-control select2">
                        <option value="">-- بدون ربط --</option>
                        <?php foreach ($members as $m): ?>
                            <option value="<?= $m['member_id'] ?>" <?= ($user['member_id'] ?? '') == $m['member_id'] ? 'selected' : '' ?>><?= e($m['member_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>الحالة</label>
                    <select name="is_active" class="form-control">
                        <option value="1" <?= $user['is_active'] ? 'selected' : '' ?>>فعّال</option>
                        <option value="0" <?= !$user['is_active'] ? 'selected' : '' ?>>معطّل</option>
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning"><i class="fas fa-save ml-1"></i> تحديث</button>
                <a href="<?= url('/users') ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div></div>

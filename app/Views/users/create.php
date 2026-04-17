<?php
$this->layout('layouts.app');
$__page_title = 'إضافة مستخدم';
$__breadcrumb = [['label' => 'المستخدمون', 'url' => '/users'], ['label' => 'إضافة']];
?>

<div class="row"><div class="col-md-8">
    <div class="card card-primary">
        <div class="card-header"><h3 class="card-title">بيانات المستخدم الجديد</h3></div>
        <form method="POST" action="<?= url('/users') ?>">
            <?= csrf_field() ?>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>اسم المستخدم <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" value="<?= e(old('username')) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>كلمة المرور <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>البريد الإلكتروني</label>
                    <input type="email" name="email" class="form-control" value="<?= e(old('email')) ?>">
                </div>
                <div class="form-group">
                    <label>الدور <span class="text-danger">*</span></label>
                    <select name="role_id" class="form-control select2" required>
                        <option value="">-- اختر --</option>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= old('role_id') == $r['id'] ? 'selected' : '' ?>><?= e($r['role_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>عضو هيئة التدريس (اختياري)</label>
                    <select name="member_id" class="form-control select2">
                        <option value="">-- بدون ربط --</option>
                        <?php foreach ($members as $m): ?>
                            <option value="<?= $m['member_id'] ?>" <?= old('member_id') == $m['member_id'] ? 'selected' : '' ?>><?= e($m['member_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                <a href="<?= url('/users') ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div></div>

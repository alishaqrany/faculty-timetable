<?php
$this->layout('layouts.app');
$__page_title = 'الإشعارات';
$__breadcrumb = [['label' => 'الإشعارات']];
?>

<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bell ml-1"></i> الإشعارات</h3>
                <div class="card-tools">
                    <form method="POST" action="<?= url('/notifications/read-all') ?>" class="d-inline">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-check-double ml-1"></i> تعليم الكل كمقروء
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($notifications)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($notifications as $n): ?>
                    <div class="list-group-item <?= !$n['is_read'] ? 'bg-light' : '' ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <?php if (!$n['is_read']): ?>
                                    <span class="badge badge-primary ml-1">جديد</span>
                                <?php endif; ?>
                                <strong><?= e($n['title'] ?? '') ?></strong>
                                <p class="mb-0 text-muted"><?= e($n['message'] ?? '') ?></p>
                                <small class="text-muted"><?= e($n['created_at'] ?? '') ?></small>
                            </div>
                            <div class="btn-group">
                                <?php if (!$n['is_read']): ?>
                                <form method="POST" action="<?= url("/notifications/{$n['id']}/read") ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-outline-success btn-xs" title="تعليم كمقروء"><i class="fas fa-check"></i></button>
                                </form>
                                <?php endif; ?>
                                <?php if (!empty($n['link'])): ?>
                                <a href="<?= url($n['link']) ?>" class="btn btn-outline-info btn-xs" title="عرض"><i class="fas fa-eye"></i></a>
                                <?php endif; ?>
                                <form method="POST" action="<?= url("/notifications/{$n['id']}/delete") ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-outline-danger btn-xs btn-delete" title="حذف"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-center text-muted p-4">لا توجد إشعارات</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (!empty($canSend)): ?>
    <div class="col-lg-5">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-paper-plane ml-1"></i> إرسال إشعار</h3>
            </div>
            <form method="POST" action="<?= url('/notifications/send') ?>">
                <?= csrf_field() ?>
                <div class="card-body">
                    <div class="form-group">
                        <label>إلى</label>
                        <select name="target_type" id="target_type" class="form-control">
                            <option value="all">كل المستخدمين</option>
                            <option value="role">دور محدد</option>
                            <option value="user">مستخدم محدد</option>
                        </select>
                    </div>

                    <div class="form-group d-none" id="role_group">
                        <label>الدور</label>
                        <select id="role_id" class="form-control select2" disabled>
                            <option value="">-- اختر الدور --</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>"><?= e($role['role_name']) ?> (<?= e($role['role_slug']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group d-none" id="user_group">
                        <label>المستخدم</label>
                        <select id="user_id" class="form-control select2" disabled>
                            <option value="">-- اختر المستخدم --</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>"><?= e($user['username']) ?><?= !empty($user['role_name']) ? ' - ' . e($user['role_name']) : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>العنوان</label>
                        <input type="text" name="title" class="form-control" maxlength="200" required>
                    </div>

                    <div class="form-group">
                        <label>الرسالة</label>
                        <textarea name="message" class="form-control" rows="4" maxlength="2000"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>النوع</label>
                            <select name="type" class="form-control">
                                <option value="info">معلومات</option>
                                <option value="success">نجاح</option>
                                <option value="warning">تحذير</option>
                                <option value="danger">خطر</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>رابط اختياري</label>
                            <input type="text" name="link" class="form-control" placeholder="/scheduling">
                        </div>
                    </div>
                </div>
                <div class="card-footer text-left">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane ml-1"></i> إرسال
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if (!empty($canSend)): ?>
<?php $this->section('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var targetType = document.getElementById('target_type');
    var roleGroup = document.getElementById('role_group');
    var userGroup = document.getElementById('user_group');
    var roleSelect = document.getElementById('role_id');
    var userSelect = document.getElementById('user_id');

    function syncTargetFields() {
        var value = targetType.value;
        roleGroup.classList.toggle('d-none', value !== 'role');
        userGroup.classList.toggle('d-none', value !== 'user');
        roleSelect.disabled = value !== 'role';
        userSelect.disabled = value !== 'user';
    }

    targetType.addEventListener('change', syncTargetFields);
    syncTargetFields();
});
</script>
<?php $this->endSection(); ?>
<?php endif; ?>

<?php
$this->layout('layouts.app');
$__page_title = 'إدارة الاستثناءات';
$__breadcrumb = [
    ['label' => 'إدارة الأولوية', 'url' => '/priority'],
    ['label' => 'الاستثناءات'],
];
?>

<!-- Grant Exception -->
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user-shield ml-1"></i> منح استثناء جديد</h3>
    </div>
    <form method="POST" action="<?= url('/priority/exceptions') ?>" id="exceptionForm">
        <?= csrf_field() ?>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>نوع الاستثناء</label>
                        <select name="exception_type" id="exceptionType" class="form-control" onchange="toggleExceptionFields()">
                            <option value="member">عضو محدد</option>
                            <option value="group">مجموعة كاملة</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3" id="memberField">
                    <div class="form-group">
                        <label>اختر العضو <span class="text-danger">*</span></label>
                        <select name="member_id" class="form-control select2">
                            <option value="">-- اختر --</option>
                            <?php foreach ($allMembers as $m): ?>
                            <option value="<?= $m['member_id'] ?>"><?= e($m['member_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 d-none" id="groupField">
                    <div class="form-group">
                        <label>اختر المجموعة <span class="text-danger">*</span></label>
                        <select name="group_id" class="form-control select2">
                            <option value="">-- اختر --</option>
                            <?php foreach ($allGroups as $g): ?>
                            <option value="<?= $g['id'] ?>"><?= e($g['category_name']) ?> → <?= e($g['group_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>السبب</label>
                        <input type="text" name="reason" class="form-control" placeholder="سبب اختياري">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>تاريخ الانتهاء <small class="text-muted">(اختياري)</small></label>
                        <input type="datetime-local" name="expires_at" class="form-control">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-shield-alt ml-1"></i> منح الاستثناء</button>
        </div>
    </form>
</div>

<!-- Exceptions List -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list ml-1"></i> الاستثناءات</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <?php if (!empty($exceptions)): ?>
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>النوع</th>
                    <th>العضو / المجموعة</th>
                    <th>السبب</th>
                    <th>منح بواسطة</th>
                    <th>تاريخ المنح</th>
                    <th>ينتهي في</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($exceptions as $i => $ex): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                        <?php if ($ex['exception_type'] === 'member'): ?>
                            <span class="badge badge-primary">عضو</span>
                        <?php else: ?>
                            <span class="badge badge-info">مجموعة</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($ex['exception_type'] === 'member'): ?>
                            <?= e($ex['member_name'] ?? '—') ?>
                        <?php else: ?>
                            <?= e($ex['group_name'] ?? '—') ?>
                        <?php endif; ?>
                    </td>
                    <td><?= e($ex['reason'] ?? '—') ?></td>
                    <td><?= e($ex['granted_by_name'] ?? '—') ?></td>
                    <td><?= $ex['created_at'] ?></td>
                    <td><?= $ex['expires_at'] ?? 'بدون انتهاء' ?></td>
                    <td>
                        <?php if ($ex['is_active']): ?>
                            <span class="badge badge-success">نشط</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">ملغي</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($ex['is_active']): ?>
                        <form method="POST" action="<?= url("/priority/exceptions/{$ex['id']}/revoke") ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger btn-xs"
                                    onclick="return confirm('هل تريد إلغاء هذا الاستثناء؟')">
                                <i class="fas fa-ban"></i> إلغاء
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-center text-muted p-4">لا توجد استثناءات</p>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleExceptionFields() {
    var type = document.getElementById('exceptionType').value;
    var memberField = document.getElementById('memberField');
    var groupField = document.getElementById('groupField');

    if (type === 'member') {
        memberField.classList.remove('d-none');
        groupField.classList.add('d-none');
    } else {
        memberField.classList.add('d-none');
        groupField.classList.remove('d-none');
    }
}
</script>

<?php
$this->layout('layouts.app');
$__page_title = 'ترتيب أولوية الأقسام';
$__breadcrumb = [
    ['label' => 'إدارة الأولوية', 'url' => '/priority'],
    ['label' => 'ترتيب الأقسام'],
];
?>

<?php if (empty($deptOrder)): ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle ml-1"></i> لا توجد أقسام نشطة. يرجى إضافة أقسام أولاً.
</div>
<?php else: ?>

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-sort ml-1"></i> ترتيب أولوية الأقسام</h3>
        <div class="card-tools">
            <span class="badge badge-info">القسم الحالي: <?= $currentDept ? e($currentDept['department_name']) : 'غير محدد' ?></span>
        </div>
    </div>
    <form method="POST" action="<?= url('/priority/dept-order') ?>">
        <?= csrf_field() ?>
        <div class="card-body p-0">
            <table class="table table-hover" id="deptOrderTable">
                <thead>
                    <tr>
                        <th style="width: 60px">الترتيب</th>
                        <th>القسم</th>
                        <th>الكود</th>
                        <th>الحالة</th>
                        <th style="width: 120px">تحريك</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deptOrder as $i => $dept): ?>
                    <tr data-id="<?= $dept['department_id'] ?>"
                        class="<?= ($currentDept && $dept['department_id'] == $currentDept['department_id']) ? 'table-success' : '' ?>">
                        <td>
                            <span class="badge badge-primary order-badge"><?= $i + 1 ?></span>
                            <input type="hidden" name="order[]" value="<?= $dept['department_id'] ?>">
                        </td>
                        <td><strong><?= e($dept['department_name']) ?></strong></td>
                        <td><?= e($dept['department_code'] ?? '—') ?></td>
                        <td>
                            <?php if ($dept['is_completed']): ?>
                                <span class="badge badge-success"><i class="fas fa-check"></i> مكتمل</span>
                            <?php elseif ($currentDept && $dept['department_id'] == $currentDept['department_id']): ?>
                                <span class="badge badge-warning"><i class="fas fa-play"></i> جاري</span>
                            <?php else: ?>
                                <span class="badge badge-secondary"><i class="fas fa-clock"></i> انتظار</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-move-up" title="تحريك للأعلى"><i class="fas fa-arrow-up"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-move-down" title="تحريك للأسفل"><i class="fas fa-arrow-down"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ الترتيب</button>
            <a href="<?= url('/priority') ?>" class="btn btn-secondary mr-2">رجوع</a>
        </div>
    </form>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var table = document.getElementById('deptOrderTable');
    if (!table) return;

    table.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-move-up, .btn-move-down');
        if (!btn) return;

        var row = btn.closest('tr');
        var tbody = row.parentNode;

        if (btn.classList.contains('btn-move-up') && row.previousElementSibling) {
            tbody.insertBefore(row, row.previousElementSibling);
        } else if (btn.classList.contains('btn-move-down') && row.nextElementSibling) {
            tbody.insertBefore(row.nextElementSibling, row);
        }

        // Update order badges
        var rows = tbody.querySelectorAll('tr');
        rows.forEach(function(r, i) {
            var badge = r.querySelector('.order-badge');
            if (badge) badge.textContent = i + 1;
        });
    });
});
</script>

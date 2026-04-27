<?php
$this->layout('layouts.app');
$__page_title = 'أعضاء المجموعة: ' . e($group['group_name']);
$__breadcrumb = [
    ['label' => 'إدارة الأولوية', 'url' => '/priority'],
    ['label' => 'التصنيفات', 'url' => '/priority/categories'],
    ['label' => e($category['category_name']), 'url' => "/priority/categories/{$category['id']}/groups"],
    ['label' => e($group['group_name'])],
];

// Build existing member IDs for filtering
$existingMemberIds = array_column($members, 'member_id');
?>

<!-- Add Member -->
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user-plus ml-1"></i> إضافة عضو إلى "<?= e($group['group_name']) ?>"</h3>
    </div>
    <form method="POST" action="<?= url("/priority/groups/{$group['id']}/members") ?>">
        <?= csrf_field() ?>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label>اختر العضو <span class="text-danger">*</span></label>
                        <select name="member_id" class="form-control select2" required>
                            <option value="">-- اختر عضواً --</option>
                            <?php foreach ($allMembers as $m): ?>
                                <?php if (!in_array($m['member_id'], $existingMemberIds)): ?>
                                <option value="<?= $m['member_id'] ?>">
                                    <?= e($m['member_name']) ?>
                                    <?php if ($m['department_name']): ?> — <?= e($m['department_name']) ?><?php endif; ?>
                                    <?php if ($m['degree_name']): ?> (<?= e($m['degree_name']) ?>)<?php endif; ?>
                                </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary mb-3"><i class="fas fa-plus ml-1"></i> إضافة</button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Members List -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users ml-1"></i> الأعضاء (<?= count($members) ?>)</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <?php if (!empty($members)): ?>
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم العضو</th>
                    <th>القسم</th>
                    <th>الدرجة العلمية</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $i => $m): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($m['member_name']) ?></td>
                    <td><?= e($m['department_name'] ?? '—') ?></td>
                    <td><?= e($m['degree_name'] ?? '—') ?></td>
                    <td>
                        <form method="POST" action="<?= url("/priority/group-members/{$m['pivot_id']}/delete") ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger btn-xs"
                                    onclick="return confirm('هل تريد إزالة هذا العضو من المجموعة؟')">
                                <i class="fas fa-user-minus"></i> إزالة
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-center text-muted p-4">لا يوجد أعضاء في هذه المجموعة</p>
        <?php endif; ?>
    </div>
</div>

<?php
$this->layout('layouts.app');
$__page_title = 'الفصول الدراسية';
$__breadcrumb = [['label' => 'الفصول الدراسية']];
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">قائمة الفصول الدراسية</h3>
        <?php if (can('semesters.manage')): ?>
        <div class="card-tools">
            <a href="<?= url('/semesters/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus ml-1"></i> إضافة فصل دراسي
            </a>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-hover datatable">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th>اسم الفصل</th>
                    <th>السنة الأكاديمية</th>
                    <th>تاريخ البداية</th>
                    <th>تاريخ النهاية</th>
                    <th>الحالة</th>
                    <th width="15%">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($semesters as $i => $s): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                        <?= e($s['semester_name']) ?>
                        <?php if ($s['is_current']): ?>
                            <span class="badge badge-success mr-1">الحالي</span>
                        <?php endif; ?>
                    </td>
                    <td><?= e($s['year_name']) ?></td>
                    <td><?= e($s['start_date']) ?></td>
                    <td><?= e($s['end_date']) ?></td>
                    <td>
                        <?php if ($s['is_active'] ?? 1): ?>
                            <span class="badge badge-success">مفعّل</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">معطّل</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (can('semesters.manage')): ?>
                            <?php if (!$s['is_current']): ?>
                            <form method="POST" action="<?= url("/semesters/{$s['id']}/set-current") ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-success btn-xs" title="تعيين كحالي">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                            <a href="<?= url("/semesters/{$s['id']}/edit") ?>" class="btn btn-warning btn-xs">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="<?= url("/semesters/{$s['id']}/delete") ?>" class="d-inline btn-delete">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-xs"><i class="fas fa-trash"></i></button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$this->layout('layouts.app');
$__page_title = 'السنوات الأكاديمية';
$__breadcrumb = [['label' => 'السنوات الأكاديمية']];
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">قائمة السنوات الأكاديمية</h3>
        <?php if (can('academic_years.manage')): ?>
        <div class="card-tools">
            <a href="<?= url('/academic-years/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus ml-1"></i> إضافة سنة أكاديمية
            </a>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-hover datatable">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th>اسم السنة</th>
                    <th>تاريخ البداية</th>
                    <th>تاريخ النهاية</th>
                    <th>الفصول الدراسية</th>
                    <th>الحالة</th>
                    <th width="15%">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($years as $i => $y): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                        <?= e($y['year_name']) ?>
                        <?php if ($y['is_current']): ?>
                            <span class="badge badge-success mr-1">الحالية</span>
                        <?php endif; ?>
                    </td>
                    <td><?= e($y['start_date']) ?></td>
                    <td><?= e($y['end_date']) ?></td>
                    <td><span class="badge badge-info"><?= $y['semester_count'] ?></span></td>
                    <td>
                        <?php if ($y['is_active'] ?? 1): ?>
                            <span class="badge badge-success">مفعّلة</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">معطّلة</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (can('academic_years.manage')): ?>
                            <?php if (!$y['is_current']): ?>
                            <form method="POST" action="<?= url("/academic-years/{$y['id']}/set-current") ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-success btn-xs" title="تعيين كحالية">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                            <a href="<?= url("/academic-years/{$y['id']}/edit") ?>" class="btn btn-warning btn-xs">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="<?= url("/academic-years/{$y['id']}/delete") ?>" class="d-inline btn-delete">
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

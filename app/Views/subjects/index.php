<?php
$this->layout('layouts.app');
$__page_title = 'إدارة المقررات';
$__breadcrumb = [['label' => 'المقررات']];
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">قائمة المقررات الدراسية</h3>
        <?php if (can('subjects.create')): ?>
        <div class="card-tools">
            <a href="<?= url('/subjects/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus ml-1"></i> إضافة مقرر
            </a>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped data-table">
            <thead>
                <tr>
                    <th>#</th><th>اسم المقرر</th><th>الرمز</th><th>القسم</th><th>المستوى</th>
                    <th>الساعات</th><th>النوع</th><th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $i => $s): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($s['subject_name']) ?></td>
                    <td><?= e($s['subject_code'] ?? '—') ?></td>
                    <td><?= e($s['department_name'] ?? '') ?></td>
                    <td><?= e($s['level_name'] ?? '') ?></td>
                    <td><?= (int)($s['hours'] ?? 0) ?></td>
                    <td><?= e($s['subject_type'] ?? '—') ?></td>
                    <td>
                        <?php if (can('subjects.edit')): ?>
                        <a href="<?= url("/subjects/{$s['subject_id']}/edit") ?>" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a>
                        <?php endif; ?>
                        <?php if (can('subjects.delete')): ?>
                        <form method="POST" action="<?= url("/subjects/{$s['subject_id']}/delete") ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger btn-xs btn-delete"><i class="fas fa-trash"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

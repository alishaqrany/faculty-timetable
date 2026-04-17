<?php
$this->layout('layouts.app');
$__page_title = 'إدارة الشُعب';
$__breadcrumb = [['label' => 'الشُعب']];
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">قائمة الشُعب</h3>
        <?php if (can('sections.create')): ?>
        <div class="card-tools">
            <a href="<?= url('/divisions/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus ml-1"></i> إضافة شعبة
            </a>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>الشعبة:</strong> هي تقسيم للفرقة - قد تكون شعبة واحدة (عامة) أو عدة شعب (أ، ب، ج...)
            <br>
            <small>الطلاب في الشعبة يحضرون المحاضرات النظرية معًا، ثم ينقسمون إلى سكاشن للعملي.</small>
        </div>
        
        <table class="table table-hover table-striped data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم الشعبة</th>
                    <th>القسم</th>
                    <th>الفرقة</th>
                    <th>عدد السكاشن</th>
                    <th>السعة</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($divisions as $i => $div): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                        <i class="fas fa-users text-primary"></i>
                        <strong><?= e($div['division_name']) ?></strong>
                    </td>
                    <td><?= e($div['department_name'] ?? '') ?></td>
                    <td><?= e($div['level_name'] ?? '') ?></td>
                    <td>
                        <span class="badge badge-info"><?= (int)($div['sections_count'] ?? 0) ?> سكشن</span>
                    </td>
                    <td><?= (int)($div['capacity'] ?? 0) ?></td>
                    <td>
                        <span class="badge badge-<?= $div['is_active'] ? 'success' : 'secondary' ?>">
                            <?= $div['is_active'] ? 'فعّالة' : 'معطّلة' ?>
                        </span>
                    </td>
                    <td>
                        <?php if (can('sections.edit')): ?>
                        <a href="<?= url("/divisions/{$div['division_id']}/edit") ?>" class="btn btn-warning btn-xs" title="تعديل">
                            <i class="fas fa-edit"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (can('sections.delete')): ?>
                        <form method="POST" action="<?= url("/divisions/{$div['division_id']}/delete") ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger btn-xs btn-delete" title="حذف">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

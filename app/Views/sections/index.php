<?php
$this->layout('layouts.app');
$__page_title = 'إدارة السكاشن (مجموعات العملي)';
$__breadcrumb = [['label' => 'السكاشن']];
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">قائمة السكاشن</h3>
        <?php if (can('sections.create')): ?>
        <div class="card-tools">
            <a href="<?= url('/sections/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus ml-1"></i> إضافة سكشن
            </a>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>السكشن:</strong> هو مجموعة عملي داخل الشعبة - لتقسيم الطلاب في المعامل.
            <br>
            <small>كل شعبة يمكن أن تحتوي على عدة سكاشن (سكشن 1، سكشن 2، ...)</small>
        </div>
        
        <?php if (empty($groupedSections)): ?>
        <div class="alert alert-warning">
            لا توجد سكاشن مسجلة. 
            <a href="<?= url('/divisions') ?>">أضف شعبة أولاً</a> ثم أضف سكاشن لها.
        </div>
        <?php else: ?>
        
        <?php foreach ($groupedSections as $group): ?>
        <div class="card card-outline card-secondary mb-3">
            <div class="card-header py-2">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users text-primary"></i>
                    <?= e($group['division_name']) ?>
                    <small class="text-muted mr-2">
                        (<?= e($group['department_name'] ?? '') ?> - <?= e($group['level_name'] ?? '') ?>)
                    </small>
                </h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width:30px"></th>
                            <th>اسم السكشن</th>
                            <th>السعة</th>
                            <th>الحالة</th>
                            <th style="width:100px">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($group['sections'] as $sec): ?>
                        <tr>
                            <td><i class="fas fa-layer-group text-secondary"></i></td>
                            <td><?= e($sec['section_name']) ?></td>
                            <td><?= (int)($sec['capacity'] ?? 0) ?></td>
                            <td>
                                <span class="badge badge-<?= $sec['is_active'] ? 'success' : 'secondary' ?>">
                                    <?= $sec['is_active'] ? 'فعّال' : 'معطّل' ?>
                                </span>
                            </td>
                            <td>
                                <?php if (can('sections.edit')): ?>
                                <a href="<?= url("/sections/{$sec['section_id']}/edit") ?>" class="btn btn-warning btn-xs">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (can('sections.delete')): ?>
                                <form method="POST" action="<?= url("/sections/{$sec['section_id']}/delete") ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-danger btn-xs btn-delete">
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
        <?php endforeach; ?>
        
        <?php endif; ?>
    </div>
</div>

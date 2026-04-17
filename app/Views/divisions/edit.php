<?php
$this->layout('layouts.app');
$__page_title = 'تعديل الشعبة';
$__breadcrumb = [['label' => 'الشُعب', 'url' => '/divisions'], ['label' => 'تعديل']];
?>

<div class="row">
    <div class="col-md-8">
        <div class="card card-warning">
            <div class="card-header"><h3 class="card-title">تعديل بيانات الشعبة</h3></div>
            <form method="POST" action="<?= url("/divisions/{$division['division_id']}") ?>">
                <?= csrf_field() ?>
                <div class="card-body">
                    <div class="form-group">
                        <label>اسم الشعبة <span class="text-danger">*</span></label>
                        <input type="text" name="division_name" class="form-control" value="<?= e($division['division_name']) ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>القسم <span class="text-danger">*</span></label>
                                <select name="department_id" class="form-control select2" required>
                                    <option value="">-- اختر القسم --</option>
                                    <?php foreach ($departments as $d): ?>
                                        <option value="<?= $d['department_id'] ?>" <?= $division['department_id'] == $d['department_id'] ? 'selected' : '' ?>>
                                            <?= e($d['department_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>الفرقة <span class="text-danger">*</span></label>
                                <select name="level_id" class="form-control select2" required>
                                    <option value="">-- اختر الفرقة --</option>
                                    <?php foreach ($levels as $l): ?>
                                        <option value="<?= $l['level_id'] ?>" <?= $division['level_id'] == $l['level_id'] ? 'selected' : '' ?>>
                                            <?= e($l['level_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>السعة (عدد الطلاب)</label>
                        <input type="number" name="capacity" class="form-control" value="<?= (int)($division['capacity'] ?? 100) ?>" min="1">
                    </div>
                    <div class="form-group">
                        <label>الحالة</label>
                        <select name="is_active" class="form-control">
                            <option value="1" <?= $division['is_active'] ? 'selected' : '' ?>>فعّالة</option>
                            <option value="0" <?= !$division['is_active'] ? 'selected' : '' ?>>معطّلة</option>
                        </select>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save ml-1"></i> تحديث</button>
                    <a href="<?= url('/divisions') ?>" class="btn btn-secondary">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">السكاشن التابعة</h3>
                <div class="card-tools">
                    <?php if (can('sections.create')): ?>
                    <a href="<?= url('/sections/create?division_id=' . $division['division_id']) ?>" class="btn btn-tool">
                        <i class="fas fa-plus"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($sections)): ?>
                <div class="p-3 text-muted text-center">
                    <i class="fas fa-layer-group fa-2x mb-2"></i>
                    <p>لا توجد سكاشن</p>
                    <?php if (can('sections.create')): ?>
                    <a href="<?= url('/sections/create?division_id=' . $division['division_id']) ?>" class="btn btn-sm btn-outline-primary">
                        إضافة سكشن
                    </a>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($sections as $sec): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fas fa-layer-group text-secondary"></i>
                            <?= e($sec['section_name']) ?>
                        </span>
                        <span>
                            <span class="badge badge-info">سعة: <?= (int)$sec['capacity'] ?></span>
                            <?php if (can('sections.edit')): ?>
                            <a href="<?= url("/sections/{$sec['section_id']}/edit") ?>" class="btn btn-xs btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                        </span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

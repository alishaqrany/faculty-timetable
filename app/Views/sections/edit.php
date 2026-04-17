<?php
$this->layout('layouts.app');
$__page_title = 'تعديل السكشن';
$__breadcrumb = [['label' => 'السكاشن', 'url' => '/sections'], ['label' => 'تعديل']];
?>

<div class="row"><div class="col-md-8">
    <div class="card card-warning">
        <div class="card-header"><h3 class="card-title">تعديل بيانات السكشن</h3></div>
        <form method="POST" action="<?= url("/sections/{$section['section_id']}") ?>">
            <?= csrf_field() ?>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-layer-group"></i> 
                    <strong>السكشن</strong> هو مجموعة عملي داخل الشعبة لتقسيم الطلاب في المعامل.
                </div>
                
                <div class="form-group">
                    <label>الشعبة التابع لها <span class="text-danger">*</span></label>
                    <select name="division_id" class="form-control select2" required>
                        <option value="">-- اختر الشعبة --</option>
                        <?php foreach ($divisions ?? [] as $div): ?>
                            <option value="<?= $div['division_id'] ?>" <?= ($section['division_id'] ?? '') == $div['division_id'] ? 'selected' : '' ?>>
                                <?= e($div['division_name']) ?> — <?= e($div['department_name']) ?> / <?= e($div['level_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>اسم السكشن <span class="text-danger">*</span></label>
                    <input type="text" name="section_name" class="form-control" value="<?= e($section['section_name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label>السعة (عدد الطلاب)</label>
                    <input type="number" name="capacity" class="form-control" value="<?= (int)($section['capacity'] ?? 25) ?>" min="1">
                </div>
                
                <div class="form-group">
                    <label>الحالة</label>
                    <select name="is_active" class="form-control">
                        <option value="1" <?= $section['is_active'] ? 'selected' : '' ?>>فعّال</option>
                        <option value="0" <?= !$section['is_active'] ? 'selected' : '' ?>>معطّل</option>
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning"><i class="fas fa-save ml-1"></i> تحديث</button>
                <a href="<?= url('/sections') ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div></div>

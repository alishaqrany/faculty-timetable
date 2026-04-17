<?php
$this->layout('layouts.app');
$__page_title = 'إضافة سكشن';
$__breadcrumb = [['label' => 'السكاشن', 'url' => '/sections'], ['label' => 'إضافة']];

// Pre-select division if passed via query string
$preselectedDivision = $_GET['division_id'] ?? old('division_id');
?>

<div class="row"><div class="col-md-8">
    <div class="card card-primary">
        <div class="card-header"><h3 class="card-title">بيانات السكشن الجديد</h3></div>
        <form method="POST" action="<?= url('/sections') ?>">
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
                            <option value="<?= $div['division_id'] ?>" <?= $preselectedDivision == $div['division_id'] ? 'selected' : '' ?>>
                                <?= e($div['division_name']) ?> — <?= e($div['department_name']) ?> / <?= e($div['level_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">
                        لا توجد شعب؟ 
                        <a href="<?= url('/divisions/create') ?>">أضف شعبة أولاً</a>
                    </small>
                </div>
                
                <div class="form-group">
                    <label>اسم السكشن <span class="text-danger">*</span></label>
                    <input type="text" name="section_name" class="form-control" value="<?= e(old('section_name')) ?>" required
                           placeholder="مثال: سكشن 1، سكشن 2، مجموعة أ">
                </div>
                
                <div class="form-group">
                    <label>السعة (عدد الطلاب)</label>
                    <input type="number" name="capacity" class="form-control" value="<?= e(old('capacity', '25')) ?>" min="1">
                    <small class="text-muted">عادة تكون سعة السكشن أقل من سعة الشعبة</small>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                <a href="<?= url('/sections') ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div></div>

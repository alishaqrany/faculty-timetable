<?php
$this->layout('layouts.app');
$__page_title = 'تعديل القاعة';
$__breadcrumb = [['label' => 'القاعات', 'url' => '/classrooms'], ['label' => 'تعديل']];
?>

<div class="row"><div class="col-md-8">
    <div class="card card-warning">
        <div class="card-header"><h3 class="card-title">تعديل بيانات القاعة</h3></div>
        <form method="POST" action="<?= url("/classrooms/{$classroom['classroom_id']}") ?>">
            <?= csrf_field() ?>
            <div class="card-body">
                <div class="form-group">
                    <label>اسم القاعة <span class="text-danger">*</span></label>
                    <input type="text" name="classroom_name" class="form-control" value="<?= e($classroom['classroom_name']) ?>" required>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>نوع القاعة</label>
                            <select name="classroom_type" class="form-control">
                                <?php foreach (['قاعة', 'معمل', 'مدرج', 'قاعة اجتماعات'] as $t): ?>
                                    <option value="<?= $t ?>" <?= ($classroom['classroom_type'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>السعة</label>
                            <input type="number" name="capacity" class="form-control" value="<?= (int)($classroom['capacity'] ?? 40) ?>" min="1">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>المبنى</label>
                            <input type="text" name="building" class="form-control" value="<?= e($classroom['building'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>الطابق</label>
                            <input type="text" name="floor" class="form-control" value="<?= e($classroom['floor'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>الحالة</label>
                    <select name="is_active" class="form-control">
                        <option value="1" <?= $classroom['is_active'] ? 'selected' : '' ?>>فعّال</option>
                        <option value="0" <?= !$classroom['is_active'] ? 'selected' : '' ?>>معطّل</option>
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning"><i class="fas fa-save ml-1"></i> تحديث</button>
                <a href="<?= url('/classrooms') ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div></div>

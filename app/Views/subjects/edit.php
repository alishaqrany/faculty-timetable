<?php
$this->layout('layouts.app');
$__page_title = 'تعديل المقرر';
$__breadcrumb = [['label' => 'المقررات', 'url' => '/subjects'], ['label' => 'تعديل']];
?>

<div class="row"><div class="col-md-8">
    <div class="card card-warning">
        <div class="card-header"><h3 class="card-title">تعديل بيانات المقرر</h3></div>
        <form method="POST" action="<?= url("/subjects/{$subject['subject_id']}") ?>">
            <?= csrf_field() ?>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>اسم المقرر <span class="text-danger">*</span></label>
                            <input type="text" name="subject_name" class="form-control" value="<?= e($subject['subject_name']) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>رمز المقرر</label>
                            <input type="text" name="subject_code" class="form-control" value="<?= e($subject['subject_code'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>القسم <span class="text-danger">*</span></label>
                            <select name="department_id" class="form-control select2" required>
                                <option value="">-- اختر --</option>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?= $d['department_id'] ?>" <?= $subject['department_id'] == $d['department_id'] ? 'selected' : '' ?>><?= e($d['department_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>المستوى <span class="text-danger">*</span></label>
                            <select name="level_id" class="form-control select2" required>
                                <option value="">-- اختر --</option>
                                <?php foreach ($levels as $l): ?>
                                    <option value="<?= $l['level_id'] ?>" <?= $subject['level_id'] == $l['level_id'] ? 'selected' : '' ?>><?= e($l['level_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>عدد الساعات</label>
                            <input type="number" name="hours" class="form-control" value="<?= (int)($subject['hours'] ?? 0) ?>" min="1">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>الساعات المعتمدة</label>
                            <input type="number" name="credit_hours" class="form-control" value="<?= (int)($subject['credit_hours'] ?? 0) ?>" min="1">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>نوع المقرر</label>
                            <select name="subject_type" class="form-control">
                                <option value="نظري" <?= ($subject['subject_type'] ?? '') === 'نظري' ? 'selected' : '' ?>>نظري</option>
                                <option value="عملي" <?= ($subject['subject_type'] ?? '') === 'عملي' ? 'selected' : '' ?>>عملي</option>
                                <option value="نظري وعملي" <?= ($subject['subject_type'] ?? '') === 'نظري وعملي' ? 'selected' : '' ?>>نظري وعملي</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>الحالة</label>
                    <select name="is_active" class="form-control">
                        <option value="1" <?= $subject['is_active'] ? 'selected' : '' ?>>فعّال</option>
                        <option value="0" <?= !$subject['is_active'] ? 'selected' : '' ?>>معطّل</option>
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning"><i class="fas fa-save ml-1"></i> تحديث</button>
                <a href="<?= url('/subjects') ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div></div>

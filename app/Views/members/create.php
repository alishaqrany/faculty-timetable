<?php
$this->layout('layouts.app');
$__page_title = 'إضافة عضو هيئة تدريس';
$__breadcrumb = [['label' => 'الأعضاء', 'url' => '/members'], ['label' => 'إضافة']];
?>

<div class="row"><div class="col-md-8">
    <div class="card card-primary">
        <div class="card-header"><h3 class="card-title">بيانات العضو الجديد</h3></div>
        <form method="POST" action="<?= url('/members') ?>">
            <?= csrf_field() ?>
            <div class="card-body">
                <div class="form-group">
                    <label>الاسم الكامل <span class="text-danger">*</span></label>
                    <input type="text" name="member_name" class="form-control" value="<?= e(old('member_name')) ?>" required>
                </div>
                <div class="form-group">
                    <label>القسم <span class="text-danger">*</span></label>
                    <select name="department_id" class="form-control select2" required>
                        <option value="">-- اختر القسم --</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['department_id'] ?>" <?= old('department_id') == $d['department_id'] ? 'selected' : '' ?>>
                                <?= e($d['department_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>الدرجة العلمية</label>
                    <select name="degree_id" class="form-control select2">
                        <option value="">-- اختر --</option>
                        <?php foreach ($degrees as $deg): ?>
                            <option value="<?= $deg['id'] ?>" <?= old('degree_id') == $deg['id'] ? 'selected' : '' ?>>
                                <?= e($deg['degree_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control" value="<?= e(old('email')) ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>الهاتف</label>
                            <input type="text" name="phone" class="form-control" value="<?= e(old('phone')) ?>">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>تاريخ الانضمام</label>
                    <input type="date" name="join_date" class="form-control" value="<?= e(old('join_date')) ?>">
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                <a href="<?= url('/members') ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div></div>

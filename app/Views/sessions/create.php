<?php
$this->layout('layouts.app');
$__page_title = 'إضافة فترة زمنية';
$__breadcrumb = [['label' => 'الفترات', 'url' => '/sessions'], ['label' => 'إضافة']];
?>

<div class="row"><div class="col-md-8">
    <div class="card card-primary">
        <div class="card-header"><h3 class="card-title">بيانات الفترة الجديدة</h3></div>
        <form method="POST" action="<?= url('/sessions') ?>">
            <?= csrf_field() ?>
            <div class="card-body">
                <div class="form-group">
                    <label>اليوم <span class="text-danger">*</span></label>
                    <select name="day" class="form-control" required>
                        <?php foreach (['الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس'] as $d): ?>
                            <option value="<?= $d ?>" <?= old('day') === $d ? 'selected' : '' ?>><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>اسم الفترة <span class="text-danger">*</span></label>
                    <input type="text" name="session_name" class="form-control" value="<?= e(old('session_name')) ?>" required>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>وقت البدء <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control" value="<?= e(old('start_time')) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>وقت الانتهاء <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" class="form-control" value="<?= e(old('end_time')) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>المدة (ساعات)</label>
                            <input type="number" name="duration" class="form-control" value="<?= e(old('duration', '2')) ?>" min="1">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                <a href="<?= url('/sessions') ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div></div>

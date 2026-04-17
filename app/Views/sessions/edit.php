<?php
$this->layout('layouts.app');
$__page_title = 'تعديل الفترة الزمنية';
$__breadcrumb = [['label' => 'الفترات', 'url' => '/sessions'], ['label' => 'تعديل']];
$s = $session_item;
?>

<div class="row"><div class="col-md-8">
    <div class="card card-warning">
        <div class="card-header"><h3 class="card-title">تعديل بيانات الفترة</h3></div>
        <form method="POST" action="<?= url("/sessions/{$s['session_id']}") ?>">
            <?= csrf_field() ?>
            <div class="card-body">
                <div class="form-group">
                    <label>اليوم <span class="text-danger">*</span></label>
                    <select name="day" class="form-control" required>
                        <?php foreach (['الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس'] as $d): ?>
                            <option value="<?= $d ?>" <?= $s['day'] === $d ? 'selected' : '' ?>><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>اسم الفترة <span class="text-danger">*</span></label>
                    <input type="text" name="session_name" class="form-control" value="<?= e($s['session_name'] ?? '') ?>" required>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>وقت البدء</label>
                            <input type="time" name="start_time" class="form-control" value="<?= e($s['start_time']) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>وقت الانتهاء</label>
                            <input type="time" name="end_time" class="form-control" value="<?= e($s['end_time']) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>المدة (ساعات)</label>
                            <input type="number" name="duration" class="form-control" value="<?= (int)($s['duration'] ?? 2) ?>" min="1">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>الحالة</label>
                    <select name="is_active" class="form-control">
                        <option value="1" <?= $s['is_active'] ? 'selected' : '' ?>>فعّال</option>
                        <option value="0" <?= !$s['is_active'] ? 'selected' : '' ?>>معطّل</option>
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning"><i class="fas fa-save ml-1"></i> تحديث</button>
                <a href="<?= url('/sessions') ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div></div>

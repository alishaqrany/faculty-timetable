<?php
$this->layout('layouts.app');
$__page_title = 'إضافة تكليف تدريس';
$__breadcrumb = [['label' => 'تكليفات التدريس', 'url' => '/member-courses'], ['label' => 'إضافة']];
?>

<div class="row"><div class="col-md-8">
    <div class="card card-primary">
        <div class="card-header"><h3 class="card-title">بيانات التكليف الجديد</h3></div>
        <form method="POST" action="<?= url('/member-courses') ?>">
            <?= csrf_field() ?>
            <div class="card-body">
                <div class="form-group">
                    <label>عضو هيئة التدريس <span class="text-danger">*</span></label>
                    <select name="member_id" class="form-control select2" required>
                        <option value="">-- اختر --</option>
                        <?php foreach ($members as $m): ?>
                            <option value="<?= $m['member_id'] ?>" <?= old('member_id') == $m['member_id'] ? 'selected' : '' ?>><?= e($m['member_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>المقرر <span class="text-danger">*</span></label>
                    <select name="subject_id" class="form-control select2" required>
                        <option value="">-- اختر --</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['subject_id'] ?>" <?= old('subject_id') == $s['subject_id'] ? 'selected' : '' ?>><?= e($s['subject_name']) ?> (<?= e($s['department_name']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>الشعبة <span class="text-danger">*</span></label>
                    <select name="section_id" class="form-control select2" required>
                        <option value="">-- اختر --</option>
                        <?php foreach ($sections as $sec): ?>
                            <option value="<?= $sec['section_id'] ?>" <?= old('section_id') == $sec['section_id'] ? 'selected' : '' ?>><?= e($sec['section_name']) ?> — <?= e($sec['department_name']) ?> / <?= e($sec['level_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                <a href="<?= url('/member-courses') ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div></div>

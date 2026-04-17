<?php
$this->layout('layouts.app');
$__page_title = 'تعديل تكليف التدريس';
$__breadcrumb = [['label' => 'تكليفات التدريس', 'url' => '/member-courses'], ['label' => 'تعديل']];
?>

<div class="row"><div class="col-md-8">
    <div class="card card-warning">
        <div class="card-header"><h3 class="card-title">تعديل بيانات التكليف</h3></div>
        <form method="POST" action="<?= url("/member-courses/{$course['member_course_id']}") ?>">
            <?= csrf_field() ?>
            <div class="card-body">
                <div class="form-group">
                    <label>عضو هيئة التدريس <span class="text-danger">*</span></label>
                    <select name="member_id" class="form-control select2" required>
                        <option value="">-- اختر --</option>
                        <?php foreach ($members as $m): ?>
                            <option value="<?= $m['member_id'] ?>" <?= $course['member_id'] == $m['member_id'] ? 'selected' : '' ?>><?= e($m['member_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>المقرر <span class="text-danger">*</span></label>
                    <select name="subject_id" class="form-control select2" required>
                        <option value="">-- اختر --</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['subject_id'] ?>" <?= $course['subject_id'] == $s['subject_id'] ? 'selected' : '' ?>><?= e($s['subject_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>الشعبة <span class="text-danger">*</span></label>
                    <select name="section_id" class="form-control select2" required>
                        <option value="">-- اختر --</option>
                        <?php foreach ($sections as $sec): ?>
                            <option value="<?= $sec['section_id'] ?>" <?= $course['section_id'] == $sec['section_id'] ? 'selected' : '' ?>><?= e($sec['section_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning"><i class="fas fa-save ml-1"></i> تحديث</button>
                <a href="<?= url('/member-courses') ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div></div>

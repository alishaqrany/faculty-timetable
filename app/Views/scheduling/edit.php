<?php
$this->layout('layouts.app');
$__page_title = 'تعديل حصة في الجدول';
$__breadcrumb = [['label' => 'الجدولة', 'url' => '/scheduling'], ['label' => 'تعديل']];
?>

<div class="row"><div class="col-md-8">
    <div class="card card-warning">
        <div class="card-header"><h3 class="card-title">تعديل الحصة</h3></div>
        <form method="POST" action="<?= url("/scheduling/{$entry['timetable_id']}") ?>">
            <?= csrf_field() ?>
            <div class="card-body">
                <div class="form-group">
                    <label>المقرر / الشعبة <span class="text-danger">*</span></label>
                    <select name="member_course_id" class="form-control select2" required>
                        <?php foreach ($myCourses as $mc): ?>
                            <option value="<?= $mc['member_course_id'] ?>" <?= $entry['member_course_id'] == $mc['member_course_id'] ? 'selected' : '' ?>>
                                <?= e($mc['subject_name']) ?> — <?= e($mc['section_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>القاعة <span class="text-danger">*</span></label>
                    <select name="classroom_id" class="form-control select2" required>
                        <?php foreach ($classrooms as $cr): ?>
                            <option value="<?= $cr['classroom_id'] ?>" <?= $entry['classroom_id'] == $cr['classroom_id'] ? 'selected' : '' ?>>
                                <?= e($cr['classroom_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>الفترة <span class="text-danger">*</span></label>
                    <select name="session_id" class="form-control select2" required>
                        <?php foreach ($sessions as $sess): ?>
                            <option value="<?= $sess['session_id'] ?>" <?= $entry['session_id'] == $sess['session_id'] ? 'selected' : '' ?>>
                                <?= e($sess['day']) ?> — <?= e($sess['start_time']) ?>-<?= e($sess['end_time']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning"><i class="fas fa-save ml-1"></i> تحديث</button>
                <a href="<?= url('/scheduling') ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div></div>

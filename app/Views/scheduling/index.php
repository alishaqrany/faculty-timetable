<?php
$this->layout('layouts.app');
$__page_title = 'الجدولة';
$__breadcrumb = [['label' => 'الجدولة']];

$priorityMode = $priorityState['mode'] ?? 'disabled';
$priorityLabel = $priorityState['mode_label'] ?? 'معطل';
?>

<!-- Priority State Info -->
<?php if ($priorityMode !== 'disabled'): ?>
<div class="alert alert-info d-flex flex-wrap align-items-center justify-content-between">
    <div class="mb-2 mb-sm-0">
        <i class="fas fa-sort-amount-up ml-1"></i>
        <strong>نظام الأولوية:</strong> <?= e($priorityLabel) ?>
        <?php if ($priorityState['current_group']): ?>
            — المجموعة الحالية: <strong><?= e($priorityState['current_group']['group_name']) ?></strong>
        <?php endif; ?>
        <?php if ($priorityState['current_department']): ?>
            — القسم الحالي: <strong><?= e($priorityState['current_department']['department_name']) ?></strong>
        <?php endif; ?>
    </div>
    <?php if ($isAdmin): ?>
    <a href="<?= url('/priority') ?>" class="btn btn-outline-info btn-sm">
        <i class="fas fa-cog ml-1"></i> إدارة الأولوية
    </a>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Status Banner -->
<?php if ($canSchedule): ?>
<div class="alert alert-success d-flex flex-wrap align-items-center justify-content-between">
    <div class="mb-2 mb-sm-0">
        <i class="fas fa-check-circle ml-1"></i> <strong>دورك الآن!</strong> يمكنك إضافة حصصك في الجدول.
    </div>
    <?php if ($priorityMode === 'disabled'): ?>
    <form method="POST" action="<?= url('/scheduling/pass-role') ?>" class="m-0">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-dark btn-sm" onclick="return confirm('هل تريد تمرير الدور للعضو التالي؟')">
            <i class="fas fa-forward ml-1"></i> تمرير الدور
        </button>
    </form>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="alert alert-warning">
    <i class="fas fa-clock ml-1"></i>
    <?php if ($priorityMode !== 'disabled'): ?>
        ليس دورك حالياً بحسب نظام الأولوية. يمكنك عرض حصصك المسجلة فقط.
    <?php else: ?>
        ليس دورك حالياً. يمكنك عرض حصصك المسجلة فقط.
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Add Entry Form -->
<?php if ($canSchedule && !empty($myCourses)): ?>
<div class="card card-primary card-outline">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-plus ml-1"></i> إضافة حصة جديدة</h3></div>
    <form method="POST" action="<?= url('/scheduling') ?>">
        <?= csrf_field() ?>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>المقرر / الشعبة <span class="text-danger">*</span></label>
                        <select name="member_course_id" class="form-control select2" required>
                            <option value="">-- اختر --</option>
                            <?php foreach ($myCourses as $mc): ?>
                                <option value="<?= $mc['member_course_id'] ?>">
                                    <?= e($mc['subject_name']) ?> — <?= e($mc['section_name'] ?: $mc['division_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>القاعة <span class="text-danger">*</span></label>
                        <select name="classroom_id" class="form-control select2" required>
                            <option value="">-- اختر --</option>
                            <?php foreach ($classrooms as $cr): ?>
                                <option value="<?= $cr['classroom_id'] ?>"><?= e($cr['classroom_name']) ?> (<?= e($cr['classroom_type'] ?? '') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>الفترة <span class="text-danger">*</span></label>
                        <select name="session_id" class="form-control select2" required>
                            <option value="">-- اختر --</option>
                            <?php foreach ($sessions as $sess): ?>
                                <option value="<?= $sess['session_id'] ?>"><?= e($sess['day']) ?> — <?= e($sess['session_name'] ?? '') ?> (<?= $sess['start_time'] ?>-<?= $sess['end_time'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> إضافة للجدول</button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- My Scheduled Entries -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list ml-1"></i> حصصي في الجدول</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <?php if (!empty($myEntries)): ?>
        <table class="table table-hover table-striped">
            <thead>
                <tr><th>#</th><th>المقرر</th><th>الشعبة</th><th>القاعة</th><th>اليوم</th><th>الفترة</th><th>إجراءات</th></tr>
            </thead>
            <tbody>
                <?php foreach ($myEntries as $i => $e): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($e['subject_name'] ?? '') ?></td>
                    <td><?= e($e['section_name'] ?? '') ?></td>
                    <td><?= e($e['classroom_name'] ?? '') ?></td>
                    <td><?= e($e['day'] ?? '') ?></td>
                    <td><?= e($e['start_time'] ?? '') ?> - <?= e($e['end_time'] ?? '') ?></td>
                    <td>
                        <?php if ($canSchedule): ?>
                        <a href="<?= url("/scheduling/{$e['timetable_id']}/edit") ?>" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="<?= url("/scheduling/{$e['timetable_id']}/delete") ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger btn-xs btn-delete"><i class="fas fa-trash"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-center text-muted p-4">لا توجد حصص مسجلة حالياً</p>
        <?php endif; ?>
    </div>
</div>

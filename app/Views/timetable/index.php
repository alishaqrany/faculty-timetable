<?php
$this->layout('layouts.app');
$__page_title = 'الجدول الدراسي';
$__breadcrumb = [['label' => 'الجدول الدراسي']];
?>

<!-- Filters -->
<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-filter ml-1"></i> تصفية</h3></div>
    <form method="GET" action="<?= url('/timetable') ?>">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>القسم</label>
                        <select name="department_id" class="form-control select2">
                            <option value="">-- الكل --</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['department_id'] ?>" <?= ($filters['department_id'] ?? '') == $d['department_id'] ? 'selected' : '' ?>><?= e($d['department_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>المستوى</label>
                        <select name="level_id" class="form-control select2">
                            <option value="">-- الكل --</option>
                            <?php foreach ($levels as $l): ?>
                                <option value="<?= $l['level_id'] ?>" <?= ($filters['level_id'] ?? '') == $l['level_id'] ? 'selected' : '' ?>><?= e($l['level_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>عضو هيئة التدريس</label>
                        <select name="member_id" class="form-control select2">
                            <option value="">-- الكل --</option>
                            <?php foreach ($members as $m): ?>
                                <option value="<?= $m['member_id'] ?>" <?= ($filters['member_id'] ?? '') == $m['member_id'] ? 'selected' : '' ?>><?= e($m['member_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>القاعة</label>
                        <select name="classroom_id" class="form-control select2">
                            <option value="">-- الكل --</option>
                            <?php foreach ($classrooms as $c): ?>
                                <option value="<?= $c['classroom_id'] ?>" <?= ($filters['classroom_id'] ?? '') == $c['classroom_id'] ? 'selected' : '' ?>><?= e($c['classroom_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search ml-1"></i> عرض</button>
            <a href="<?= url('/timetable') ?>" class="btn btn-secondary">إعادة تعيين</a>
            <?php if ($hasFilter && can('timetable.export')): ?>
            <div class="float-left">
                <div class="btn-group" role="group">
                    <a href="<?= url('/timetable/export?' . http_build_query(array_merge($filters, ['format' => 'pdf']))) ?>" class="btn btn-danger btn-sm" target="_blank">
                        <i class="fas fa-file-pdf ml-1"></i> PDF
                    </a>
                    <a href="<?= url('/timetable/export?' . http_build_query(array_merge($filters, ['format' => 'excel']))) ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel ml-1"></i> Excel
                    </a>
                    <a href="<?= url('/timetable/export?' . http_build_query(array_merge($filters, ['format' => 'csv']))) ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-file-csv ml-1"></i> CSV
                    </a>
                    <a href="<?= url('/timetable/export?' . http_build_query(array_merge($filters, ['format' => 'html']))) ?>" class="btn btn-info btn-sm" target="_blank">
                        <i class="fas fa-print ml-1"></i> طباعة
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if ($hasFilter): ?>

    <!-- Pivot Table (if dept + level selected) -->
    <?php if ($pivotData): ?>
    <style>
        .timetable-pivot { font-size: 0.95rem; }
        .timetable-pivot thead { background-color: #2c3e50; }
        .timetable-pivot th { color: #fff; font-weight: 600; padding: 12px 8px; }
        .timetable-pivot tbody td { padding: 8px 6px; vertical-align: middle; min-height: 80px; }
        .timetable-pivot .day-header { background-color: #ecf0f1; font-weight: bold; text-align: center; }
        .timetable-pivot .course-cell { border-radius: 4px; padding: 8px; margin-bottom: 4px; color: #fff; word-wrap: break-word; font-size: 0.9rem; line-height: 1.4; }
        .timetable-pivot .course-theory { background-color: #f39c12; } /* أصفر للنظري */
        .timetable-pivot .course-practical { background-color: #3498db; } /* أزرق للعملي */
        .timetable-pivot .course-mixed { background-color: #9b59b6; } /* بنفسجي للمختلط */
        .timetable-pivot .course-lab { background-color: #e67e22; } /* برتقالي للمختبر */
        .timetable-pivot .course-name { font-weight: bold; margin-bottom: 4px; }
        .timetable-pivot .course-instructor { font-size: 0.85rem; margin: 3px 0; }
        .timetable-pivot .course-room { font-size: 0.8rem; margin-top: 3px; }
        .timetable-pivot .empty-cell { background-color: #f8f9fa; color: #999; text-align: center; }
    </style>
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-table ml-1"></i> جدول التوزيع</h3></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered timetable-pivot" style="margin: 0;">
                <thead>
                    <tr>
                        <th style="width: 80px;">اليوم</th>
                        <?php foreach ($pivotData['sessions'] as $sess): ?>
                            <th><?= e($sess['session_name'] ?? '') ?><br><small><?= e($sess['start_time'] ?? '') ?> - <?= e($sess['end_time'] ?? '') ?></small></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pivotData['days'] as $day): ?>
                    <tr>
                        <td class="day-header"><?= e($day) ?></td>
                        <?php foreach ($pivotData['sessions'] as $sess): ?>
                            <td>
                                <?php
                                $cellEntries = $pivotData['data'][$day][$sess['session_id']] ?? [];
                                if (!empty($cellEntries)):
                                ?>
                                    <?php foreach ($cellEntries as $idx => $cell): ?>
                                        <?php
                                        $courseType = strtolower($cell['subject_type'] ?? 'نظري');
                                        $cssClass = 'course-cell course-theory';
                                        if (strpos($courseType, 'عملي') !== false && strpos($courseType, 'نظري') !== false) {
                                            $cssClass = 'course-cell course-mixed';
                                        } elseif (strpos($courseType, 'عملي') !== false) {
                                            $cssClass = 'course-cell course-practical';
                                        } elseif (strpos($courseType, 'مختبر') !== false || strpos($courseType, 'lab') !== false) {
                                            $cssClass = 'course-cell course-lab';
                                        }
                                        ?>
                                        <div class="<?= $cssClass ?>">
                                            <div class="course-name"><?= e($cell['subject_name']) ?></div>
                                            <div class="course-instructor">أ/ <?= e($cell['member_name']) ?></div>
                                            <div class="course-room"><?= e($cell['classroom_name']) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="empty-cell">—</div>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Flat List -->
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-list ml-1"></i> نتائج البحث (<?= count($entries) ?>)</h3></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped data-table">
                <thead>
                    <tr><th>#</th><th>المقرر</th><th>العضو</th><th>المجموعة</th><th>النوع</th><th>القاعة</th><th>اليوم</th><th>الفترة</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $i => $e): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= e($e['subject_name'] ?? '') ?></td>
                        <td><?= e($e['member_name'] ?? '') ?></td>
                        <td><?= e($e['section_name'] ?? '') ?></td>
                        <td><?= e($e['section_type'] ?? 'شعبة') ?></td>
                        <td><?= e($e['classroom_name'] ?? '') ?></td>
                        <td><?= e($e['day'] ?? '') ?></td>
                        <td><?= e($e['start_time'] ?? '') ?> - <?= e($e['end_time'] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php else: ?>
<div class="callout callout-info">
    <p><i class="fas fa-info-circle ml-1"></i> اختر معايير التصفية أعلاه ثم اضغط "عرض" لعرض الجدول.</p>
</div>
<?php endif; ?>

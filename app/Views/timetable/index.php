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
                <a href="<?= url('/timetable/export?' . http_build_query(array_merge($filters, ['format' => 'csv']))) ?>" class="btn btn-success btn-sm">
                    <i class="fas fa-file-csv ml-1"></i> تصدير CSV
                </a>
                <a href="<?= url('/timetable/export?' . http_build_query(array_merge($filters, ['format' => 'html']))) ?>" class="btn btn-info btn-sm" target="_blank">
                    <i class="fas fa-print ml-1"></i> طباعة
                </a>
            </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if ($hasFilter): ?>

    <!-- Pivot Table (if dept + level selected) -->
    <?php if ($pivotData): ?>
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-table ml-1"></i> الجدول المحوري</h3></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered text-center">
                <thead class="thead-dark">
                    <tr>
                        <th>اليوم / الفترة</th>
                        <?php foreach ($pivotData['sessions'] as $sess): ?>
                            <th><?= e($sess['session_name'] ?? $sess['start_time']) ?><br><small><?= e($sess['start_time']) ?>-<?= e($sess['end_time']) ?></small></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pivotData['days'] as $day): ?>
                    <tr>
                        <td class="font-weight-bold bg-light"><?= e($day) ?></td>
                        <?php foreach ($pivotData['sessions'] as $sess): ?>
                            <td>
                                <?php
                                $cell = $pivotData['data'][$day][$sess['session_id']] ?? null;
                                if ($cell):
                                ?>
                                    <strong><?= e($cell['subject_name']) ?></strong><br>
                                    <small class="text-muted"><?= e($cell['member_name']) ?></small><br>
                                    <span class="badge badge-info"><?= e($cell['classroom_name']) ?></span>
                                    <span class="badge badge-secondary"><?= e($cell['section_name']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
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
                    <tr><th>#</th><th>المقرر</th><th>العضو</th><th>الشعبة</th><th>القاعة</th><th>اليوم</th><th>الفترة</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $i => $e): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= e($e['subject_name'] ?? '') ?></td>
                        <td><?= e($e['member_name'] ?? '') ?></td>
                        <td><?= e($e['section_name'] ?? '') ?></td>
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

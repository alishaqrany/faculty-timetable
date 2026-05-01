<?php
$this->layout('layouts.app');
$__page_title = 'لوحة التحكم';
?>

<!-- ══════════════════════════════════════════════════════════════
     ROW 1 — Primary KPI small-boxes (always visible)
═══════════════════════════════════════════════════════════════════ -->
<div class="row">
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box bg-info">
            <div class="inner"><h3><?= $stats['departments'] ?? 0 ?></h3><p>الأقسام</p></div>
            <div class="icon"><i class="fas fa-building"></i></div>
            <a href="<?= url('/departments') ?>" class="small-box-footer">المزيد <i class="fas fa-arrow-circle-left"></i></a>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box bg-teal">
            <div class="inner"><h3><?= $stats['members'] ?? 0 ?></h3><p>أعضاء التدريس</p></div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <a href="<?= url('/members') ?>" class="small-box-footer">المزيد <i class="fas fa-arrow-circle-left"></i></a>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box bg-warning">
            <div class="inner"><h3><?= $stats['subjects'] ?? 0 ?></h3><p>المقررات</p></div>
            <div class="icon"><i class="fas fa-book"></i></div>
            <a href="<?= url('/subjects') ?>" class="small-box-footer">المزيد <i class="fas fa-arrow-circle-left"></i></a>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3><?= $stats['sections'] ?? 0 ?></h3><p>الشُعب</p></div>
            <div class="icon"><i class="fas fa-layer-group"></i></div>
            <a href="<?= url('/sections') ?>" class="small-box-footer">المزيد <i class="fas fa-arrow-circle-left"></i></a>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box bg-secondary">
            <div class="inner"><h3><?= $stats['classrooms'] ?? 0 ?></h3><p>القاعات</p></div>
            <div class="icon"><i class="fas fa-door-open"></i></div>
            <a href="<?= url('/classrooms') ?>" class="small-box-footer">المزيد <i class="fas fa-arrow-circle-left"></i></a>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3><?= $stats['timetable'] ?? 0 ?></h3><p>محاضرات في الجدول</p></div>
            <div class="icon"><i class="fas fa-calendar-alt"></i></div>
            <a href="<?= url('/timetable') ?>" class="small-box-footer">المزيد <i class="fas fa-arrow-circle-left"></i></a>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     ROW 2 — Scheduling KPI info-boxes
═══════════════════════════════════════════════════════════════════ -->
<div class="row">
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-percentage"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">نسبة تغطية التسكين</span>
                <span class="info-box-number"><?= number_format((float)($scheduleCoverage ?? 0), 1) ?>%</span>
                <div class="progress"><div class="progress-bar bg-primary" style="width:<?= min(100, (float)($scheduleCoverage ?? 0)) ?>%"></div></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">محاضرات مُسكَّنة</span>
                <span class="info-box-number"><?= (int)($scheduledAssignments ?? 0) ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-exclamation-triangle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">محاضرات غير مُسكَّنة</span>
                <span class="info-box-number"><?= (int)($unscheduledAssignments ?? 0) ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-fire"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">أكثر يوم ازدحامًا</span>
                <span class="info-box-number">
                    <?= e($busiestDay['day'] ?? '—') ?>
                    <small class="d-block text-muted" style="font-size:.75rem"><?= (int)($busiestDay['cnt'] ?? 0) ?> محاضرة</small>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     ROW 3 — Bar chart + Recent activity
═══════════════════════════════════════════════════════════════════ -->
<div class="row mb-3">
    <div class="col-12 text-left">
        <a href="<?= url('/reports') ?>" class="btn btn-outline-primary">
            <i class="fas fa-chart-bar ml-1"></i> عرض التقارير والإحصائيات الكاملة
            <i class="fas fa-arrow-left mr-1"></i>
        </a>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     ROW 4 — Day distribution + Recent activity
═══════════════════════════════════════════════════════════════════ -->
<div class="row">
    <div class="col-md-8">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar ml-1"></i> توزيع المحاضرات حسب اليوم</h3>
                <?php if (!empty($hasFilter)): ?>
                <span class="badge badge-info mr-2">مُصفَّى</span>
                <?php endif; ?>
            </div>
            <div class="card-body"><canvas id="dayChart" style="height:260px"></canvas></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-outline card-secondary">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-history ml-1"></i> آخر العمليات</h3></div>
            <div class="card-body p-0" style="max-height:310px;overflow-y:auto">
                <ul class="list-group list-group-flush">
                    <?php if (!empty($recentLogs)): ?>
                        <?php foreach ($recentLogs as $log): ?>
                        <li class="list-group-item py-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <strong class="text-sm"><?= e($log['username'] ?? 'نظام') ?></strong>
                                <small class="text-muted"><?= e(substr($log['created_at'] ?? '', 0, 10)) ?></small>
                            </div>
                            <small class="text-muted"><?= e($log['action']) ?> — <?= e($log['module']) ?></small>
                        </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item text-muted text-center">لا توجد عمليات حديثة</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     ROW 5 — Classroom occupancy + Faculty workload
═══════════════════════════════════════════════════════════════════ -->
<div class="row">
    <div class="col-md-7">
        <div class="card card-outline card-info">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-door-open ml-1"></i> نسبة إشغال القاعات</h3></div>
            <div class="card-body"><canvas id="occupancyChart" style="height:280px"></canvas></div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card card-outline card-success">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-user-clock ml-1"></i> عبء التدريس حسب العضو</h3></div>
            <div class="card-body"><canvas id="workloadChart" style="height:280px"></canvas></div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     ROW 6 — Department load + Daily utilization table
═══════════════════════════════════════════════════════════════════ -->
<div class="row">
    <div class="col-md-7">
        <div class="card card-outline card-warning">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-sitemap ml-1"></i> المحاضرات حسب القسم</h3></div>
            <div class="card-body"><canvas id="departmentChart" style="height:260px"></canvas></div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card card-outline card-danger">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-clipboard-list ml-1"></i> استغلال اليوم الدراسي</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <thead class="thead-light">
                        <tr><th>اليوم</th><th>المحاضرات</th><th>نسبة الاستغلال</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($utilizationByDay)): ?>
                            <?php foreach ($utilizationByDay as $row): ?>
                            <tr>
                                <td><?= e($row['day']) ?></td>
                                <td><?= (int)($row['lecture_count'] ?? 0) ?></td>
                                <td>
                                    <?php $pct = (float)($row['utilization_percent'] ?? 0); ?>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 mb-0 ml-2" style="height:8px">
                                            <div class="progress-bar bg-<?= $pct >= 75 ? 'danger' : ($pct >= 40 ? 'warning' : 'success') ?>"
                                                 style="width:<?= min(100, $pct) ?>%"></div>
                                        </div>
                                        <small class="mr-1"><?= number_format($pct, 0) ?>%</small>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center text-muted p-3">لا تتوفر بيانات</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     FILTER — Collapsible card (collapsed by default, opens when active)
═══════════════════════════════════════════════════════════════════ -->
<div class="card card-outline card-secondary mt-2" id="filterCard">
    <div class="card-header" data-card-widget="collapse" style="cursor:pointer">
        <h3 class="card-title">
            <i class="fas fa-filter ml-1"></i> تصفية التقارير
            <?php if (!empty($hasFilter)): ?>
            <span class="badge badge-primary mr-2">فعّال</span>
            <?php endif; ?>
        </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool"><i class="fas fa-<?= !empty($hasFilter) ? 'minus' : 'plus' ?>"></i></button>
        </div>
    </div>
    <form method="GET" action="<?= url('/dashboard') ?>">
        <div class="card-body">
            <?php if (!empty($hasFilter)): ?>
            <div class="alert alert-info py-2 mb-3">
                <i class="fas fa-info-circle ml-1"></i> الأرقام والرسوم البيانية تعكس نتائج التصفية الحالية.
            </div>
            <?php endif; ?>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>القسم</label>
                        <select name="department_id" class="form-control select2">
                            <option value="">-- الكل --</option>
                            <?php foreach (($departments ?? []) as $d): ?>
                                <option value="<?= $d['department_id'] ?>" <?= ($filters['department_id'] ?? '') == $d['department_id'] ? 'selected' : '' ?>>
                                    <?= e($d['department_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>المستوى</label>
                        <select name="level_id" class="form-control select2">
                            <option value="">-- الكل --</option>
                            <?php foreach (($levels ?? []) as $l): ?>
                                <option value="<?= $l['level_id'] ?>" <?= ($filters['level_id'] ?? '') == $l['level_id'] ? 'selected' : '' ?>>
                                    <?= e($l['level_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>السنة الدراسية</label>
                        <select name="academic_year_id" id="academicYearFilter" class="form-control select2">
                            <option value="">-- الكل --</option>
                            <?php foreach (($academicYears ?? []) as $year): ?>
                                <option value="<?= $year['id'] ?>" <?= ($filters['academic_year_id'] ?? '') == $year['id'] ? 'selected' : '' ?>>
                                    <?= e($year['year_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>الفصل الدراسي</label>
                        <select name="semester_id" id="semesterFilter" class="form-control select2">
                            <option value="">-- الكل --</option>
                            <?php foreach (($semesters ?? []) as $semester): ?>
                                <option
                                    value="<?= $semester['id'] ?>"
                                    data-year-id="<?= (int)($semester['academic_year_id'] ?? 0) ?>"
                                    <?= ($filters['semester_id'] ?? '') == $semester['id'] ? 'selected' : '' ?>
                                >
                                    <?= e($semester['semester_name']) ?> - <?= e($semester['year_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search ml-1"></i> تطبيق</button>
            <a href="<?= url('/dashboard') ?>" class="btn btn-secondary mr-1">إعادة تعيين</a>
        </div>
    </form>
</div>

<?php $this->section('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
// ── Day Distribution ─────────────────────────────────────────────
var dayData = <?= json_encode($dayData ?? [], JSON_UNESCAPED_UNICODE) ?>;
new Chart(document.getElementById('dayChart'), {
    type: 'bar',
    data: {
        labels: dayData.map(function(d) { return d.day; }),
        datasets: [{ label: 'عدد المحاضرات', data: dayData.map(function(d) { return parseInt(d.cnt); }),
            backgroundColor: ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b'], borderRadius: 6 }]
    },
    options: { responsive: true, maintainAspectRatio: false,
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
        plugins: { legend: { display: false } } }
});

// ── Classroom Occupancy ──────────────────────────────────────────
var occupancyData = <?= json_encode($classroomOccupancy ?? [], JSON_UNESCAPED_UNICODE) ?>;
var totalSlots = <?= (int)($totalSlots ?? 0) ?>;
new Chart(document.getElementById('occupancyChart'), {
    type: 'bar',
    data: {
        labels: occupancyData.map(function(d) { return d.name; }),
        datasets: [
            { label: 'محاضرات مستخدمة', data: occupancyData.map(function(d) { return parseInt(d.used_slots); }),
              backgroundColor: 'rgba(54,162,235,0.75)', borderRadius: 4 },
            { label: 'إجمالي الفترات المتاحة', data: occupancyData.map(function() { return totalSlots; }),
              backgroundColor: 'rgba(201,203,207,0.3)' }
        ]
    },
    options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y',
        scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } },
        plugins: { legend: { position: 'bottom' },
            tooltip: { callbacks: { afterBody: function(ctx) {
                if (ctx[0].datasetIndex === 0 && totalSlots > 0)
                    return 'نسبة الإشغال: ' + ((ctx[0].parsed.x / totalSlots) * 100).toFixed(1) + '%';
            }}}}
    }
});

// ── Faculty Workload ─────────────────────────────────────────────
var workloadData = <?= json_encode($facultyWorkload ?? [], JSON_UNESCAPED_UNICODE) ?>;
var wColors = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#fd7e14','#20c997','#e83e8c','#17a2b8','#6c757d'];
new Chart(document.getElementById('workloadChart'), {
    type: 'doughnut',
    data: {
        labels: workloadData.map(function(d) { return d.name; }),
        datasets: [{ data: workloadData.map(function(d) { return parseInt(d.session_count); }),
            backgroundColor: wColors.slice(0, workloadData.length), borderWidth: 2 }]
    },
    options: { responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { font: { family: 'Tajawal' } } },
            tooltip: { callbacks: { label: function(ctx) { return ctx.label + ': ' + ctx.parsed + ' محاضرة'; } } } }
    }
});

// ── Department Load ──────────────────────────────────────────────
var departmentData = <?= json_encode($departmentLoad ?? [], JSON_UNESCAPED_UNICODE) ?>;
new Chart(document.getElementById('departmentChart'), {
    type: 'bar',
    data: {
        labels: departmentData.map(function(d) { return d.department; }),
        datasets: [{ label: 'عدد المحاضرات', data: departmentData.map(function(d) { return parseInt(d.lecture_count); }),
            backgroundColor: '#36b9cc', borderRadius: 6 }]
    },
    options: { responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

// ── Collapse filter if no active filter ──────────────────────────
<?php if (empty($hasFilter)): ?>
(function() {
    var card = document.getElementById('filterCard');
    if (!card) return;
    var body = card.querySelector('.card-body');
    var footer = card.querySelector('.card-footer');
    if (body) body.style.display = 'none';
    if (footer) footer.style.display = 'none';
})();
<?php endif; ?>

// ── Semester linked to Academic Year ─────────────────────────────
(function() {
    var yearSelect = document.getElementById('academicYearFilter');
    var semesterSelect = document.getElementById('semesterFilter');
    if (!yearSelect || !semesterSelect) return;
    function filterSemestersByYear() {
        var selectedYear = yearSelect.value;
        var currentSemester = semesterSelect.value;
        var visible = false;
        Array.prototype.forEach.call(semesterSelect.options, function(option, i) {
            if (i === 0) { option.hidden = false; return; }
            var show = selectedYear === '' || option.getAttribute('data-year-id') === selectedYear;
            option.hidden = !show;
            if (show && option.value === currentSemester) visible = true;
        });
        if (currentSemester && !visible) semesterSelect.value = '';
    }
    yearSelect.addEventListener('change', filterSemestersByYear);
    filterSemestersByYear();
})();
</script>
<?php $this->endSection(); ?>

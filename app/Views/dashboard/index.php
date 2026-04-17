<?php
$this->layout('layouts.app');
$__page_title = 'لوحة التحكم';
?>

<!-- Stats Boxes -->
<div class="row">
    <div class="col-lg-4 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?= $stats['departments'] ?? 0 ?></h3>
                <p>الأقسام</p>
            </div>
            <div class="icon"><i class="fas fa-building"></i></div>
            <a href="<?= url('/departments') ?>" class="small-box-footer">
                المزيد <i class="fas fa-arrow-circle-left"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-4 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?= $stats['members'] ?? 0 ?></h3>
                <p>أعضاء هيئة التدريس</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <a href="<?= url('/members') ?>" class="small-box-footer">
                المزيد <i class="fas fa-arrow-circle-left"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-4 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?= $stats['subjects'] ?? 0 ?></h3>
                <p>المقررات</p>
            </div>
            <div class="icon"><i class="fas fa-book"></i></div>
            <a href="<?= url('/subjects') ?>" class="small-box-footer">
                المزيد <i class="fas fa-arrow-circle-left"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-4 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3><?= $stats['sections'] ?? 0 ?></h3>
                <p>الشُعب</p>
            </div>
            <div class="icon"><i class="fas fa-layer-group"></i></div>
            <a href="<?= url('/sections') ?>" class="small-box-footer">
                المزيد <i class="fas fa-arrow-circle-left"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-4 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3><?= $stats['classrooms'] ?? 0 ?></h3>
                <p>القاعات</p>
            </div>
            <div class="icon"><i class="fas fa-door-open"></i></div>
            <a href="<?= url('/classrooms') ?>" class="small-box-footer">
                المزيد <i class="fas fa-arrow-circle-left"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-4 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3><?= $stats['timetable'] ?? 0 ?></h3>
                <p>حصص في الجدول</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-alt"></i></div>
            <a href="<?= url('/timetable') ?>" class="small-box-footer">
                المزيد <i class="fas fa-arrow-circle-left"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Chart: Entries Per Day -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar ml-1"></i> توزيع الحصص حسب اليوم</h3>
            </div>
            <div class="card-body">
                <canvas id="dayChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history ml-1"></i> آخر العمليات</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php if (!empty($recentLogs)): ?>
                        <?php foreach ($recentLogs as $log): ?>
                        <li class="list-group-item">
                            <small class="text-muted"><?= e($log['created_at'] ?? '') ?></small><br>
                            <strong><?= e($log['username'] ?? 'نظام') ?></strong>:
                            <?= e($log['action']) ?> — <?= e($log['module']) ?>
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

<!-- Row 2: Occupancy & Workload Charts -->
<div class="row">
    <!-- Classroom Occupancy -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-door-open ml-1"></i> نسبة إشغال القاعات</h3>
            </div>
            <div class="card-body">
                <canvas id="occupancyChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Faculty Workload -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-clock ml-1"></i> عبء التدريس (حصص لكل عضو)</h3>
            </div>
            <div class="card-body">
                <canvas id="workloadChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<?php $this->section('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
// ── Day Distribution Chart ───────────────────────────────────────
var dayData = <?= json_encode($dayData ?? [], JSON_UNESCAPED_UNICODE) ?>;
var labels = dayData.map(function(d) { return d.day; });
var counts = dayData.map(function(d) { return parseInt(d.cnt); });

new Chart(document.getElementById('dayChart'), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'عدد الحصص',
            data: counts,
            backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1'],
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
        plugins: { legend: { display: false } }
    }
});

// ── Classroom Occupancy Chart ────────────────────────────────────
var occupancyData = <?= json_encode($classroomOccupancy ?? [], JSON_UNESCAPED_UNICODE) ?>;
var totalSlots = <?= (int)($totalSlots ?? 0) ?>;

new Chart(document.getElementById('occupancyChart'), {
    type: 'bar',
    data: {
        labels: occupancyData.map(function(d) { return d.name; }),
        datasets: [
            {
                label: 'حصص مستخدمة',
                data: occupancyData.map(function(d) { return parseInt(d.used_slots); }),
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                borderRadius: 4,
            },
            {
                label: 'إجمالي الفترات المتاحة',
                data: occupancyData.map(function() { return totalSlots; }),
                backgroundColor: 'rgba(201, 203, 207, 0.3)',
                borderColor: 'rgba(201, 203, 207, 0.6)',
                borderWidth: 1,
                borderRadius: 4,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } },
        plugins: {
            legend: { position: 'bottom' },
            tooltip: {
                callbacks: {
                    afterBody: function(ctx) {
                        if (ctx[0].datasetIndex === 0 && totalSlots > 0) {
                            var pct = ((ctx[0].parsed.x / totalSlots) * 100).toFixed(1);
                            return 'نسبة الإشغال: ' + pct + '%';
                        }
                    }
                }
            }
        }
    }
});

// ── Faculty Workload Chart ───────────────────────────────────────
var workloadData = <?= json_encode($facultyWorkload ?? [], JSON_UNESCAPED_UNICODE) ?>;
var wColors = ['#007bff','#28a745','#ffc107','#dc3545','#6f42c1','#fd7e14','#20c997','#e83e8c','#17a2b8','#6c757d'];

new Chart(document.getElementById('workloadChart'), {
    type: 'doughnut',
    data: {
        labels: workloadData.map(function(d) { return d.name; }),
        datasets: [{
            data: workloadData.map(function(d) { return parseInt(d.session_count); }),
            backgroundColor: wColors.slice(0, workloadData.length),
            borderWidth: 2,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { font: { family: 'Tajawal' } } },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        return ctx.label + ': ' + ctx.parsed + ' حصة';
                    }
                }
            }
        }
    }
});
</script>
<?php $this->endSection(); ?>

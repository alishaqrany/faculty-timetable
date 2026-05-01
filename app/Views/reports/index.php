<?php
$this->layout('layouts.app');
$__page_title = 'التقارير والإحصائيات';
?>

<style>
.section-header {
    border-right: 4px solid #007bff;
    padding-right: .75rem;
    margin-bottom: 1rem;
    font-size: 1.1rem;
    font-weight: 700;
    color: #343a40;
}
.kpi-value { font-size: 2rem; font-weight: 800; line-height: 1.1; }
.kpi-label { font-size: .8rem; color: #6c757d; }
.table td, .table th { vertical-align: middle; }
.coverage-bar { height: 8px; border-radius: 4px; }
</style>

<!-- ══════════════════════════════════════════════════════════════
     SECTION 1 — ملخص عام
═══════════════════════════════════════════════════════════════════ -->
<div class="section-header"><i class="fas fa-chart-pie ml-1 text-primary"></i> الملخص العام</div>
<div class="row mb-4">
    <?php
    $kpis = [
        ['label'=>'الأقسام الفعّالة',       'val'=>$summary['departments'],     'icon'=>'building',      'color'=>'info'],
        ['label'=>'أعضاء التدريس',           'val'=>$summary['members'],         'icon'=>'users',         'color'=>'teal'],
        ['label'=>'المقررات',                'val'=>$summary['subjects'],        'icon'=>'book',          'color'=>'warning'],
        ['label'=>'الشُعب الدراسية',          'val'=>$summary['sections'],        'icon'=>'layer-group',   'color'=>'danger'],
        ['label'=>'القاعات الفعّالة',         'val'=>$summary['classrooms'],      'icon'=>'door-open',     'color'=>'secondary'],
        ['label'=>'الفترات الدراسية',         'val'=>$summary['active_sessions'], 'icon'=>'clock',         'color'=>'dark'],
        ['label'=>'إجمالي التكليفات',         'val'=>$summary['assignments'],     'icon'=>'tasks',         'color'=>'primary'],
        ['label'=>'محاضرات مُسكَّنة',         'val'=>$summary['placed'],          'icon'=>'check-circle',  'color'=>'success'],
        ['label'=>'محاضرات غير مُسكَّنة',    'val'=>$summary['unplaced'],        'icon'=>'exclamation-triangle', 'color'=>'warning'],
        ['label'=>'نسبة التغطية',            'val'=>$summary['coverage'].'%',    'icon'=>'percentage',    'color'=>'primary'],
    ];
    foreach ($kpis as $k): ?>
    <div class="col-xl-2 col-md-3 col-6 mb-3">
        <div class="card border-left-<?= $k['color'] ?> shadow-sm h-100">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-value text-<?= $k['color'] ?>"><?= $k['val'] ?></div>
                        <div class="kpi-label"><?= $k['label'] ?></div>
                    </div>
                    <i class="fas fa-<?= $k['icon'] ?> fa-2x text-<?= $k['color'] ?> opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Progress bar for overall coverage -->
<div class="card shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between mb-1">
            <strong>نسبة التسكين الإجمالية</strong>
            <span class="text-primary font-weight-bold"><?= $summary['coverage'] ?>%</span>
        </div>
        <div class="progress" style="height:14px;border-radius:7px">
            <?php $cov = (float)$summary['coverage']; ?>
            <div class="progress-bar bg-<?= $cov >= 80 ? 'success' : ($cov >= 50 ? 'warning' : 'danger') ?>"
                 style="width:<?= min(100,$cov) ?>%;border-radius:7px">
                <?= $cov ?>%
            </div>
        </div>
        <small class="text-muted mt-1 d-block"><?= $summary['placed'] ?> من <?= $summary['assignments'] ?> تكليف مُسكَّن</small>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     SECTION 2 — تحليل التسكين حسب القسم
═══════════════════════════════════════════════════════════════════ -->
<div class="section-header mt-4"><i class="fas fa-building ml-1 text-info"></i> تحليل التسكين حسب القسم</div>
<div class="row mb-4">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header py-2"><h3 class="card-title mb-0">تفاصيل تسكين كل قسم</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <thead class="thead-light">
                        <tr><th>القسم</th><th>إجمالي</th><th>مُسكَّن</th><th>غير مُسكَّن</th><th>نسبة التغطية</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($placementByDept as $row):
                            $total = (int)$row['total_assignments'];
                            $placed = (int)$row['placed_assignments'];
                            $pct = $total > 0 ? round($placed/$total*100) : 0;
                        ?>
                        <tr>
                            <td><?= e($row['department_name']) ?></td>
                            <td><?= $total ?></td>
                            <td><span class="badge badge-success"><?= $placed ?></span></td>
                            <td><span class="badge badge-<?= $row['unplaced']>0?'danger':'secondary' ?>"><?= (int)$row['unplaced'] ?></span></td>
                            <td style="min-width:120px">
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 mb-0 ml-1" style="height:8px">
                                        <div class="progress-bar bg-<?= $pct>=80?'success':($pct>=50?'warning':'danger') ?>" style="width:<?= $pct ?>%"></div>
                                    </div>
                                    <small><?= $pct ?>%</small>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card shadow-sm h-100">
            <div class="card-header py-2"><h3 class="card-title mb-0">توزيع التسكين حسب القسم</h3></div>
            <div class="card-body"><canvas id="deptCoverageChart" style="height:280px"></canvas></div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     SECTION 3 — تحليل التسكين حسب المستوى
═══════════════════════════════════════════════════════════════════ -->
<div class="section-header mt-2"><i class="fas fa-layer-group ml-1 text-warning"></i> تحليل التسكين حسب المستوى</div>
<div class="row mb-4">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <thead class="thead-light">
                        <tr><th>المستوى</th><th>إجمالي</th><th>مُسكَّن</th><th>التغطية</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($placementByLevel as $row):
                            $total = (int)$row['total_assignments'];
                            $placed = (int)$row['placed_assignments'];
                            $pct = $total > 0 ? round($placed/$total*100) : 0;
                        ?>
                        <tr>
                            <td><?= e($row['level_name']) ?></td>
                            <td><?= $total ?></td>
                            <td><?= $placed ?></td>
                            <td>
                                <div class="progress mb-0" style="height:8px">
                                    <div class="progress-bar bg-<?= $pct>=80?'success':($pct>=50?'warning':'danger') ?>" style="width:<?= $pct ?>%"></div>
                                </div>
                                <small><?= $pct ?>%</small>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card shadow-sm h-100">
            <div class="card-header py-2"><h3 class="card-title mb-0">الشُعب حسب القسم والمستوى</h3></div>
            <div class="card-body p-0" style="max-height:280px;overflow-y:auto">
                <table class="table table-sm mb-0">
                    <thead class="thead-light sticky-top">
                        <tr><th>القسم</th><th>المستوى</th><th>عدد الشُعب</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sectionsByDept as $row): ?>
                        <tr>
                            <td><?= e($row['department_name']) ?></td>
                            <td><?= e($row['level_name'] ?? '—') ?></td>
                            <td><span class="badge badge-primary"><?= (int)$row['section_count'] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     SECTION 4 — أعضاء التدريس
═══════════════════════════════════════════════════════════════════ -->
<div class="section-header mt-2"><i class="fas fa-users ml-1 text-teal"></i> تقرير أعضاء التدريس</div>
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">عبء التدريس لكل عضو</h3>
                <small class="text-muted">(ترتيب تنازلي حسب المحاضرات المُسكَّنة)</small>
            </div>
            <div class="card-body p-0" style="max-height:380px;overflow-y:auto">
                <table class="table table-sm table-striped mb-0">
                    <thead class="thead-light sticky-top">
                        <tr><th>#</th><th>الاسم</th><th>القسم</th><th>الدرجة</th><th>التكليفات</th><th>مُسكَّن</th><th>نسبة</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($memberWorkload as $i => $row):
                            $total = (int)$row['assigned_courses'];
                            $placed = (int)$row['placed_lectures'];
                            $pct = $total > 0 ? round($placed/$total*100) : 0;
                        ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= e($row['member_name']) ?></td>
                            <td><small><?= e($row['department_name'] ?? '—') ?></small></td>
                            <td><small class="text-muted"><?= e($row['degree'] ?? '') ?></small></td>
                            <td><?= $total ?></td>
                            <td><span class="badge badge-<?= $placed>0?'success':'secondary' ?>"><?= $placed ?></span></td>
                            <td>
                                <?php if ($total > 0): ?>
                                <div class="progress mb-0" style="height:6px;min-width:50px">
                                    <div class="progress-bar bg-<?= $pct>=80?'success':($pct>=50?'warning':'danger') ?>" style="width:<?= $pct ?>%"></div>
                                </div>
                                <small><?= $pct ?>%</small>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header py-2 bg-warning">
                <h3 class="card-title mb-0 text-white"><i class="fas fa-exclamation-circle ml-1"></i> أعضاء بلا تكليفات (<?= count($membersNoAssignment) ?>)</h3>
            </div>
            <div class="card-body p-0" style="max-height:180px;overflow-y:auto">
                <?php if (empty($membersNoAssignment)): ?>
                <p class="text-center text-muted p-3 mb-0"><i class="fas fa-check-circle text-success"></i> جميع الأعضاء لديهم تكليفات</p>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($membersNoAssignment as $m): ?>
                    <li class="list-group-item py-1">
                        <i class="fas fa-user text-warning ml-1"></i>
                        <?= e($m['member_name']) ?>
                        <small class="text-muted d-block"><?= e($m['department_name'] ?? '—') ?></small>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-header py-2"><h3 class="card-title mb-0">توزيع عبء التدريس</h3></div>
            <div class="card-body p-2"><canvas id="workloadChart" style="height:160px"></canvas></div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     SECTION 5 — القاعات
═══════════════════════════════════════════════════════════════════ -->
<div class="section-header mt-2"><i class="fas fa-door-open ml-1 text-danger"></i> تقرير القاعات</div>
<div class="row mb-4">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header py-2"><h3 class="card-title mb-0">نسبة إشغال كل قاعة</h3>
                <small class="text-muted float-left">(من <?= $totalSlots ?> فترة متاحة)</small>
            </div>
            <div class="card-body p-0" style="max-height:340px;overflow-y:auto">
                <table class="table table-sm table-striped mb-0">
                    <thead class="thead-light sticky-top">
                        <tr><th>القاعة</th><th>النوع</th><th>السعة</th><th>المحاضرات</th><th>الإشغال</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classroomUtil as $row):
                            $pct = (float)$row['utilization_pct'];
                        ?>
                        <tr>
                            <td><?= e($row['classroom_name']) ?></td>
                            <td><small><?= e($row['classroom_type'] ?? '—') ?></small></td>
                            <td><?= (int)$row['capacity'] ?></td>
                            <td><?= (int)$row['used_slots'] ?></td>
                            <td style="min-width:100px">
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 mb-0 ml-1" style="height:8px">
                                        <div class="progress-bar bg-<?= $pct>=70?'danger':($pct>=40?'warning':'success') ?>" style="width:<?= min(100,$pct) ?>%"></div>
                                    </div>
                                    <small><?= number_format($pct,0) ?>%</small>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card shadow-sm mb-3">
            <div class="card-header py-2"><h3 class="card-title mb-0">إشغال القاعات</h3></div>
            <div class="card-body p-2"><canvas id="classroomChart" style="height:200px"></canvas></div>
        </div>
        <?php if (!empty($unusedClassrooms)): ?>
        <div class="card shadow-sm border-warning">
            <div class="card-header py-2 bg-warning">
                <h3 class="card-title mb-0 text-white"><i class="fas fa-bed ml-1"></i> قاعات غير مستخدمة (<?= count($unusedClassrooms) ?>)</h3>
            </div>
            <div class="card-body p-2">
                <?php foreach ($unusedClassrooms as $r): ?>
                <span class="badge badge-light border mr-1 mb-1"><?= e($r['classroom_name']) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     SECTION 6 — المقررات
═══════════════════════════════════════════════════════════════════ -->
<div class="section-header mt-2"><i class="fas fa-book ml-1 text-warning"></i> تقرير المقررات</div>
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header py-2"><h3 class="card-title mb-0">المقررات والتسكين</h3></div>
            <div class="card-body p-0" style="max-height:320px;overflow-y:auto">
                <table class="table table-sm table-striped mb-0">
                    <thead class="thead-light sticky-top">
                        <tr><th>المقرر</th><th>الكود</th><th>القسم</th><th>التكليفات</th><th>مُسكَّن</th><th>الحالة</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjectStats as $row):
                            $ta = (int)$row['assignment_count'];
                            $pl = (int)$row['placed_count'];
                        ?>
                        <tr>
                            <td><?= e($row['subject_name']) ?></td>
                            <td><code><?= e($row['subject_code'] ?? '') ?></code></td>
                            <td><small><?= e($row['department_name'] ?? '—') ?></small></td>
                            <td><?= $ta ?></td>
                            <td><?= $pl ?></td>
                            <td>
                                <?php if ($ta === 0): ?>
                                <span class="badge badge-secondary">لا تكليفات</span>
                                <?php elseif ($pl === $ta): ?>
                                <span class="badge badge-success">مكتمل</span>
                                <?php elseif ($pl === 0): ?>
                                <span class="badge badge-danger">غير مُسكَّن</span>
                                <?php else: ?>
                                <span class="badge badge-warning">جزئي</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <?php if (!empty($subjectsNotPlaced)): ?>
        <div class="card shadow-sm border-danger">
            <div class="card-header py-2 bg-danger">
                <h3 class="card-title mb-0 text-white"><i class="fas fa-times-circle ml-1"></i> مقررات غير مُسكَّنة (<?= count($subjectsNotPlaced) ?>)</h3>
            </div>
            <div class="card-body p-0" style="max-height:220px;overflow-y:auto">
                <ul class="list-group list-group-flush">
                    <?php foreach ($subjectsNotPlaced as $r): ?>
                    <li class="list-group-item py-1">
                        <strong><?= e($r['subject_name']) ?></strong>
                        <small class="d-block text-muted"><?= e($r['department_name'] ?? '') ?></small>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php else: ?>
        <div class="card shadow-sm border-success">
            <div class="card-body text-center text-success p-4">
                <i class="fas fa-check-circle fa-3x mb-2"></i>
                <p class="mb-0">جميع المقررات المُكلَّفة مُسكَّنة</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     SECTION 7 — توزيع الأيام والفترات
═══════════════════════════════════════════════════════════════════ -->
<div class="section-header mt-2"><i class="fas fa-calendar-week ml-1 text-success"></i> توزيع الأيام والفترات</div>
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header py-2"><h3 class="card-title mb-0">المحاضرات لكل يوم</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light"><tr><th>اليوم</th><th>عدد المحاضرات</th><th>القاعات المستخدمة</th><th>التوزيع</th></tr></thead>
                    <tbody>
                        <?php
                        $maxDay = max(array_column($dayDistribution, 'lecture_count') ?: [1]);
                        foreach ($dayDistribution as $row):
                            $cnt = (int)$row['lecture_count'];
                            $barPct = $maxDay > 0 ? round($cnt/$maxDay*100) : 0;
                        ?>
                        <tr>
                            <td><strong><?= e($row['day']) ?></strong></td>
                            <td><?= $cnt ?></td>
                            <td><?= (int)$row['classrooms_used'] ?></td>
                            <td style="min-width:100px">
                                <div class="progress mb-0" style="height:10px">
                                    <div class="progress-bar bg-primary" style="width:<?= $barPct ?>%"></div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header py-2"><h3 class="card-title mb-0">إشغال الفترات</h3></div>
            <div class="card-body p-0" style="max-height:280px;overflow-y:auto">
                <table class="table table-sm mb-0">
                    <thead class="thead-light sticky-top"><tr><th>الفترة</th><th>اليوم</th><th>الوقت</th><th>محاضرات</th></tr></thead>
                    <tbody>
                        <?php foreach ($sessionDistribution as $row): ?>
                        <tr>
                            <td><?= e($row['session_name']) ?></td>
                            <td><?= e($row['day']) ?></td>
                            <td><small class="text-muted"><?= e($row['start_time']) ?> - <?= e($row['end_time']) ?></small></td>
                            <td>
                                <span class="badge badge-<?= (int)$row['lecture_count']>0?'info':'secondary' ?>">
                                    <?= (int)$row['lecture_count'] ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     SECTION 8 — المحاضرات غير المُسكَّنة
═══════════════════════════════════════════════════════════════════ -->
<?php if (!empty($unplacedList)): ?>
<div class="section-header mt-2"><i class="fas fa-exclamation-triangle ml-1 text-danger"></i> قائمة المحاضرات غير المُسكَّنة (<?= count($unplacedList) ?>)</div>
<div class="card shadow-sm mb-4">
    <div class="card-body p-0">
        <table class="table table-sm table-striped table-hover mb-0" id="unplacedTable">
            <thead class="thead-light">
                <tr><th>#</th><th>عضو التدريس</th><th>المقرر</th><th>الكود</th><th>القسم</th><th>المستوى</th>
                    <th><a href="<?= url('/scheduling') ?>" class="btn btn-xs btn-primary float-left"><i class="fas fa-plus ml-1"></i> تسكين</a></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unplacedList as $i => $row): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= e($row['member_name']) ?></td>
                    <td><?= e($row['subject_name']) ?></td>
                    <td><code><?= e($row['subject_code'] ?? '') ?></code></td>
                    <td><?= e($row['department_name'] ?? '—') ?></td>
                    <td><?= e($row['level_name'] ?? '—') ?></td>
                    <td>
                        <a href="<?= url('/scheduling') ?>" class="btn btn-xs btn-outline-primary">
                            <i class="fas fa-calendar-plus"></i> تسكين
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════════
     SECTION 9 — الفترات المكتظة
═══════════════════════════════════════════════════════════════════ -->
<?php if (!empty($conflictSlots)): ?>
<div class="section-header mt-2"><i class="fas fa-fire ml-1 text-danger"></i> أكثر الفترات ازدحامًا</div>
<div class="card shadow-sm mb-4">
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead class="thead-light"><tr><th>اليوم</th><th>الفترة</th><th>المحاضرات</th><th>القاعات المستخدمة</th></tr></thead>
            <tbody>
                <?php foreach ($conflictSlots as $row): ?>
                <tr>
                    <td><?= e($row['day']) ?></td>
                    <td><?= e($row['session_name']) ?></td>
                    <td><span class="badge badge-danger"><?= (int)$row['lecture_count'] ?></span></td>
                    <td><?= (int)$row['classrooms_used'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php $this->section('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
// ── Department coverage grouped bar ─────────────────────────────
var deptData = <?= json_encode(array_map(fn($r) => [
    'dept'    => $r['department_name'],
    'placed'  => (int)$r['placed_assignments'],
    'unplaced'=> (int)$r['unplaced'],
], $placementByDept), JSON_UNESCAPED_UNICODE) ?>;

new Chart(document.getElementById('deptCoverageChart'), {
    type: 'bar',
    data: {
        labels: deptData.map(function(d){ return d.dept; }),
        datasets: [
            { label: 'مُسكَّن',    data: deptData.map(function(d){ return d.placed;   }), backgroundColor:'rgba(40,167,69,.8)',  borderRadius:4 },
            { label: 'غير مُسكَّن', data: deptData.map(function(d){ return d.unplaced; }), backgroundColor:'rgba(220,53,69,.7)', borderRadius:4 }
        ]
    },
    options: { responsive:true, maintainAspectRatio:false,
        scales:{ x:{ stacked:true }, y:{ stacked:true, beginAtZero:true, ticks:{stepSize:1} } },
        plugins:{ legend:{ position:'bottom' } }
    }
});

// ── Workload doughnut ─────────────────────────────────────────────
var wlData = <?= json_encode(
    array_filter(
        array_map(fn($r) => ['name'=>$r['member_name'], 'cnt'=>(int)$r['placed_lectures']], $memberWorkload),
        fn($r) => $r['cnt'] > 0
    ),
    JSON_UNESCAPED_UNICODE
) ?>;
wlData = Object.values(wlData).slice(0,10);
var wColors = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#fd7e14','#20c997','#e83e8c','#17a2b8','#6c757d'];
if (wlData.length > 0) {
    new Chart(document.getElementById('workloadChart'), {
        type: 'doughnut',
        data: {
            labels: wlData.map(function(d){ return d.name; }),
            datasets:[{ data: wlData.map(function(d){ return d.cnt; }), backgroundColor: wColors.slice(0,wlData.length), borderWidth:2 }]
        },
        options:{ responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ position:'bottom', labels:{ font:{size:10} } } } }
    });
}

// ── Classroom bar ─────────────────────────────────────────────────
var crData = <?= json_encode(array_map(fn($r) => [
    'name'=>$r['classroom_name'],
    'used'=>(int)$r['used_slots'],
    'pct' =>(float)$r['utilization_pct'],
], $classroomUtil), JSON_UNESCAPED_UNICODE) ?>;

new Chart(document.getElementById('classroomChart'), {
    type: 'bar',
    data: {
        labels: crData.map(function(d){ return d.name; }),
        datasets:[{ label:'محاضرات مستخدمة', data: crData.map(function(d){ return d.used; }),
            backgroundColor:'rgba(54,162,235,.75)', borderRadius:4 }]
    },
    options:{ responsive:true, maintainAspectRatio:false, indexAxis:'y',
        scales:{ x:{ beginAtZero:true, ticks:{stepSize:1} } },
        plugins:{ legend:{display:false} }
    }
});
</script>
<?php $this->endSection(); ?>

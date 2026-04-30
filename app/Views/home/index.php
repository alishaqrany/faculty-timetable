<?php
$this->layout('layouts.public');
$__page_title = 'الرئيسية';

$ctaUrl = $auth ? url('/dashboard') : url('/login');
$ctaLabel = $auth ? 'لوحة التحكم' : 'تسجيل الدخول';
$resultsCount = $hasFilter ? count($entries) : count($recentEntries);

$resolveLabel = static function (array $rows, string $idKey, string $labelKey, ?int $selectedId): ?string {
    if ($selectedId === null) return null;
    foreach ($rows as $row) {
        if ((int) ($row[$idKey] ?? 0) === $selectedId) return (string) ($row[$labelKey] ?? '');
    }
    return null;
};

$selectedFilters = array_values(array_filter([
    ['label' => 'القسم', 'value' => $resolveLabel($departments, 'department_id', 'department_name', $filters['department_id'] ?? null)],
    ['label' => 'الفرقة', 'value' => $resolveLabel($levels, 'level_id', 'level_name', $filters['level_id'] ?? null)],
    ['label' => 'عضو هيئة التدريس', 'value' => $resolveLabel($members, 'member_id', 'member_name', $filters['member_id'] ?? null)],
    ['label' => 'القاعة', 'value' => $resolveLabel($classrooms, 'classroom_id', 'classroom_name', $filters['classroom_id'] ?? null)],
], static fn(array $item): bool => !empty($item['value'])));

$statItems = [
    ['label' => 'الأقسام', 'value' => (int) ($stats['departments'] ?? 0), 'icon' => 'fa-sitemap'],
    ['label' => 'الفرق الدراسية', 'value' => (int) ($stats['levels'] ?? 0), 'icon' => 'fa-layer-group'],
    ['label' => 'المقررات', 'value' => (int) ($stats['subjects'] ?? 0), 'icon' => 'fa-book'],
    ['label' => 'أعضاء هيئة التدريس', 'value' => (int) ($stats['members'] ?? 0), 'icon' => 'fa-users'],
    ['label' => 'القاعات', 'value' => (int) ($stats['classrooms'] ?? 0), 'icon' => 'fa-door-open'],
    ['label' => 'عناصر الجدول', 'value' => (int) ($stats['timetable'] ?? 0), 'icon' => 'fa-calendar-check'],
];

$spotlights = [
    ['title' => 'القسم الأكثر نشاطاً', 'icon' => 'fa-building-columns',
     'name' => $insights['topDepartment']['name'] ?? '—', 'total' => (int) ($insights['topDepartment']['total'] ?? 0)],
    ['title' => 'الأكثر محاضراتٍ', 'icon' => 'fa-user-tie',
     'name' => $insights['topMember']['name'] ?? '—', 'total' => (int) ($insights['topMember']['total'] ?? 0)],
    ['title' => 'القاعة الأكثر استخداماً', 'icon' => 'fa-door-open',
     'name' => $insights['topClassroom']['name'] ?? '—', 'total' => (int) ($insights['topClassroom']['total'] ?? 0)],
];
?>

<?php $this->section('styles'); ?>
<link rel="stylesheet" href="<?= asset('assets/css/home.css') ?>">
<?php $this->endSection(); ?>

<header class="home-topbar">
    <div class="container">
        <a href="<?= url('/') ?>" class="home-brand">
            <i class="fas fa-graduation-cap"></i><?= e(config('app.name', 'نظام الجداول')) ?>
        </a>
        <div class="home-nav">
            <a href="#timetable-browser" class="home-nav-link">استعراض الجداول</a>
            <a href="<?= $ctaUrl ?>" class="home-cta-btn">
                <i class="fas <?= $auth ? 'fa-columns' : 'fa-sign-in-alt' ?>"></i><?= e($ctaLabel) ?>
            </a>
        </div>
    </div>
</header>

<main>
    <!-- Hero -->
    <section class="home-section home-hero">
        <div class="container">
            <div class="home-animate">
                <span class="home-hero-badge"><i class="fas fa-calendar-alt"></i> نظام إدارة الجداول الدراسية</span>
            </div>
            <h1 class="home-animate home-animate-d1"><?= e($institutionName) ?></h1>
            <p class="home-hero-sub home-animate home-animate-d2">
                استعرض الجداول الدراسية وتعرّف على توزيع المقررات والمحاضرات بسهولة وسرعة
            </p>
            <div class="home-hero-actions home-animate home-animate-d3">
                <a href="#timetable-browser" class="home-btn-primary"><i class="fas fa-search"></i> استعراض الجداول</a>
                <a href="<?= $ctaUrl ?>" class="home-btn-outline"><i class="fas <?= $auth ? 'fa-columns' : 'fa-user-shield' ?>"></i> <?= e($ctaLabel) ?></a>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="home-section">
        <div class="container">
            <div class="home-stats">
                <?php foreach ($statItems as $si): ?>
                <div class="home-stat home-animate">
                    <div class="home-stat-val"><?= $si['value'] ?></div>
                    <div class="home-stat-label"><i class="fas <?= $si['icon'] ?> ml-1"></i><?= e($si['label']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Insights -->
    <?php if (!$setupRequired): ?>
    <section class="home-section home-insights">
        <div class="container">
            <div class="home-insights-grid">
                <?php foreach ($spotlights as $sp): ?>
                <div class="home-insight">
                    <span class="home-insight-icon"><i class="fas <?= e($sp['icon']) ?>"></i></span>
                    <div>
                        <div class="home-insight-label"><?= e($sp['title']) ?></div>
                        <div class="home-insight-name"><?= e($sp['name']) ?></div>
                    </div>
                    <div class="home-insight-count"><?= $sp['total'] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Timetable Browser -->
    <section id="timetable-browser" class="home-section home-browser">
        <div class="container">
            <div class="home-section-head">
                <h2>استعراض الجداول</h2>
                <p>اختر معايير البحث لعرض الجدول الدراسي</p>
            </div>
            <div class="home-browser-layout">
                <!-- Filter Panel -->
                <aside class="home-filter-panel">
                    <h3><i class="fas fa-sliders ml-2"></i>خيارات البحث</h3>
                    <?php if ($setupRequired): ?>
                        <div class="alert alert-warning mb-0">
                            يرجى إعداد قاعدة البيانات أولاً.
                            <a href="<?= url('/install.php') ?>" class="alert-link">معالج التثبيت</a>
                        </div>
                    <?php else: ?>
                        <form method="GET" action="<?= url('/') ?>">
                            <div class="home-field">
                                <label>القسم</label>
                                <select name="department_id">
                                    <option value="">جميع الأقسام</option>
                                    <?php foreach ($departments as $d): ?>
                                    <option value="<?= (int) $d['department_id'] ?>" <?= ($filters['department_id'] ?? null) === (int) $d['department_id'] ? 'selected' : '' ?>><?= e($d['department_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="home-field">
                                <label>الفرقة</label>
                                <select name="level_id">
                                    <option value="">جميع الفرق</option>
                                    <?php foreach ($levels as $l): ?>
                                    <option value="<?= (int) $l['level_id'] ?>" <?= ($filters['level_id'] ?? null) === (int) $l['level_id'] ? 'selected' : '' ?>><?= e($l['level_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="home-field">
                                <label>عضو هيئة التدريس</label>
                                <select name="member_id">
                                    <option value="">الكل</option>
                                    <?php foreach ($members as $m): ?>
                                    <option value="<?= (int) $m['member_id'] ?>" <?= ($filters['member_id'] ?? null) === (int) $m['member_id'] ? 'selected' : '' ?>><?= e($m['member_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="home-field">
                                <label>القاعة</label>
                                <select name="classroom_id">
                                    <option value="">جميع القاعات</option>
                                    <?php foreach ($classrooms as $c): ?>
                                    <option value="<?= (int) $c['classroom_id'] ?>" <?= ($filters['classroom_id'] ?? null) === (int) $c['classroom_id'] ? 'selected' : '' ?>><?= e($c['classroom_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="home-filter-actions">
                                <button type="submit" class="home-btn-search"><i class="fas fa-search ml-1"></i>بحث</button>
                                <a href="<?= url('/') ?>" class="home-btn-reset">إعادة تعيين</a>
                            </div>
                        </form>

                        <?php if (!empty($selectedFilters)): ?>
                        <div class="home-active-chips">
                            <?php foreach ($selectedFilters as $sf): ?>
                            <span class="home-chip"><i class="fas fa-filter"></i><?= e($sf['label']) ?>: <?= e($sf['value']) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <div class="home-status-cards">
                            <div class="home-status-card">
                                <span class="home-status-icon"><i class="fas fa-wave-square"></i></span>
                                <div>
                                    <div class="home-status-label">الوضع الحالي</div>
                                    <div class="home-status-val"><?= $hasFilter ? 'بحث مخصص' : 'عرض عام' ?></div>
                                </div>
                            </div>
                            <div class="home-status-card">
                                <span class="home-status-icon"><i class="fas fa-list-ol"></i></span>
                                <div>
                                    <div class="home-status-label">عدد النتائج</div>
                                    <div class="home-status-val"><?= (int) $resultsCount ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </aside>

                <!-- Results -->
                <div class="home-results">
                    <div class="home-results-header">
                        <h3><?= $hasFilter ? 'نتائج البحث' : 'آخر المحاضرات المُدخلة' ?></h3>
                        <span class="home-results-badge"><?= (int) $resultsCount ?> <?= $hasFilter ? 'نتيجة' : 'عنصر' ?></span>
                    </div>

                    <?php if ($setupRequired): ?>
                        <div class="home-empty-state">
                            <h3>قاعدة البيانات غير جاهزة</h3>
                            <p>يرجى إتمام التثبيت أولاً لعرض الجداول.</p>
                        </div>

                    <?php elseif ($hasFilter && !empty($pivotData)): ?>
                        <div class="home-table-wrap table-responsive mb-3">
                            <table class="table table-bordered">
                                <thead><tr>
                                    <th>اليوم</th>
                                    <?php foreach ($pivotData['sessions'] as $session): ?>
                                    <th><?= e($session['session_name'] ?? '') ?><br><small><?= e($session['start_time'] ?? '') ?> - <?= e($session['end_time'] ?? '') ?></small></th>
                                    <?php endforeach; ?>
                                </tr></thead>
                                <tbody>
                                <?php foreach ($pivotData['days'] as $day): ?>
                                    <tr>
                                        <td class="home-pivot-day"><?= e($day) ?></td>
                                        <?php foreach ($pivotData['sessions'] as $session): ?>
                                        <td>
                                            <?php $cellEntries = $pivotData['data'][$day][$session['slot_key']] ?? []; ?>
                                            <?php if (!empty($cellEntries)): ?>
                                                <?php foreach ($cellEntries as $ce): ?>
                                                <div class="home-entry <?= (($ce['assignment_type'] ?? '') === 'عملي') ? 'home-entry-practical' : '' ?>">
                                                    <div class="home-entry-title"><?= e($ce['subject_name'] ?? '') ?></div>
                                                    <div class="home-entry-meta"><?= e($ce['member_name'] ?? '') ?></div>
                                                    <div class="home-entry-meta"><?= e($ce['classroom_name'] ?? '') ?> | <?= e($ce['section_name'] ?? 'عامة') ?></div>
                                                </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="home-empty-cell">—</div>
                                            <?php endif; ?>
                                        </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (!empty($entries)): ?>
                        <div class="home-table-wrap table-responsive">
                            <table class="table table-hover">
                                <thead><tr><th>#</th><th>المقرر</th><th>المحاضر</th><th>الشعبة</th><th>القسم</th><th>الفرقة</th><th>اليوم</th><th>الوقت</th><th>القاعة</th></tr></thead>
                                <tbody>
                                <?php foreach ($entries as $i => $entry): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= e($entry['subject_name'] ?? '') ?></td>
                                    <td><?= e($entry['member_name'] ?? '') ?></td>
                                    <td><?= e($entry['section_name'] ?? '') ?></td>
                                    <td><?= e($entry['department_name'] ?? '') ?></td>
                                    <td><?= e($entry['level_name'] ?? '') ?></td>
                                    <td><?= e($entry['day'] ?? '') ?></td>
                                    <td><?= e($entry['start_time'] ?? '') ?> - <?= e($entry['end_time'] ?? '') ?></td>
                                    <td><?= e($entry['classroom_name'] ?? '') ?></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>

                    <?php elseif ($hasFilter): ?>
                        <?php if (!empty($entries)): ?>
                            <?php if (empty($pivotData) && (!empty($filters['department_id']) || !empty($filters['level_id']))): ?>
                            <div class="alert alert-info border mb-3" style="background:rgba(56,189,248,0.08);border-color:rgba(56,189,248,0.2)!important;color:var(--home-accent);">
                                <i class="fas fa-info-circle ml-1"></i>اختر القسم والفرقة معاً لعرض المخطط الأسبوعي.
                            </div>
                            <?php endif; ?>
                            <div class="home-table-wrap table-responsive">
                                <table class="table table-hover">
                                    <thead><tr><th>#</th><th>المقرر</th><th>المحاضر</th><th>الشعبة</th><th>القسم</th><th>الفرقة</th><th>اليوم</th><th>الوقت</th><th>القاعة</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($entries as $i => $entry): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= e($entry['subject_name'] ?? '') ?></td>
                                        <td><?= e($entry['member_name'] ?? '') ?></td>
                                        <td><?= e($entry['section_name'] ?? '') ?></td>
                                        <td><?= e($entry['department_name'] ?? '') ?></td>
                                        <td><?= e($entry['level_name'] ?? '') ?></td>
                                        <td><?= e($entry['day'] ?? '') ?></td>
                                        <td><?= e($entry['start_time'] ?? '') ?> - <?= e($entry['end_time'] ?? '') ?></td>
                                        <td><?= e($entry['classroom_name'] ?? '') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="home-empty-state">
                                <h3>لا توجد نتائج</h3>
                                <p>جرّب تعديل معايير البحث أو إزالة بعض الفلاتر.</p>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <?php if (!empty($recentEntries)): ?>
                            <div class="home-feed">
                                <?php foreach ($recentEntries as $entry): ?>
                                <article class="home-feed-card">
                                    <h4><?= e($entry['subject_name'] ?? '') ?></h4>
                                    <p><?= e($entry['member_name'] ?? '') ?> · <?= e($entry['classroom_name'] ?? '') ?></p>
                                    <div class="home-feed-pills">
                                        <span class="home-feed-pill"><i class="fas fa-building"></i><?= e($entry['department_name'] ?? '') ?></span>
                                        <span class="home-feed-pill"><i class="fas fa-layer-group"></i><?= e($entry['level_name'] ?? '') ?></span>
                                        <span class="home-feed-pill"><i class="fas fa-calendar-day"></i><?= e($entry['day'] ?? '') ?></span>
                                        <span class="home-feed-pill"><i class="fas fa-clock"></i><?= e($entry['start_time'] ?? '') ?> - <?= e($entry['end_time'] ?? '') ?></span>
                                    </div>
                                </article>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="home-empty-state">
                                <h3>لا توجد بيانات بعد</h3>
                                <p>ستظهر الجداول هنا تلقائياً بعد إدخال البيانات من لوحة التحكم.</p>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="home-footer home-section">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>© <?= date('Y') ?> <?= e(config('app.name', 'نظام الجداول')) ?></div>
        <div class="mt-2 mt-md-0">نظام إدارة الجداول الدراسية</div>
    </div>
</footer>
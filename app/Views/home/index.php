<?php
$this->layout('layouts.public');
$__page_title = 'الرئيسية';
?>

<?php $this->section('styles'); ?>
<style>
    :root {
        --public-bg: #f6f0e4;
        --public-surface: rgba(255, 252, 246, 0.88);
        --public-border: rgba(122, 92, 54, 0.16);
        --public-ink: #17313a;
        --public-muted: #5f6f71;
        --public-accent: #0f766e;
        --public-accent-dark: #155e75;
        --public-warm: #d97706;
        --public-shadow: 0 24px 60px rgba(58, 44, 25, 0.10);
    }

    body.public-home-page {
        margin: 0;
        color: var(--public-ink);
        font-family: 'Tajawal', sans-serif;
        background:
            radial-gradient(circle at top right, rgba(15, 118, 110, 0.18), transparent 32%),
            radial-gradient(circle at left 20%, rgba(217, 119, 6, 0.12), transparent 28%),
            linear-gradient(180deg, #f8f4ea 0%, var(--public-bg) 52%, #efe8d8 100%);
    }

    .public-shell {
        min-height: 100vh;
    }

    .public-topbar {
        position: sticky;
        top: 0;
        z-index: 20;
        backdrop-filter: blur(14px);
        background: rgba(246, 240, 228, 0.78);
        border-bottom: 1px solid rgba(122, 92, 54, 0.10);
    }

    .public-brand {
        font-family: 'Changa', sans-serif;
        font-size: 1.2rem;
        color: var(--public-ink);
        text-decoration: none;
    }

    .public-brand:hover {
        color: var(--public-accent);
        text-decoration: none;
    }

    .public-hero {
        padding: 4.5rem 0 2.5rem;
    }

    .public-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 0.95rem;
        border-radius: 999px;
        background: rgba(15, 118, 110, 0.10);
        color: var(--public-accent);
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .public-hero-title {
        font-family: 'Changa', sans-serif;
        font-size: clamp(2.2rem, 5vw, 4.4rem);
        line-height: 1.15;
        margin-bottom: 1rem;
        max-width: 9em;
    }

    .public-hero-copy,
    .public-feature p,
    .public-section-copy,
    .public-note,
    .public-highlight p {
        color: var(--public-muted);
        line-height: 1.9;
    }

    .public-hero-copy {
        font-size: 1.12rem;
        max-width: 42rem;
    }

    .public-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.75rem;
    }

    .public-btn-primary,
    .public-btn-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        border-radius: 999px;
        padding: 0.9rem 1.35rem;
        font-weight: 700;
        text-decoration: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .public-btn-primary {
        color: #fff;
        background: linear-gradient(135deg, var(--public-accent-dark), var(--public-accent));
        box-shadow: 0 18px 40px rgba(15, 118, 110, 0.22);
    }

    .public-btn-secondary {
        color: var(--public-ink);
        background: rgba(255, 255, 255, 0.62);
        border: 1px solid rgba(23, 49, 58, 0.10);
    }

    .public-btn-primary:hover,
    .public-btn-secondary:hover {
        transform: translateY(-2px);
        text-decoration: none;
    }

    .public-panel {
        background: var(--public-surface);
        border: 1px solid var(--public-border);
        border-radius: 28px;
        box-shadow: var(--public-shadow);
    }

    .public-highlight {
        padding: 1.6rem;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .public-highlight::after {
        content: '';
        position: absolute;
        width: 9rem;
        height: 9rem;
        border-radius: 50%;
        background: rgba(15, 118, 110, 0.10);
        left: -2rem;
        bottom: -2rem;
    }

    .public-highlight h3,
    .public-feature h3,
    .public-section-title {
        font-family: 'Changa', sans-serif;
    }

    .public-mini-list {
        display: grid;
        gap: 0.9rem;
        margin-top: 1.2rem;
    }

    .public-mini-item {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        padding: 0.9rem 1rem;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.55);
        position: relative;
        z-index: 1;
    }

    .public-mini-icon,
    .public-feature-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .public-mini-icon {
        width: 2.4rem;
        height: 2.4rem;
        border-radius: 14px;
        background: rgba(15, 118, 110, 0.12);
        color: var(--public-accent);
    }

    .public-stats,
    .public-features {
        padding-top: 1rem;
    }

    .public-stat-card,
    .public-feature {
        height: 100%;
        padding: 1.4rem;
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.70);
        border: 1px solid rgba(122, 92, 54, 0.10);
        box-shadow: 0 16px 36px rgba(58, 44, 25, 0.06);
    }

    .public-stat-label {
        color: var(--public-muted);
        font-size: 0.95rem;
        margin-bottom: 0.6rem;
    }

    .public-stat-value {
        font-family: 'Changa', sans-serif;
        font-size: 2rem;
        line-height: 1;
        margin-bottom: 0.55rem;
    }

    .public-feature-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 18px;
        margin-bottom: 1rem;
        background: rgba(217, 119, 6, 0.12);
        color: var(--public-warm);
    }

    .public-browser {
        margin: 2.5rem 0 4rem;
        padding: 2rem;
    }

    .public-browser .form-control {
        height: calc(2.8rem + 2px);
        border-radius: 16px;
        border-color: rgba(23, 49, 58, 0.10);
    }

    .public-browser .btn {
        border-radius: 16px;
        padding: 0.7rem 1.25rem;
        font-weight: 700;
    }

    .public-table-wrap {
        border-radius: 22px;
        overflow: hidden;
        border: 1px solid rgba(122, 92, 54, 0.10);
        background: rgba(255, 255, 255, 0.85);
    }

    .public-table-wrap table {
        margin-bottom: 0;
    }

    .public-table-wrap thead th {
        background: #19434a;
        color: #fff;
        border-color: rgba(255, 255, 255, 0.12);
        vertical-align: middle;
    }

    .public-pivot-day {
        background: rgba(15, 118, 110, 0.08);
        font-weight: 700;
        min-width: 88px;
    }

    .public-entry-cell {
        border-radius: 16px;
        padding: 0.7rem 0.8rem;
        margin-bottom: 0.45rem;
        color: #fff;
        background: linear-gradient(135deg, #0f766e, #155e75);
        box-shadow: 0 10px 20px rgba(15, 118, 110, 0.16);
    }

    .public-entry-cell.public-entry-practical {
        background: linear-gradient(135deg, #b45309, #d97706);
    }

    .public-entry-title {
        font-weight: 700;
        margin-bottom: 0.35rem;
    }

    .public-empty {
        color: #94a3b8;
        text-align: center;
        min-width: 72px;
    }

    .public-footer {
        padding: 0 0 2.25rem;
        color: var(--public-muted);
    }

    @media (max-width: 991.98px) {
        .public-hero {
            padding-top: 3rem;
        }

        .public-browser {
            padding: 1.35rem;
        }
    }

    @media (max-width: 575.98px) {
        .public-hero-title {
            font-size: 2rem;
        }

        .public-panel,
        .public-stat-card,
        .public-feature {
            border-radius: 20px;
        }
    }
</style>
<?php $this->endSection(); ?>

<?php
$ctaUrl = $auth ? url('/dashboard') : url('/login');
$ctaLabel = $auth ? 'دخول لوحة التحكم' : 'تسجيل الدخول للإدارة';
?>

<div class="public-shell">
    <header class="public-topbar">
        <div class="container py-3 d-flex flex-wrap align-items-center justify-content-between">
            <a href="<?= url('/') ?>" class="public-brand">
                <i class="fas fa-bezier-curve ml-2"></i><?= e(config('app.name', 'نظام الجداول')) ?>
            </a>
            <a href="<?= $ctaUrl ?>" class="public-btn-secondary">
                <i class="fas <?= $auth ? 'fa-columns' : 'fa-sign-in-alt' ?>"></i>
                <span><?= e($ctaLabel) ?></span>
            </a>
        </div>
    </header>

    <main>
        <section class="public-hero">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-7 mb-4 mb-lg-0">
                        <span class="public-badge"><i class="fas fa-satellite-dish"></i>منصة عرض وإدارة الجداول الأكاديمية</span>
                        <h1 class="public-hero-title"><?= e($institutionName) ?></h1>
                        <p class="public-hero-copy">
                            صفحة رئيسية عامة تعرض تعريفاً بالنظام، مؤشرات سريعة عن البيانات الأكاديمية، واستعراضاً مباشراً للجداول الدراسية دون الحاجة إلى تسجيل دخول.
                        </p>
                        <div class="public-actions">
                            <a href="#timetable-browser" class="public-btn-primary"><i class="fas fa-table"></i>استعراض الجداول</a>
                            <a href="<?= $ctaUrl ?>" class="public-btn-secondary"><i class="fas <?= $auth ? 'fa-columns' : 'fa-user-shield' ?>"></i><?= e($ctaLabel) ?></a>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="public-panel public-highlight">
                            <h3>ما الذي تعرضه الصفحة الآن؟</h3>
                            <p>ملخص سريع عن الهيكل الأكاديمي، نافذة عامة لاستكشاف الجداول، وعرض آخر البيانات المدرجة في الجدول لتسهيل الاطلاع من خارج لوحة الإدارة.</p>
                            <div class="public-mini-list">
                                <div class="public-mini-item">
                                    <span class="public-mini-icon"><i class="fas fa-layer-group"></i></span>
                                    <div>
                                        <strong>إحصاءات حية</strong>
                                        <div class="public-note">عدد الأقسام والفرق والمقررات والقاعات وعناصر الجدول الحالية.</div>
                                    </div>
                                </div>
                                <div class="public-mini-item">
                                    <span class="public-mini-icon"><i class="fas fa-calendar-alt"></i></span>
                                    <div>
                                        <strong>استعراض أسبوعي</strong>
                                        <div class="public-note">يمكن اختيار القسم والفرقة لعرض الجدول بصيغة أسبوعية واضحة.</div>
                                    </div>
                                </div>
                                <div class="public-mini-item">
                                    <span class="public-mini-icon"><i class="fas fa-user-lock"></i></span>
                                    <div>
                                        <strong>إدارة منفصلة</strong>
                                        <div class="public-note">تبقى عمليات الإدارة والتعديل والحذف داخل لوحة التحكم بعد تسجيل الدخول فقط.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="public-stats">
                    <div class="row">
                        <div class="col-6 col-lg-2 mb-3"><div class="public-stat-card"><div class="public-stat-label">الأقسام</div><div class="public-stat-value"><?= (int) ($stats['departments'] ?? 0) ?></div><div class="public-note">أقسام مفعلة</div></div></div>
                        <div class="col-6 col-lg-2 mb-3"><div class="public-stat-card"><div class="public-stat-label">الفرق</div><div class="public-stat-value"><?= (int) ($stats['levels'] ?? 0) ?></div><div class="public-note">مستويات دراسية</div></div></div>
                        <div class="col-6 col-lg-2 mb-3"><div class="public-stat-card"><div class="public-stat-label">المقررات</div><div class="public-stat-value"><?= (int) ($stats['subjects'] ?? 0) ?></div><div class="public-note">مقررات فعالة</div></div></div>
                        <div class="col-6 col-lg-2 mb-3"><div class="public-stat-card"><div class="public-stat-label">هيئة التدريس</div><div class="public-stat-value"><?= (int) ($stats['members'] ?? 0) ?></div><div class="public-note">أعضاء مفعّلون</div></div></div>
                        <div class="col-6 col-lg-2 mb-3"><div class="public-stat-card"><div class="public-stat-label">القاعات</div><div class="public-stat-value"><?= (int) ($stats['classrooms'] ?? 0) ?></div><div class="public-note">قاعات متاحة</div></div></div>
                        <div class="col-6 col-lg-2 mb-3"><div class="public-stat-card"><div class="public-stat-label">عناصر الجدول</div><div class="public-stat-value"><?= (int) ($stats['timetable'] ?? 0) ?></div><div class="public-note">مدخلات مجدولة</div></div></div>
                    </div>
                </div>

                <div class="public-features">
                    <div class="row">
                        <div class="col-lg-4 mb-3"><div class="public-feature"><div class="public-feature-icon"><i class="fas fa-route"></i></div><h3>وصول مباشر</h3><p>يفتح النظام من الجذر مباشرة بدون الحاجة لإضافة <span dir="ltr">public</span> في الرابط، مع بقاء بنية التطبيق الداخلية كما هي.</p></div></div>
                        <div class="col-lg-4 mb-3"><div class="public-feature"><div class="public-feature-icon"><i class="fas fa-search"></i></div><h3>بحث عام في الجداول</h3><p>يمكن لزائر الموقع اختيار القسم والفرقة ثم عرض الجدول الأسبوعي وبيانات التوزيع مباشرة من الصفحة الرئيسية.</p></div></div>
                        <div class="col-lg-4 mb-3"><div class="public-feature"><div class="public-feature-icon"><i class="fas fa-shield-alt"></i></div><h3>إدارة محمية</h3><p>التعديل والإدارة ونقل البيانات تبقى خلف تسجيل الدخول، بينما تظل المعلومات العامة والعرض متاحة للجميع.</p></div></div>
                    </div>
                </div>
            </div>
        </section>

        <section id="timetable-browser">
            <div class="container">
                <div class="public-panel public-browser">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between mb-4">
                        <div class="mb-3 mb-lg-0">
                            <h2 class="public-section-title mb-2">استعراض الجداول</h2>
                            <p class="public-section-copy mb-0">اختر القسم والفرقة لعرض الجدول الأسبوعي، أو تصفح آخر العناصر المدرجة في النظام إذا لم تحدد أي مرشح.</p>
                        </div>
                        <div><a href="<?= $ctaUrl ?>" class="public-btn-secondary"><i class="fas <?= $auth ? 'fa-columns' : 'fa-sign-in-alt' ?>"></i><?= e($ctaLabel) ?></a></div>
                    </div>

                    <?php if ($setupRequired): ?>
                        <div class="alert alert-warning mb-0">لا يمكن تحميل بيانات الصفحة حالياً. تأكد من إعداد قاعدة البيانات وتشغيل التثبيت ثم أعد المحاولة. <a href="<?= url('/install.php') ?>" class="alert-link mr-1">فتح معالج التثبيت</a></div>
                    <?php else: ?>
                        <form method="GET" action="<?= url('/') ?>" class="mb-4">
                            <div class="form-row align-items-end">
                                <div class="col-md-5 mb-3">
                                    <label>القسم</label>
                                    <select name="department_id" class="form-control">
                                        <option value="">اختر القسم</option>
                                        <?php foreach ($departments as $department): ?>
                                            <option value="<?= (int) $department['department_id'] ?>" <?= ($filters['department_id'] ?? null) === (int) $department['department_id'] ? 'selected' : '' ?>><?= e($department['department_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label>الفرقة</label>
                                    <select name="level_id" class="form-control">
                                        <option value="">اختر الفرقة</option>
                                        <?php foreach ($levels as $level): ?>
                                            <option value="<?= (int) $level['level_id'] ?>" <?= ($filters['level_id'] ?? null) === (int) $level['level_id'] ? 'selected' : '' ?>><?= e($level['level_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <button type="submit" class="btn btn-dark btn-block"><i class="fas fa-search ml-1"></i>عرض</button>
                                </div>
                            </div>
                        </form>

                        <?php if ($hasFilter && !empty($pivotData)): ?>
                            <div class="public-table-wrap table-responsive mb-4">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width: 90px;">اليوم</th>
                                            <?php foreach ($pivotData['sessions'] as $session): ?>
                                                <th><?= e($session['session_name'] ?? '') ?><br><small><?= e($session['start_time'] ?? '') ?> - <?= e($session['end_time'] ?? '') ?></small></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pivotData['days'] as $day): ?>
                                            <tr>
                                                <td class="public-pivot-day"><?= e($day) ?></td>
                                                <?php foreach ($pivotData['sessions'] as $session): ?>
                                                    <td>
                                                        <?php $cellEntries = $pivotData['data'][$day][$session['slot_key']] ?? []; ?>
                                                        <?php if (!empty($cellEntries)): ?>
                                                            <?php foreach ($cellEntries as $cellEntry): ?>
                                                                <?php $entryClass = (($cellEntry['assignment_type'] ?? '') === 'عملي') ? 'public-entry-cell public-entry-practical' : 'public-entry-cell'; ?>
                                                                <div class="<?= $entryClass ?>">
                                                                    <div class="public-entry-title"><?= e($cellEntry['subject_name'] ?? '') ?></div>
                                                                    <div><?= e($cellEntry['member_name'] ?? '') ?></div>
                                                                    <small><?= e($cellEntry['classroom_name'] ?? '') ?> | <?= e($cellEntry['section_name'] ?? 'محاضرة عامة') ?></small>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <div class="public-empty">—</div>
                                                        <?php endif; ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                        <?php if ($hasFilter): ?>
                            <?php if (!empty($entries)): ?>
                                <div class="public-table-wrap table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr><th>#</th><th>المقرر</th><th>عضو هيئة التدريس</th><th>الشعبة</th><th>القسم</th><th>الفرقة</th><th>اليوم</th><th>الفترة</th><th>القاعة</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($entries as $index => $entry): ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
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
                                <div class="alert alert-info mb-0">لا توجد بيانات جدول مطابقة للمرشحات المحددة حالياً.</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if (!empty($recentEntries)): ?>
                                <div class="public-table-wrap table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr><th>المقرر</th><th>عضو هيئة التدريس</th><th>القسم</th><th>الفرقة</th><th>اليوم</th><th>الفترة</th><th>القاعة</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentEntries as $entry): ?>
                                                <tr>
                                                    <td><?= e($entry['subject_name'] ?? '') ?></td>
                                                    <td><?= e($entry['member_name'] ?? '') ?></td>
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
                                <div class="alert alert-light border mb-0">لا توجد عناصر جدول معروضة حالياً. يمكنك البدء بإدخال البيانات من لوحة التحكم.</div>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <footer class="public-footer">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div>© <?= date('Y') ?> <?= e(config('app.name', 'نظام الجداول')) ?></div>
            <div class="mt-2 mt-md-0">عرض عام للجداول والبيانات الأساسية مع بقاء الإدارة خلف تسجيل الدخول.</div>
        </div>
    </footer>
</div>
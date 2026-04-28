<?php
$this->layout('layouts.public');
$__page_title = 'الرئيسية';

$ctaUrl = $auth ? url('/dashboard') : url('/login');
$ctaLabel = $auth ? 'دخول لوحة التحكم' : 'تسجيل الدخول للإدارة';
$resultsCount = $hasFilter ? count($entries) : count($recentEntries);

$resolveLabel = static function (array $rows, string $idKey, string $labelKey, ?int $selectedId): ?string {
    if ($selectedId === null) {
        return null;
    }

    foreach ($rows as $row) {
        if ((int) ($row[$idKey] ?? 0) === $selectedId) {
            return (string) ($row[$labelKey] ?? '');
        }
    }

    return null;
};

$selectedFilters = array_values(array_filter([
    ['label' => 'القسم', 'value' => $resolveLabel($departments, 'department_id', 'department_name', $filters['department_id'] ?? null)],
    ['label' => 'الفرقة', 'value' => $resolveLabel($levels, 'level_id', 'level_name', $filters['level_id'] ?? null)],
    ['label' => 'عضو هيئة التدريس', 'value' => $resolveLabel($members, 'member_id', 'member_name', $filters['member_id'] ?? null)],
    ['label' => 'القاعة', 'value' => $resolveLabel($classrooms, 'classroom_id', 'classroom_name', $filters['classroom_id'] ?? null)],
], static fn(array $item): bool => !empty($item['value'])));

$overviewCards = [
    [
        'icon' => 'fa-sitemap',
        'title' => 'هيكل أكاديمي متكامل',
        'text' => 'الأقسام والفرق وأعضاء هيئة التدريس والقاعات تُعرض في واجهة عامة واضحة تسهّل فهم بنية النظام قبل الدخول إلى لوحة الإدارة.',
    ],
    [
        'icon' => 'fa-table-cells-large',
        'title' => 'استعراض مباشر للجداول',
        'text' => 'يمكن للزائر فلترة الجداول حسب القسم والفرقة أو التوسّع بالبحث عبر عضو هيئة التدريس والقاعة دون الحاجة إلى حساب دخول.',
    ],
    [
        'icon' => 'fa-shield-halved',
        'title' => 'إدارة محمية ومنفصلة',
        'text' => 'تبقى عمليات التعديل والحذف والإعدادات الحساسة داخل مساحة الإدارة فقط، بينما تظل المعلومات العامة متاحة للاستعراض بأمان.',
    ],
];

$workflowSteps = [
    [
        'number' => '01',
        'title' => 'اختر نطاق العرض',
        'text' => 'حدّد القسم والفرقة أو أضف عضو هيئة التدريس والقاعة للوصول إلى نطاق أدق للجدول العام.',
    ],
    [
        'number' => '02',
        'title' => 'راجع المخطط الأسبوعي',
        'text' => 'عند تحديد القسم والفرقة يظهر لك الجدول الأسبوعي بتقسيم الفترات على الأيام بصورة مباشرة.',
    ],
    [
        'number' => '03',
        'title' => 'انتقل للإدارة عند الحاجة',
        'text' => 'إذا كنت من فريق العمل يمكنك الدخول إلى لوحة التحكم لمتابعة التعديل والإدارة من المسار المخصص لذلك.',
    ],
];

$spotlights = [
    [
        'title' => 'القسم الأكثر نشاطاً',
        'icon' => 'fa-building-columns',
        'name' => $insights['topDepartment']['name'] ?? 'لا توجد بيانات بعد',
        'total' => (int) ($insights['topDepartment']['total'] ?? 0),
        'suffix' => 'عنصراً مجدولاً',
    ],
    [
        'title' => 'الأكثر ظهوراً في الجداول',
        'icon' => 'fa-user-tie',
        'name' => $insights['topMember']['name'] ?? 'لا توجد بيانات بعد',
        'total' => (int) ($insights['topMember']['total'] ?? 0),
        'suffix' => 'فترة مسندة',
    ],
    [
        'title' => 'القاعة الأكثر استخداماً',
        'icon' => 'fa-door-open',
        'name' => $insights['topClassroom']['name'] ?? 'لا توجد بيانات بعد',
        'total' => (int) ($insights['topClassroom']['total'] ?? 0),
        'suffix' => 'مرة استخدام',
    ],
];
?>

<?php $this->section('styles'); ?>
<style>
    :root {
        --public-bg: #f7f0e5;
        --public-bg-strong: #f1e5d1;
        --public-surface: rgba(255, 252, 248, 0.86);
        --public-surface-strong: rgba(255, 255, 255, 0.92);
        --public-border: rgba(104, 79, 45, 0.14);
        --public-ink: #19343d;
        --public-muted: #627475;
        --public-accent: #0f766e;
        --public-accent-dark: #155e75;
        --public-gold: #d97706;
        --public-shadow: 0 24px 60px rgba(63, 46, 24, 0.10);
        --public-shadow-soft: 0 14px 32px rgba(63, 46, 24, 0.08);
    }

    html {
        scroll-behavior: smooth;
    }

    body.public-home-page {
        margin: 0;
        color: var(--public-ink);
        font-family: 'Tajawal', sans-serif;
        background:
            radial-gradient(circle at 88% 10%, rgba(15, 118, 110, 0.18), transparent 24%),
            radial-gradient(circle at 10% 14%, rgba(217, 119, 6, 0.16), transparent 22%),
            linear-gradient(180deg, #fbf7f0 0%, var(--public-bg) 46%, var(--public-bg-strong) 100%);
        overflow-x: hidden;
    }

    .public-shell {
        min-height: 100vh;
        position: relative;
    }

    .public-shell::before,
    .public-shell::after {
        content: '';
        position: fixed;
        pointer-events: none;
        z-index: 0;
    }

    .public-shell::before {
        top: 7rem;
        left: -5rem;
        width: 14rem;
        height: 14rem;
        border-radius: 2.5rem;
        border: 1px solid rgba(15, 118, 110, 0.14);
        transform: rotate(18deg);
        opacity: 0.55;
    }

    .public-shell::after {
        bottom: 5rem;
        right: -4rem;
        width: 12rem;
        height: 12rem;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(217, 119, 6, 0.14) 0%, rgba(217, 119, 6, 0) 70%);
        filter: blur(3px);
    }

    .public-topbar {
        position: sticky;
        top: 0;
        z-index: 50;
        backdrop-filter: blur(16px);
        background: rgba(248, 241, 228, 0.78);
        border-bottom: 1px solid rgba(104, 79, 45, 0.09);
    }

    a.public-brand,
    a.public-brand:visited,
    a.public-brand:hover,
    a.public-brand:focus {
        color: var(--public-ink) !important;
        font-family: 'Changa', sans-serif;
        font-size: 1.2rem;
        text-decoration: none !important;
        letter-spacing: 0.01em;
    }

    a.public-brand:hover,
    a.public-brand:focus {
        color: var(--public-accent) !important;
    }

    .public-topbar-links {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    a.public-nav-link,
    a.public-nav-link:visited,
    a.public-nav-link:hover,
    a.public-nav-link:focus {
        color: var(--public-muted) !important;
        font-weight: 700;
        text-decoration: none !important;
        padding: 0.5rem 0.75rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.38);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s ease, background-color 0.2s ease, transform 0.2s ease;
    }

    a.public-nav-link:hover,
    a.public-nav-link:focus {
        color: var(--public-accent-dark) !important;
        background: rgba(15, 118, 110, 0.08);
        transform: translateY(-1px);
    }

    .public-section {
        position: relative;
        z-index: 1;
    }

    .public-hero {
        padding: 4.4rem 0 2.6rem;
    }

    .public-hero-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.08fr) minmax(320px, 0.92fr);
        gap: 1.6rem;
        align-items: stretch;
    }

    .public-glass,
    .public-panel {
        background: var(--public-surface);
        border: 1px solid var(--public-border);
        box-shadow: var(--public-shadow);
        border-radius: 30px;
        position: relative;
        overflow: hidden;
    }

    .public-glass::before,
    .public-panel::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.55), rgba(255, 255, 255, 0));
        pointer-events: none;
    }

    .public-hero-copywrap {
        padding: 2.25rem;
        min-height: 100%;
    }

    .public-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.5rem 1rem;
        border-radius: 999px;
        background: rgba(15, 118, 110, 0.10);
        color: var(--public-accent);
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .public-hero-title {
        font-family: 'Changa', sans-serif;
        font-size: clamp(2.3rem, 4.5vw, 4.6rem);
        line-height: 1.08;
        margin-bottom: 1rem;
        max-width: 9em;
    }

    .public-hero-copy,
    .public-note,
    .public-support-copy,
    .public-section-intro,
    .public-overview-card p,
    .public-step-card p,
    .public-summary-copy,
    .public-empty-state p,
    .public-table-caption {
        color: var(--public-muted);
        line-height: 1.9;
    }

    .public-hero-copy {
        max-width: 42rem;
        font-size: 1.08rem;
        margin-bottom: 0;
    }

    .public-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.8rem;
        margin-top: 1.7rem;
    }

    a.public-btn-primary,
    a.public-btn-primary:visited,
    a.public-btn-primary:hover,
    a.public-btn-primary:focus,
    a.public-btn-secondary,
    a.public-btn-secondary:visited,
    a.public-btn-secondary:hover,
    a.public-btn-secondary:focus,
    a.public-btn-ghost,
    a.public-btn-ghost:visited,
    a.public-btn-ghost:hover,
    a.public-btn-ghost:focus {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        border-radius: 999px;
        padding: 0.92rem 1.4rem;
        font-weight: 700;
        text-decoration: none !important;
        transition: transform 0.22s ease, box-shadow 0.22s ease, background-color 0.22s ease;
    }

    a.public-btn-primary,
    a.public-btn-primary:visited {
        color: #fff !important;
        background: linear-gradient(135deg, var(--public-accent-dark), var(--public-accent));
        box-shadow: 0 18px 40px rgba(15, 118, 110, 0.24);
    }

    a.public-btn-secondary,
    a.public-btn-secondary:visited {
        color: var(--public-ink) !important;
        background: rgba(255, 255, 255, 0.76);
        border: 1px solid rgba(25, 52, 61, 0.09);
        box-shadow: var(--public-shadow-soft);
    }

    a.public-btn-ghost,
    a.public-btn-ghost:visited {
        color: var(--public-accent-dark) !important;
        background: rgba(15, 118, 110, 0.12);
        border: 1px solid rgba(15, 118, 110, 0.16);
        box-shadow: var(--public-shadow-soft);
    }

    a.public-btn-primary:hover,
    a.public-btn-primary:focus,
    a.public-btn-secondary:hover,
    a.public-btn-secondary:focus,
    a.public-btn-ghost:hover,
    a.public-btn-ghost:focus {
        transform: translateY(-2px);
    }

    .public-inline-facts {
        display: flex;
        flex-wrap: wrap;
        gap: 0.9rem;
        margin-top: 1.6rem;
    }

    .public-inline-fact {
        min-width: 9rem;
        padding: 0.85rem 1rem;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.68);
        border: 1px solid rgba(104, 79, 45, 0.10);
        box-shadow: var(--public-shadow-soft);
    }

    .public-inline-fact strong {
        display: block;
        font-family: 'Changa', sans-serif;
        font-size: 1.3rem;
        line-height: 1;
        margin-bottom: 0.25rem;
    }

    .public-stage {
        min-height: 100%;
        padding: 1.85rem;
        display: grid;
        gap: 1rem;
        background:
            radial-gradient(circle at top left, rgba(255, 255, 255, 0.45), transparent 34%),
            linear-gradient(160deg, rgba(15, 118, 110, 0.10), rgba(255, 255, 255, 0.10));
    }

    .public-stage-shapes {
        position: absolute;
        inset: 0;
        pointer-events: none;
    }

    .public-orb {
        position: absolute;
        border-radius: 999px;
        opacity: 0.9;
    }

    .public-orb-a {
        width: 8.8rem;
        height: 8.8rem;
        top: -1.8rem;
        right: -1.2rem;
        background: radial-gradient(circle, rgba(15, 118, 110, 0.22) 0%, rgba(15, 118, 110, 0) 72%);
        animation: publicFloatA 7s ease-in-out infinite;
    }

    .public-orb-b {
        width: 6.5rem;
        height: 6.5rem;
        bottom: 3rem;
        left: -1.5rem;
        background: radial-gradient(circle, rgba(217, 119, 6, 0.20) 0%, rgba(217, 119, 6, 0) 72%);
        animation: publicFloatB 9s ease-in-out infinite;
    }

    .public-orb-c {
        width: 10rem;
        height: 10rem;
        top: 44%;
        right: 24%;
        border: 1px dashed rgba(25, 52, 61, 0.14);
        background: transparent;
        animation: publicPulse 10s linear infinite;
    }

    .public-spotlights {
        display: grid;
        gap: 0.9rem;
        position: relative;
        z-index: 1;
    }

    .public-spotlight {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 0.9rem;
        align-items: center;
        background: rgba(255, 255, 255, 0.74);
        border: 1px solid rgba(104, 79, 45, 0.10);
        border-radius: 22px;
        padding: 0.95rem 1rem;
        box-shadow: var(--public-shadow-soft);
    }

    .public-spotlight-icon,
    .public-overview-icon,
    .public-step-number,
    .public-summary-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .public-spotlight-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 18px;
        background: rgba(15, 118, 110, 0.12);
        color: var(--public-accent);
        font-size: 1.15rem;
    }

    .public-spotlight-title {
        color: var(--public-muted);
        font-size: 0.92rem;
        margin-bottom: 0.2rem;
    }

    .public-spotlight-name {
        font-weight: 700;
    }

    .public-spotlight-total {
        min-width: 4.1rem;
        padding: 0.7rem 0.75rem;
        border-radius: 18px;
        text-align: center;
        background: rgba(217, 119, 6, 0.10);
        color: var(--public-gold);
        font-weight: 700;
    }

    .public-top-ribbon {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
        margin-top: 1.4rem;
    }

    .public-ribbon-card {
        padding: 1rem 1.1rem;
        border-radius: 24px;
        background: var(--public-surface-strong);
        border: 1px solid rgba(104, 79, 45, 0.10);
        box-shadow: var(--public-shadow-soft);
        transform: translateY(22px);
        opacity: 0;
        animation: publicRise 0.9s ease forwards;
    }

    .public-ribbon-card:nth-child(2) {
        animation-delay: 0.12s;
    }

    .public-ribbon-card:nth-child(3) {
        animation-delay: 0.24s;
    }

    .public-ribbon-card strong {
        display: block;
        margin-bottom: 0.3rem;
        font-family: 'Changa', sans-serif;
    }

    .public-overview,
    .public-workflow,
    .public-browser,
    .public-footer {
        padding-top: 0.6rem;
    }

    .public-overview-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
        gap: 1.2rem;
    }

    .public-overview-lead,
    .public-browser-panel,
    .public-results-panel,
    .public-summary-card,
    .public-overview-card,
    .public-step-card {
        border-radius: 28px;
        background: rgba(255, 255, 255, 0.76);
        border: 1px solid rgba(104, 79, 45, 0.10);
        box-shadow: var(--public-shadow-soft);
    }

    .public-overview-lead {
        padding: 1.55rem;
        display: grid;
        gap: 1.2rem;
    }

    .public-overview-title,
    .public-browser-title,
    .public-step-title,
    .public-summary-title {
        font-family: 'Changa', sans-serif;
    }

    .public-mosaic {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
    }

    .public-stat-card {
        padding: 1.15rem;
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.80);
        border: 1px solid rgba(104, 79, 45, 0.10);
        box-shadow: var(--public-shadow-soft);
        min-height: 10rem;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        position: relative;
        overflow: hidden;
    }

    .public-stat-card:nth-child(1) {
        background: linear-gradient(160deg, rgba(15, 118, 110, 0.14), rgba(255, 255, 255, 0.92));
    }

    .public-stat-card:nth-child(4) {
        background: linear-gradient(160deg, rgba(217, 119, 6, 0.14), rgba(255, 255, 255, 0.92));
    }

    .public-stat-card::after {
        content: '';
        position: absolute;
        width: 5rem;
        height: 5rem;
        border-radius: 50%;
        border: 1px solid rgba(25, 52, 61, 0.08);
        top: -1.6rem;
        left: -1.4rem;
    }

    .public-stat-label {
        color: var(--public-muted);
        font-size: 0.95rem;
        margin-bottom: 0.55rem;
    }

    .public-stat-value {
        font-family: 'Changa', sans-serif;
        font-size: 2rem;
        line-height: 1;
        margin-bottom: 0.3rem;
    }

    .public-overview-stack,
    .public-summary-stack {
        display: grid;
        gap: 1rem;
    }

    .public-overview-card,
    .public-summary-card,
    .public-step-card {
        padding: 1.2rem 1.25rem;
    }

    .public-overview-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 18px;
        background: rgba(217, 119, 6, 0.12);
        color: var(--public-gold);
        margin-bottom: 0.95rem;
    }

    .public-workflow-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .public-step-card {
        position: relative;
        overflow: hidden;
    }

    .public-step-card::before {
        content: '';
        position: absolute;
        inset: auto auto -2rem -1.5rem;
        width: 7rem;
        height: 7rem;
        border-radius: 50%;
        background: rgba(15, 118, 110, 0.08);
    }

    .public-step-number {
        width: 3rem;
        height: 3rem;
        border-radius: 50%;
        background: rgba(15, 118, 110, 0.12);
        color: var(--public-accent-dark);
        font-family: 'Changa', sans-serif;
        margin-bottom: 1rem;
    }

    .public-browser-wrap {
        display: grid;
        grid-template-columns: minmax(280px, 0.8fr) minmax(0, 1.2fr);
        gap: 1.1rem;
        align-items: start;
    }

    .public-browser-panel,
    .public-results-panel {
        position: relative;
    }

    .public-browser-panel {
        padding: 1.45rem;
        position: sticky;
        top: 6.1rem;
    }

    .public-filter-grid {
        display: grid;
        gap: 0.9rem;
        margin-top: 1rem;
    }

    .public-filter-field label {
        display: block;
        font-weight: 700;
        margin-bottom: 0.45rem;
    }

    .public-filter-field .form-control {
        height: calc(2.9rem + 2px);
        border-radius: 18px;
        border-color: rgba(25, 52, 61, 0.10);
        box-shadow: none;
        background: rgba(255, 255, 255, 0.84);
    }

    .public-filter-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .public-filter-actions .btn {
        border-radius: 18px;
        padding: 0.8rem 1.25rem;
        font-weight: 700;
        min-width: 10rem;
    }

    .public-selected-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        margin-top: 1.1rem;
    }

    .public-filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.48rem 0.8rem;
        border-radius: 999px;
        background: rgba(15, 118, 110, 0.10);
        color: var(--public-accent-dark);
        font-weight: 700;
        font-size: 0.92rem;
    }

    .public-summary-stack {
        margin-top: 1.15rem;
    }

    .public-summary-card {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 0.85rem;
        align-items: center;
    }

    .public-summary-icon {
        width: 2.9rem;
        height: 2.9rem;
        border-radius: 16px;
        background: rgba(15, 118, 110, 0.12);
        color: var(--public-accent);
    }

    .public-summary-value {
        font-family: 'Changa', sans-serif;
        font-size: 1.45rem;
        line-height: 1;
        margin-bottom: 0.2rem;
    }

    .public-results-panel {
        padding: 1.45rem;
        min-height: 100%;
    }

    .public-results-head {
        display: flex;
        flex-wrap: wrap;
        align-items: start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .public-results-count {
        min-width: 8.8rem;
        padding: 0.95rem 1rem;
        border-radius: 22px;
        background: linear-gradient(145deg, rgba(15, 118, 110, 0.12), rgba(255, 255, 255, 0.82));
        border: 1px solid rgba(15, 118, 110, 0.12);
        box-shadow: var(--public-shadow-soft);
    }

    .public-results-count strong {
        display: block;
        font-family: 'Changa', sans-serif;
        font-size: 1.7rem;
        line-height: 1;
        margin-bottom: 0.2rem;
    }

    .public-support-banner {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem;
        margin-bottom: 1rem;
    }

    .public-support-card {
        padding: 1rem 1.05rem;
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.72);
        border: 1px solid rgba(104, 79, 45, 0.10);
        box-shadow: var(--public-shadow-soft);
    }

    .public-support-card strong {
        display: block;
        margin-bottom: 0.35rem;
    }

    .public-table-wrap {
        border-radius: 24px;
        overflow: hidden;
        border: 1px solid rgba(104, 79, 45, 0.10);
        background: rgba(255, 255, 255, 0.88);
        box-shadow: var(--public-shadow-soft);
    }

    .public-table-wrap table {
        margin-bottom: 0;
    }

    .public-table-wrap thead th {
        background: #184149;
        color: #fff;
        border-color: rgba(255, 255, 255, 0.10);
        vertical-align: middle;
        white-space: nowrap;
    }

    .public-table-wrap tbody td {
        vertical-align: middle;
    }

    .public-table-wrap tbody tr:hover {
        background: rgba(15, 118, 110, 0.04);
    }

    .public-pivot-day {
        background: rgba(15, 118, 110, 0.08);
        font-weight: 700;
        min-width: 92px;
    }

    .public-entry-cell {
        border-radius: 18px;
        padding: 0.75rem 0.82rem;
        margin-bottom: 0.5rem;
        color: #fff;
        background: linear-gradient(135deg, #0f766e, #155e75);
        box-shadow: 0 10px 20px rgba(15, 118, 110, 0.16);
    }

    .public-entry-cell.public-entry-practical {
        background: linear-gradient(135deg, #c26919, #dd8a2f);
    }

    .public-entry-title {
        font-weight: 700;
        margin-bottom: 0.3rem;
    }

    .public-entry-meta {
        font-size: 0.9rem;
        opacity: 0.95;
    }

    .public-empty {
        color: #94a3b8;
        min-width: 72px;
        text-align: center;
    }

    .public-feed-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .public-feed-card {
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.82);
        border: 1px solid rgba(104, 79, 45, 0.10);
        box-shadow: var(--public-shadow-soft);
        padding: 1.1rem 1.15rem;
    }

    .public-feed-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.75rem;
    }

    .public-feed-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.38rem 0.7rem;
        border-radius: 999px;
        background: rgba(15, 118, 110, 0.08);
        color: var(--public-accent-dark);
        font-size: 0.88rem;
        font-weight: 700;
    }

    .public-empty-state {
        padding: 1.5rem;
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.72);
        border: 1px dashed rgba(104, 79, 45, 0.18);
        text-align: center;
    }

    .public-footer {
        padding: 0.6rem 0 2.3rem;
        position: relative;
        z-index: 1;
        color: var(--public-muted);
    }

    .public-divider-title {
        display: inline-flex;
        align-items: center;
        gap: 0.65rem;
        margin-bottom: 0.8rem;
        font-weight: 700;
        color: var(--public-accent-dark);
    }

    .public-divider-title::before {
        content: '';
        width: 2rem;
        height: 1px;
        background: rgba(15, 118, 110, 0.40);
    }

    @keyframes publicRise {
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes publicFloatA {
        0%,
        100% {
            transform: translate3d(0, 0, 0);
        }

        50% {
            transform: translate3d(-10px, 14px, 0);
        }
    }

    @keyframes publicFloatB {
        0%,
        100% {
            transform: translate3d(0, 0, 0);
        }

        50% {
            transform: translate3d(12px, -10px, 0);
        }
    }

    @keyframes publicPulse {
        0% {
            transform: scale(0.96) rotate(0deg);
            opacity: 0.7;
        }

        50% {
            transform: scale(1.04) rotate(180deg);
            opacity: 1;
        }

        100% {
            transform: scale(0.96) rotate(360deg);
            opacity: 0.7;
        }
    }

    @media (max-width: 1199.98px) {
        .public-hero-grid,
        .public-overview-grid,
        .public-browser-wrap {
            grid-template-columns: 1fr;
        }

        .public-browser-panel {
            position: relative;
            top: auto;
        }
    }

    @media (max-width: 991.98px) {
        .public-hero {
            padding-top: 3rem;
        }

        .public-top-ribbon,
        .public-workflow-grid,
        .public-support-banner,
        .public-feed-grid {
            grid-template-columns: 1fr;
        }

        .public-hero-copywrap,
        .public-stage,
        .public-browser-panel,
        .public-results-panel,
        .public-overview-lead {
            padding: 1.35rem;
        }
    }

    @media (max-width: 767.98px) {
        .public-mosaic {
            grid-template-columns: 1fr;
        }

        .public-results-head {
            flex-direction: column;
        }

        .public-filter-actions .btn {
            width: 100%;
            min-width: 0;
        }
    }

    @media (max-width: 575.98px) {
        .public-hero-title {
            font-size: 2rem;
        }

        .public-glass,
        .public-panel,
        .public-overview-lead,
        .public-overview-card,
        .public-step-card,
        .public-summary-card,
        .public-feed-card,
        .public-results-count,
        .public-ribbon-card,
        .public-stat-card,
        .public-browser-panel,
        .public-results-panel {
            border-radius: 22px;
        }

        .public-inline-facts {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
    }
</style>
<?php $this->endSection(); ?>

<div class="public-shell">
    <header class="public-topbar">
        <div class="container py-3 d-flex flex-wrap align-items-center justify-content-between">
            <a href="<?= url('/') ?>" class="public-brand">
                <i class="fas fa-bezier-curve ml-2"></i><?= e(config('app.name', 'نظام الجداول')) ?>
            </a>
            <div class="public-topbar-links">
                <a href="#public-overview" class="public-nav-link">عن النظام</a>
                <a href="#timetable-browser" class="public-nav-link">استعراض الجداول</a>
                <a href="<?= $ctaUrl ?>" class="public-btn-secondary">
                    <i class="fas <?= $auth ? 'fa-columns' : 'fa-sign-in-alt' ?>"></i>
                    <span><?= e($ctaLabel) ?></span>
                </a>
            </div>
        </div>
    </header>

    <main>
        <section class="public-section public-hero">
            <div class="container">
                <div class="public-hero-grid">
                    <div class="public-glass public-hero-copywrap">
                        <span class="public-badge"><i class="fas fa-satellite-dish"></i>واجهة عامة لعرض الجداول والمعلومات الأكاديمية</span>
                        <h1 class="public-hero-title"><?= e($institutionName) ?></h1>
                        <p class="public-hero-copy">هذه الصفحة صُممت لتكون بوابة مشاهدة عامة: تعطي صورة واضحة عن النظام، وتسمح باستعراض الجداول والبيانات الأساسية بسرعة، مع إبقاء لوحة الإدارة محمية للمستخدمين المصرح لهم فقط.</p>

                        <div class="public-actions">
                            <a href="#timetable-browser" class="public-btn-primary"><i class="fas fa-table-cells-large"></i>استعراض الجداول الآن</a>
                            <a href="<?= $ctaUrl ?>" class="public-btn-secondary"><i class="fas <?= $auth ? 'fa-columns' : 'fa-user-shield' ?>"></i><?= e($ctaLabel) ?></a>
                            <a href="#public-overview" class="public-btn-ghost"><i class="fas fa-compass-drafting"></i>تعرف على النظام</a>
                        </div>

                        <div class="public-inline-facts">
                            <div class="public-inline-fact">
                                <strong><?= (int) ($stats['departments'] ?? 0) ?></strong>
                                <span class="public-note">قسماً مفعلاً</span>
                            </div>
                            <div class="public-inline-fact">
                                <strong><?= (int) ($stats['members'] ?? 0) ?></strong>
                                <span class="public-note">عضو هيئة تدريس</span>
                            </div>
                            <div class="public-inline-fact">
                                <strong><?= (int) ($stats['classrooms'] ?? 0) ?></strong>
                                <span class="public-note">قاعة متاحة</span>
                            </div>
                        </div>
                    </div>

                    <div class="public-panel public-stage">
                        <div class="public-stage-shapes" aria-hidden="true">
                            <span class="public-orb public-orb-a"></span>
                            <span class="public-orb public-orb-b"></span>
                            <span class="public-orb public-orb-c"></span>
                        </div>

                        <div class="public-divider-title">مؤشرات حية من البيانات الحالية</div>
                        <div class="public-spotlights">
                            <?php foreach ($spotlights as $spotlight): ?>
                                <div class="public-spotlight">
                                    <span class="public-spotlight-icon"><i class="fas <?= e($spotlight['icon']) ?>"></i></span>
                                    <div>
                                        <div class="public-spotlight-title"><?= e($spotlight['title']) ?></div>
                                        <div class="public-spotlight-name"><?= e($spotlight['name']) ?></div>
                                    </div>
                                    <div class="public-spotlight-total">
                                        <?= (int) $spotlight['total'] ?><br>
                                        <small><?= e($spotlight['suffix']) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="public-top-ribbon">
                    <div class="public-ribbon-card">
                        <strong>عرض من الجذر مباشرة</strong>
                        <div class="public-note">يمكن فتح الموقع من الرابط الرئيسي للمشروع دون كتابة public في المسار.</div>
                    </div>
                    <div class="public-ribbon-card">
                        <strong>بحث مرن على البيانات</strong>
                        <div class="public-note">فلترة حسب القسم والفرقة، أو التوسع إلى عضو هيئة التدريس والقاعة عند الحاجة.</div>
                    </div>
                    <div class="public-ribbon-card">
                        <strong>واجهة عامة منفصلة عن الإدارة</strong>
                        <div class="public-note">الزائر يرى المعلومات والاستعراض فقط، بينما الإدارة تبقى في مسار مستقل ومحمٍ.</div>
                    </div>
                </div>
            </div>
        </section>

        <section id="public-overview" class="public-section public-overview">
            <div class="container">
                <div class="public-overview-grid">
                    <div class="public-overview-lead">
                        <div>
                            <div class="public-divider-title">صورة عامة عن النظام</div>
                            <h2 class="public-overview-title mb-2">نظام موحد لإدارة الجداول الأكاديمية وعرضها</h2>
                            <p class="public-section-intro mb-0">الواجهة العامة لا تكتفي بعرض أرقام مختصرة، بل تعطي القارئ صورة سريعة عن بنية النظام، وتربط هذه الصورة مباشرة بإمكانية استعراض الجداول الفعلية من نفس الصفحة.</p>
                        </div>

                        <div class="public-mosaic">
                            <div class="public-stat-card">
                                <div class="public-stat-label">الأقسام</div>
                                <div class="public-stat-value"><?= (int) ($stats['departments'] ?? 0) ?></div>
                                <div class="public-note">كيانات أكاديمية مفعلة داخل النظام.</div>
                            </div>
                            <div class="public-stat-card">
                                <div class="public-stat-label">الفرق</div>
                                <div class="public-stat-value"><?= (int) ($stats['levels'] ?? 0) ?></div>
                                <div class="public-note">مستويات دراسية قابلة للفهرسة والعرض.</div>
                            </div>
                            <div class="public-stat-card">
                                <div class="public-stat-label">المقررات</div>
                                <div class="public-stat-value"><?= (int) ($stats['subjects'] ?? 0) ?></div>
                                <div class="public-note">مقررات فعالة مرتبطة بالهيكل الأكاديمي.</div>
                            </div>
                            <div class="public-stat-card">
                                <div class="public-stat-label">عناصر الجدول</div>
                                <div class="public-stat-value"><?= (int) ($stats['timetable'] ?? 0) ?></div>
                                <div class="public-note">مدخلات مجدولة قابلة للاستعراض العام.</div>
                            </div>
                        </div>
                    </div>

                    <div class="public-overview-stack">
                        <?php foreach ($overviewCards as $card): ?>
                            <article class="public-overview-card">
                                <span class="public-overview-icon"><i class="fas <?= e($card['icon']) ?>"></i></span>
                                <h3 class="mb-2"><?= e($card['title']) ?></h3>
                                <p class="mb-0"><?= e($card['text']) ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <section class="public-section public-workflow">
            <div class="container">
                <div class="public-divider-title">كيف تستخدم هذه الواجهة</div>
                <div class="public-workflow-grid">
                    <?php foreach ($workflowSteps as $step): ?>
                        <article class="public-step-card">
                            <span class="public-step-number"><?= e($step['number']) ?></span>
                            <h3 class="public-step-title mb-2"><?= e($step['title']) ?></h3>
                            <p class="mb-0"><?= e($step['text']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section id="timetable-browser" class="public-section public-browser">
            <div class="container">
                <div class="public-browser-wrap">
                    <aside class="public-browser-panel">
                        <div class="public-divider-title">مركز الاستعراض</div>
                        <h2 class="public-browser-title mb-2">فلترة عامة للجداول</h2>
                        <p class="public-support-copy mb-0">يمكنك الآن الجمع بين أكثر من معيار في نفس البحث. سيظهر الجدول الأسبوعي الكامل عندما تختار القسم والفرقة معاً، ويمكن إضافة عضو هيئة التدريس أو القاعة لتضييق النتائج.</p>

                        <?php if ($setupRequired): ?>
                            <div class="alert alert-warning mt-4 mb-0">
                                لا يمكن تحميل بيانات الصفحة حالياً. تأكد من إعداد قاعدة البيانات أولاً.
                                <a href="<?= url('/install.php') ?>" class="alert-link mr-1">فتح معالج التثبيت</a>
                            </div>
                        <?php else: ?>
                            <form method="GET" action="<?= url('/') ?>" class="mt-4">
                                <div class="public-filter-grid">
                                    <div class="public-filter-field">
                                        <label>القسم</label>
                                        <select name="department_id" class="form-control">
                                            <option value="">اختر القسم</option>
                                            <?php foreach ($departments as $department): ?>
                                                <option value="<?= (int) $department['department_id'] ?>" <?= ($filters['department_id'] ?? null) === (int) $department['department_id'] ? 'selected' : '' ?>><?= e($department['department_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="public-filter-field">
                                        <label>الفرقة</label>
                                        <select name="level_id" class="form-control">
                                            <option value="">اختر الفرقة</option>
                                            <?php foreach ($levels as $level): ?>
                                                <option value="<?= (int) $level['level_id'] ?>" <?= ($filters['level_id'] ?? null) === (int) $level['level_id'] ? 'selected' : '' ?>><?= e($level['level_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="public-filter-field">
                                        <label>عضو هيئة التدريس</label>
                                        <select name="member_id" class="form-control">
                                            <option value="">اختر العضو</option>
                                            <?php foreach ($members as $member): ?>
                                                <option value="<?= (int) $member['member_id'] ?>" <?= ($filters['member_id'] ?? null) === (int) $member['member_id'] ? 'selected' : '' ?>><?= e($member['member_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="public-filter-field">
                                        <label>القاعة</label>
                                        <select name="classroom_id" class="form-control">
                                            <option value="">اختر القاعة</option>
                                            <?php foreach ($classrooms as $classroom): ?>
                                                <option value="<?= (int) $classroom['classroom_id'] ?>" <?= ($filters['classroom_id'] ?? null) === (int) $classroom['classroom_id'] ? 'selected' : '' ?>><?= e($classroom['classroom_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="public-filter-actions">
                                    <button type="submit" class="btn btn-dark"><i class="fas fa-search ml-1"></i>عرض النتائج</button>
                                    <a href="<?= url('/') ?>" class="btn btn-outline-secondary">إعادة تعيين</a>
                                </div>
                            </form>

                            <?php if (!empty($selectedFilters)): ?>
                                <div class="public-selected-filters">
                                    <?php foreach ($selectedFilters as $selectedFilter): ?>
                                        <span class="public-filter-chip"><i class="fas fa-filter"></i><?= e($selectedFilter['label']) ?>: <?= e($selectedFilter['value']) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="public-summary-stack">
                                <div class="public-summary-card">
                                    <span class="public-summary-icon"><i class="fas fa-wave-square"></i></span>
                                    <div>
                                        <div class="public-summary-title">الحالة الحالية</div>
                                        <div class="public-summary-value"><?= $hasFilter ? 'بحث مخصص' : 'عرض عام' ?></div>
                                        <div class="public-summary-copy"><?= $hasFilter ? 'تم تطبيق الفلاتر المختارة على نتائج الجدول.' : 'تظهر أحدث البيانات المجدولة عند عدم تحديد فلاتر.' ?></div>
                                    </div>
                                </div>

                                <div class="public-summary-card">
                                    <span class="public-summary-icon"><i class="fas fa-list-check"></i></span>
                                    <div>
                                        <div class="public-summary-title">عدد العناصر المعروضة</div>
                                        <div class="public-summary-value"><?= (int) $resultsCount ?></div>
                                        <div class="public-summary-copy"><?= $hasFilter ? 'نتيجة ضمن نطاق الفلاتر المختارة.' : 'عنصر حديث يظهر في الاستعراض العام.' ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </aside>

                    <div class="public-results-panel">
                        <div class="public-results-head">
                            <div>
                                <div class="public-divider-title">نتائج العرض</div>
                                <h2 class="public-browser-title mb-2"><?= $hasFilter ? 'نتائج الفلترة العامة' : 'آخر ما أضيف إلى الجداول' ?></h2>
                                <p class="public-table-caption mb-0"><?= $hasFilter ? 'النتائج التالية مبنية على الفلاتر الحالية، ويظهر المخطط الأسبوعي الكامل عندما يكون القسم والفرقة محددين معاً.' : 'في الوضع العام نعرض أحدث العناصر المجدولة حتى يتعرّف الزائر سريعاً على محتوى النظام.' ?></p>
                            </div>
                            <div class="public-results-count">
                                <strong><?= (int) $resultsCount ?></strong>
                                <span class="public-note"><?= $hasFilter ? 'نتيجة مطابقة' : 'عنصر حديث' ?></span>
                            </div>
                        </div>

                        <div class="public-support-banner">
                            <div class="public-support-card">
                                <strong>متى يظهر الجدول الأسبوعي؟</strong>
                                <div class="public-note">عند اختيار القسم والفرقة معاً، حتى تتوزع الفترات على الأيام ضمن نفس المخطط.</div>
                            </div>
                            <div class="public-support-card">
                                <strong>ما الذي تضيفه الفلاتر الجديدة؟</strong>
                                <div class="public-note">يمكنك الآن تضييق النتائج حسب عضو هيئة التدريس أو القاعة حتى لو أردت عرضاً نصياً فقط.</div>
                            </div>
                        </div>

                        <?php if ($setupRequired): ?>
                            <div class="public-empty-state">
                                <h3 class="mb-2">البيانات غير جاهزة بعد</h3>
                                <p class="mb-0">بعد إتمام التثبيت وضبط قاعدة البيانات ستظهر الإحصاءات والجداول العامة هنا تلقائياً.</p>
                            </div>
                        <?php else: ?>
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
                                                                        <div class="public-entry-meta"><?= e($cellEntry['member_name'] ?? '') ?></div>
                                                                        <div class="public-entry-meta"><?= e($cellEntry['classroom_name'] ?? '') ?> | <?= e($cellEntry['section_name'] ?? 'محاضرة عامة') ?></div>
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
                                    <?php if (empty($pivotData) && (!empty($filters['department_id']) || !empty($filters['level_id']))): ?>
                                        <div class="alert alert-light border mb-3">للحصول على مخطط أسبوعي كامل اختر القسم والفرقة معاً. الفلاتر الحالية تعرض النتائج النصية التفصيلية فقط.</div>
                                    <?php endif; ?>
                                    <div class="public-table-wrap table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>المقرر</th>
                                                    <th>عضو هيئة التدريس</th>
                                                    <th>الشعبة</th>
                                                    <th>القسم</th>
                                                    <th>الفرقة</th>
                                                    <th>اليوم</th>
                                                    <th>الفترة</th>
                                                    <th>القاعة</th>
                                                </tr>
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
                                    <div class="public-empty-state">
                                        <h3 class="mb-2">لا توجد نتائج مطابقة حالياً</h3>
                                        <p class="mb-0">جرّب توسيع نطاق البحث أو إزالة بعض الفلاتر للحصول على نتائج أوسع.</p>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if (!empty($recentEntries)): ?>
                                    <div class="public-feed-grid">
                                        <?php foreach ($recentEntries as $entry): ?>
                                            <article class="public-feed-card">
                                                <h3 class="mb-2"><?= e($entry['subject_name'] ?? '') ?></h3>
                                                <p class="mb-0 public-note"><?= e($entry['member_name'] ?? '') ?> · <?= e($entry['classroom_name'] ?? '') ?></p>
                                                <div class="public-feed-meta">
                                                    <span class="public-feed-pill"><i class="fas fa-building"></i><?= e($entry['department_name'] ?? '') ?></span>
                                                    <span class="public-feed-pill"><i class="fas fa-layer-group"></i><?= e($entry['level_name'] ?? '') ?></span>
                                                    <span class="public-feed-pill"><i class="fas fa-calendar-day"></i><?= e($entry['day'] ?? '') ?></span>
                                                    <span class="public-feed-pill"><i class="fas fa-clock"></i><?= e($entry['start_time'] ?? '') ?> - <?= e($entry['end_time'] ?? '') ?></span>
                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="public-empty-state">
                                        <h3 class="mb-2">لا توجد عناصر جدول معروضة بعد</h3>
                                        <p class="mb-0">بمجرد إدخال البيانات من لوحة التحكم ستظهر أحدث العناصر هنا ليستطيع الزائر استعراضها مباشرة.</p>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="public-section public-footer">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div>© <?= date('Y') ?> <?= e(config('app.name', 'نظام الجداول')) ?></div>
            <div class="mt-2 mt-md-0">واجهة عامة تركز على العرض والاستكشاف، مع تصميم حيوي ومساحة إدارة منفصلة للمستخدمين المخولين.</div>
        </div>
    </footer>
</div>
<?php
$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uriPath = is_string($uriPath) ? $uriPath : '/';

$basePath = trim(app_base_path(), '/');
if ($basePath !== '') {
    $prefix = '/' . $basePath;
    if (str_starts_with($uriPath, $prefix)) {
        $uriPath = substr($uriPath, strlen($prefix));
    }
}

$currentPath = '/' . trim($uriPath, '/');
if ($currentPath === '//') {
    $currentPath = '/';
}

// Strip index.php prefix (used on Nginx without URL rewriting)
if ($currentPath === '/index.php') {
    $currentPath = '/';
} elseif (str_starts_with($currentPath, '/index.php/')) {
    $currentPath = substr($currentPath, strlen('/index.php'));
}

$matchesRoute = static function (string $path) use ($currentPath): bool {
    if ($path === '/') {
        return $currentPath === '/';
    }

    return $currentPath === $path || str_starts_with($currentPath, $path . '/');
};

$matchesAny = static function (array $paths) use ($matchesRoute): bool {
    foreach ($paths as $path) {
        if ($matchesRoute($path)) {
            return true;
        }
    }

    return false;
};

$permissions = [
    'scheduling' => can('scheduling.view'),
    'scheduling_admin' => can('scheduling.admin'),
    'priority' => can('priority.view'),
    'timetable' => can('timetable.view'),
    'members' => can('members.view'),
    'subjects' => can('subjects.view'),
    'member_courses' => can('membercourses.view'),
    'academic_years' => can('academic_years.view'),
    'semesters' => can('semesters.view'),
    'departments' => can('departments.view'),
    'levels' => can('levels.view'),
    'divisions' => can('divisions.view'),
    'sections' => can('sections.view'),
    'classrooms' => can('classrooms.view'),
    'sessions' => can('sessions.view'),
    'notifications' => can('notifications.view'),
    'users' => can('users.view'),
    'audit' => can('audit.view'),
    'settings' => can('settings.view'),
    'backup' => can('backup.view'),
];

// Check scheduling view mode setting
$schedulingViewMode = 'both';
try {
    $svmRow = \Database::getInstance()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'scheduling_view_mode'");
    if ($svmRow)
        $schedulingViewMode = $svmRow['setting_value'];
} catch (\Throwable $e) {
}

$sectionBlueprint = [
    [
        'header' => ' التشغيل',
        'title' => ' ادارة التسكين',
        'icon' => 'fas fa-satellite-dish',
        'items' => array_filter([
            in_array($schedulingViewMode, ['classic', 'both']) ? ['label' => 'التسكين', 'path' => '/scheduling', 'icon' => 'far fa-calendar-check', 'permission' => 'scheduling'] : null,
            in_array($schedulingViewMode, ['grid', 'both']) ? ['label' => 'التسكين الرسومي', 'path' => '/grid-scheduling', 'icon' => 'fas fa-th', 'permission' => 'scheduling'] : null,
            ['label' => 'التسكين الإداري', 'path' => '/admin-scheduling', 'icon' => 'fas fa-user-shield', 'permission' => 'scheduling_admin'],
            ['label' => 'إدارة الأولوية', 'path' => '/priority', 'icon' => 'fas fa-sort-amount-up', 'permission' => 'priority'],
            ['label' => 'الجدول الدراسي', 'path' => '/timetable', 'icon' => 'far fa-clock', 'permission' => 'timetable'],
            ['label' => 'التقارير والإحصائيات', 'path' => '/reports', 'icon' => 'fas fa-chart-bar', 'permission' => null],
        ]),
    ],
    [
        'header' => 'التدريس',
        'title' => 'الهيئة والمقررات',
        'icon' => 'fas fa-chalkboard-teacher',
        'items' => [
            ['label' => 'أعضاء هيئة التدريس', 'path' => '/members', 'icon' => 'far fa-user', 'permission' => 'members'],
            ['label' => 'المقررات', 'path' => '/subjects', 'icon' => 'far fa-book-open', 'permission' => 'subjects'],
            ['label' => 'تكليفات التدريس', 'path' => '/member-courses', 'icon' => 'far fa-list-alt', 'permission' => 'member_courses'],
        ],
    ],
    [
        'header' => 'البنية الأكاديمية',
        'title' => 'الهيكل والتنظيم',
        'icon' => 'fas fa-project-diagram',
        'items' => [
            ['label' => 'السنوات الأكاديمية', 'path' => '/academic-years', 'icon' => 'far fa-calendar', 'permission' => 'academic_years'],
            ['label' => 'الفصول الدراسية', 'path' => '/semesters', 'icon' => 'far fa-flag', 'permission' => 'semesters'],
            ['label' => 'الأقسام', 'path' => '/departments', 'icon' => 'far fa-building', 'permission' => 'departments'],
            ['label' => 'الفرق', 'path' => '/levels', 'icon' => 'fas fa-layer-group', 'permission' => 'levels'],
            ['label' => 'الشعب', 'path' => '/divisions', 'icon' => 'far fa-object-group', 'permission' => 'divisions'],
            ['label' => 'السكاشن', 'path' => '/sections', 'icon' => 'far fa-clone', 'permission' => 'sections'],
        ],
    ],
    [
        'header' => 'الموارد',
        'title' => 'القاعات والفترات',
        'icon' => 'fas fa-boxes',
        'items' => [
            ['label' => 'القاعات', 'path' => '/classrooms', 'icon' => 'far fa-square', 'permission' => 'classrooms'],
            ['label' => 'الفترات الزمنية', 'path' => '/sessions', 'icon' => 'far fa-hourglass', 'permission' => 'sessions'],
        ],
    ],
    [
        'header' => 'المتابعة',
        'title' => 'الإدارة والتحكم',
        'icon' => 'fas fa-tools',
        'items' => [
            ['label' => 'الإشعارات', 'path' => '/notifications', 'icon' => 'far fa-bell', 'permission' => 'notifications'],
            ['label' => 'المستخدمون', 'path' => '/users', 'icon' => 'fas fa-users', 'permission' => 'users'],
            ['label' => 'سجل المراجعة', 'path' => '/audit-logs', 'icon' => 'far fa-clipboard', 'permission' => 'audit'],
            ['label' => 'الإعدادات', 'path' => '/settings', 'icon' => 'fas fa-sliders-h', 'permission' => 'settings'],
            ['label' => 'النسخ الاحتياطي', 'path' => '/backups', 'icon' => 'fas fa-database', 'permission' => 'backup'],
        ],
    ],
];

$visibleSections = [];
foreach ($sectionBlueprint as $section) {
    $visibleItems = [];

    foreach ($section['items'] as $item) {
        $permissionKey = $item['permission'] ?? null;
        if ($permissionKey && empty($permissions[$permissionKey])) {
            continue;
        }
        $visibleItems[] = $item;
    }

    if (!empty($visibleItems)) {
        $section['items'] = $visibleItems;
        $visibleSections[] = $section;
    }
}

$visibleSectionPaths = [];
foreach ($visibleSections as $section) {
    foreach ($section['items'] as $item) {
        $visibleSectionPaths[$item['path']] = true;
    }
}

$accountLinks = [
    ['label' => 'الملف الشخصي', 'path' => '/profile', 'icon' => 'fas fa-id-card'],
    ['label' => 'الإعدادات', 'path' => '/settings', 'icon' => 'fas fa-cog', 'permission' => 'settings'],
    ['label' => 'تسجيل الخروج', 'path' => '/logout', 'icon' => 'fas fa-sign-out-alt', 'class' => 'text-danger'],
];

$visibleAccountLinks = [];
foreach ($accountLinks as $link) {
    $permissionKey = $link['permission'] ?? null;
    if ($permissionKey && empty($permissions[$permissionKey])) {
        continue;
    }

    if (isset($visibleSectionPaths[$link['path']])) {
        continue;
    }

    $visibleAccountLinks[] = $link;
}
?>
<aside class="main-sidebar sidebar-rebuild-v3 elevation-0">
    <a href="<?= url('/dashboard') ?>" class="brand-link sidebar-rebuild-brand">
        <span class="sidebar-rebuild-logo" aria-hidden="true">
            <i class="fas fa-bezier-curve"></i>
        </span>
        <span class="sidebar-rebuild-brand-text">
            <strong><?= e(config('app.name', 'نظام الجداول')) ?></strong>
        </span>
    </a>

    <div class="sidebar sidebar-rebuild-scroll">
        <!-- User Profile -->
        <div class="sidebar-rebuild-profile">
            <span class="sidebar-rebuild-avatar" aria-hidden="true">
                <?= mb_substr($auth['username'] ?? 'U', 0, 1, 'UTF-8') ?>
            </span>
            <div class="sidebar-rebuild-user">
                <a href="<?= url('/profile') ?>"><?= e($auth['username'] ?? 'المستخدم') ?></a>
                <span><?php
                    $roleLabels = ['admin' => 'مدير النظام', 'dept_head' => 'رئيس قسم', 'faculty' => 'عضو هيئة تدريس', 'viewer' => 'مشاهد'];
                    echo e($roleLabels[$auth['role'] ?? ''] ?? ($auth['role'] ?? ''));
                ?></span>
            </div>
        </div>

        <nav class="sidebar-rebuild-nav mt-2">
            <ul class="nav nav-sidebar nav-child-indent flex-column" data-widget="treeview" data-accordion="false"
                role="menu">
                <li class="nav-item">
                    <a href="<?= url('/dashboard') ?>"
                        class="nav-link sidebar-rebuild-link <?= $matchesRoute('/dashboard') ? 'active' : '' ?>">
                        <span class="sidebar-rebuild-icon"><i class="fas fa-th-large"></i></span>
                        <p>لوحة التحكم</p>
                    </a>
                </li>

                <?php foreach ($visibleSections as $section): ?>
                    <?php
                    $sectionPaths = array_map(static fn(array $item): string => $item['path'], $section['items']);
                    $sectionOpen = $matchesAny($sectionPaths);
                    ?>
                    <li class="nav-header sidebar-rebuild-header"><?= e($section['header']) ?></li>
                    <li class="nav-item has-treeview <?= $sectionOpen ? 'menu-open' : '' ?>">
                        <a href="#"
                            class="nav-link sidebar-rebuild-link sidebar-rebuild-parent <?= $sectionOpen ? 'active' : '' ?>">
                            <span class="sidebar-rebuild-icon"><i class="<?= e($section['icon']) ?>"></i></span>
                            <p>
                                <?= e($section['title']) ?>
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview sidebar-rebuild-tree">
                            <?php foreach ($section['items'] as $item): ?>
                                <li class="nav-item">
                                    <a href="<?= url($item['path']) ?>"
                                        class="nav-link sidebar-rebuild-link <?= $matchesRoute($item['path']) ? 'active' : '' ?>">
                                        <span class="sidebar-rebuild-icon"><i class="<?= e($item['icon']) ?>"></i></span>
                                        <p><?= e($item['label']) ?></p>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endforeach; ?>

                <?php if (!empty($visibleAccountLinks)): ?>
                    <li class="nav-header sidebar-rebuild-header">الحساب</li>
                    <?php foreach ($visibleAccountLinks as $link): ?>
                        <?php $linkClass = $link['class'] ?? ''; ?>
                        <li class="nav-item">
                            <a href="<?= url($link['path']) ?>"
                                class="nav-link sidebar-rebuild-link <?= $matchesRoute($link['path']) ? 'active' : '' ?> <?= e($linkClass) ?>">
                                <span class="sidebar-rebuild-icon"><i class="<?= e($link['icon']) ?>"></i></span>
                                <p><?= e($link['label']) ?></p>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (empty($visibleSections) && empty($visibleAccountLinks)): ?>
                    <li class="nav-item">
                        <span class="nav-link sidebar-rebuild-empty">
                            <span class="sidebar-rebuild-icon"><i class="fas fa-info-circle"></i></span>
                            <p>لا توجد صفحات متاحة حالياً لهذا الحساب</p>
                        </span>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</aside>
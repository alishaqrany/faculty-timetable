<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$basePath = config('app.base_path', '');
$current = str_replace($basePath, '', $currentPath);
$current = '/' . trim($current, '/');

if (!function_exists('isActive')) {
    function isActive(string $path, string $current): string {
        if ($path === '/') return ($current === '/' || $current === '') ? 'active' : '';
        return str_starts_with($current, $path) ? 'active' : '';
    }
}

if (!function_exists('menuOpenAny')) {
    function menuOpenAny(array $paths, string $current): string {
        foreach ($paths as $path) {
            if (str_starts_with($current, $path)) {
                return 'menu-open';
            }
        }
        return '';
    }
}

if (!function_exists('activeAny')) {
    function activeAny(array $paths, string $current): string {
        foreach ($paths as $path) {
            if (str_starts_with($current, $path)) {
                return 'active';
            }
        }
        return '';
    }
}

$dataPaths = ['/departments', '/levels', '/classrooms', '/sessions', '/academic-years', '/semesters'];
$academicPaths = ['/members', '/subjects', '/divisions', '/sections', '/member-courses'];
$adminPaths = ['/users', '/audit-logs', '/settings', '/notifications', '/backups'];
?>
<!-- Main Sidebar -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand -->
    <a href="<?= url('/') ?>" class="brand-link">
        <i class="fas fa-calendar-alt brand-image elevation-3 mt-1 mr-2" style="font-size: 1.6rem; color: #fff;"></i>
        <span class="brand-text font-weight-bold"><?= e(config('app.name', 'نظام الجداول')) ?></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- User Panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <i class="fas fa-user-circle fa-2x text-light"></i>
            </div>
            <div class="info">
                <a href="<?= url('/profile') ?>" class="d-block"><?= e($auth['username'] ?? '') ?></a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">

                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="<?= url('/') ?>" class="nav-link <?= isActive('/', $current) ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>لوحة التحكم</p>
                    </a>
                </li>

                <!-- Data Management -->
                <li class="nav-item has-treeview <?= menuOpenAny($dataPaths, $current) ?>">
                    <a href="#" class="nav-link <?= activeAny($dataPaths, $current) ?>">
                        <i class="nav-icon fas fa-database"></i>
                        <p>البيانات الأساسية <i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if (can('academic_years.view')): ?>
                        <li class="nav-item">
                            <a href="<?= url('/academic-years') ?>" class="nav-link <?= isActive('/academic-years', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>السنوات الأكاديمية</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (can('semesters.view')): ?>
                        <li class="nav-item">
                            <a href="<?= url('/semesters') ?>" class="nav-link <?= isActive('/semesters', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>الفصول الدراسية</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (can('departments.view')): ?>
                        <li class="nav-item">
                            <a href="<?= url('/departments') ?>" class="nav-link <?= isActive('/departments', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>الأقسام</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (can('levels.view')): ?>
                        <li class="nav-item">
                            <a href="<?= url('/levels') ?>" class="nav-link <?= isActive('/levels', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>المستويات</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (can('classrooms.view')): ?>
                        <li class="nav-item">
                            <a href="<?= url('/classrooms') ?>" class="nav-link <?= isActive('/classrooms', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>القاعات</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (can('sessions.view')): ?>
                        <li class="nav-item">
                            <a href="<?= url('/sessions') ?>" class="nav-link <?= isActive('/sessions', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>الفترات الزمنية</p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>

                <!-- Academic -->
                <li class="nav-item has-treeview <?= menuOpenAny($academicPaths, $current) ?>">
                    <a href="#" class="nav-link <?= activeAny($academicPaths, $current) ?>">
                        <i class="nav-icon fas fa-graduation-cap"></i>
                        <p>الشؤون الأكاديمية <i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if (can('members.view')): ?>
                        <li class="nav-item">
                            <a href="<?= url('/members') ?>" class="nav-link <?= isActive('/members', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>أعضاء هيئة التدريس</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (can('subjects.view')): ?>
                        <li class="nav-item">
                            <a href="<?= url('/subjects') ?>" class="nav-link <?= isActive('/subjects', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>المقررات</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (can('divisions.view')): ?>
                        <li class="nav-item">
                            <a href="<?= url('/divisions') ?>" class="nav-link <?= isActive('/divisions', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>الشُعب</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (can('sections.view')): ?>
                        <li class="nav-item">
                            <a href="<?= url('/sections') ?>" class="nav-link <?= isActive('/sections', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>السكاشن</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (can('membercourses.view')): ?>
                        <li class="nav-item">
                            <a href="<?= url('/member-courses') ?>" class="nav-link <?= isActive('/member-courses', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>تكليفات التدريس</p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>

                <!-- Scheduling & Timetable -->
                <?php if (can('scheduling.view')): ?>
                <li class="nav-item">
                    <a href="<?= url('/scheduling') ?>" class="nav-link <?= isActive('/scheduling', $current) ?>">
                        <i class="nav-icon fas fa-calendar-plus"></i>
                        <p>الجدولة</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if (can('timetable.view')): ?>
                <li class="nav-item">
                    <a href="<?= url('/timetable') ?>" class="nav-link <?= isActive('/timetable', $current) ?>">
                        <i class="nav-icon fas fa-table"></i>
                        <p>الجدول الدراسي</p>
                    </a>
                </li>
                <?php endif; ?>

                <!-- Administration -->
                <li class="nav-item has-treeview <?= menuOpenAny($adminPaths, $current) ?>">
                    <a href="#" class="nav-link <?= activeAny($adminPaths, $current) ?>">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>الإدارة <i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if (can('users.view')): ?>
                        <li class="nav-item">
                            <a href="<?= url('/users') ?>" class="nav-link <?= isActive('/users', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>المستخدمون</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (can('notifications.view')): ?>
                        <li class="nav-item">
                            <a href="<?= url('/notifications') ?>" class="nav-link <?= isActive('/notifications', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>الإشعارات</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (can('audit.view')): ?>
                        <li class="nav-item">
                            <a href="<?= url('/audit-logs') ?>" class="nav-link <?= isActive('/audit-logs', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>سجل المراجعة</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (can('settings.view')): ?>
                        <li class="nav-item">
                            <a href="<?= url('/settings') ?>" class="nav-link <?= isActive('/settings', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>الإعدادات</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (can('backup.view')): ?>
                        <li class="nav-item">
                            <a href="<?= url('/backups') ?>" class="nav-link <?= isActive('/backups', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>النسخ الاحتياطي</p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>

            </ul>
        </nav>
    </div>
</aside>

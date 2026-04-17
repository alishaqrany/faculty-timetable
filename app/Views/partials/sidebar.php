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

$operationsPaths = ['/scheduling', '/timetable'];
$teachingPaths = ['/members', '/subjects', '/member-courses'];
$structurePaths = ['/academic-years', '/semesters', '/departments', '/levels', '/divisions', '/sections'];
$resourcesPaths = ['/classrooms', '/sessions'];
$systemPaths = ['/users', '/notifications', '/audit-logs', '/settings', '/backups', '/profile'];

$canScheduling = can('scheduling.view');
$canTimetable = can('timetable.view');

$canMembers = can('members.view');
$canSubjects = can('subjects.view');
$canMemberCourses = can('membercourses.view');

$canAcademicYears = can('academic_years.view');
$canSemesters = can('semesters.view');
$canDepartments = can('departments.view');
$canLevels = can('levels.view');
$canDivisions = can('divisions.view');
$canSections = can('sections.view');

$canClassrooms = can('classrooms.view');
$canSessions = can('sessions.view');

$canUsers = can('users.view');
$canNotifications = can('notifications.view');
$canAudit = can('audit.view');
$canSettings = can('settings.view');
$canBackup = can('backup.view');

$showOperationsMenu = $canScheduling || $canTimetable;
$showTeachingMenu = $canMembers || $canSubjects || $canMemberCourses;
$showStructureMenu = $canAcademicYears || $canSemesters || $canDepartments || $canLevels || $canDivisions || $canSections;
$showResourcesMenu = $canClassrooms || $canSessions;
$showSystemMenu = $canUsers || $canNotifications || $canAudit || $canSettings || $canBackup;
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

                <li class="nav-header">مركز التشغيل</li>

                <li class="nav-item">
                    <a href="<?= url('/') ?>" class="nav-link <?= isActive('/', $current) ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>لوحة التحكم</p>
                    </a>
                </li>

                <?php if ($showOperationsMenu): ?>
                <li class="nav-item has-treeview <?= menuOpenAny($operationsPaths, $current) ?>">
                    <a href="#" class="nav-link <?= activeAny($operationsPaths, $current) ?>">
                        <i class="nav-icon fas fa-compass"></i>
                        <p>عمليات الجدولة <i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if ($canScheduling): ?>
                        <li class="nav-item">
                            <a href="<?= url('/scheduling') ?>" class="nav-link <?= isActive('/scheduling', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>الجدولة</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($canTimetable): ?>
                        <li class="nav-item">
                            <a href="<?= url('/timetable') ?>" class="nav-link <?= isActive('/timetable', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>الجدول الدراسي</p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <?php if ($showTeachingMenu): ?>
                <li class="nav-header">التدريس والمقررات</li>
                <li class="nav-item has-treeview <?= menuOpenAny($teachingPaths, $current) ?>">
                    <a href="#" class="nav-link <?= activeAny($teachingPaths, $current) ?>">
                        <i class="nav-icon fas fa-chalkboard-teacher"></i>
                        <p>إدارة التدريس <i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if ($canMembers): ?>
                        <li class="nav-item">
                            <a href="<?= url('/members') ?>" class="nav-link <?= isActive('/members', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>أعضاء هيئة التدريس</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($canSubjects): ?>
                        <li class="nav-item">
                            <a href="<?= url('/subjects') ?>" class="nav-link <?= isActive('/subjects', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>المقررات</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($canMemberCourses): ?>
                        <li class="nav-item">
                            <a href="<?= url('/member-courses') ?>" class="nav-link <?= isActive('/member-courses', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>تكليفات التدريس</p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <?php if ($showStructureMenu): ?>
                <li class="nav-header">الهيكل الأكاديمي</li>
                <li class="nav-item has-treeview <?= menuOpenAny($structurePaths, $current) ?>">
                    <a href="#" class="nav-link <?= activeAny($structurePaths, $current) ?>">
                        <i class="nav-icon fas fa-sitemap"></i>
                        <p>المكونات الأكاديمية <i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if ($canAcademicYears): ?>
                        <li class="nav-item">
                            <a href="<?= url('/academic-years') ?>" class="nav-link <?= isActive('/academic-years', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>السنوات الأكاديمية</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($canSemesters): ?>
                        <li class="nav-item">
                            <a href="<?= url('/semesters') ?>" class="nav-link <?= isActive('/semesters', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>الفصول الدراسية</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($canDepartments): ?>
                        <li class="nav-item">
                            <a href="<?= url('/departments') ?>" class="nav-link <?= isActive('/departments', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>الأقسام</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($canLevels): ?>
                        <li class="nav-item">
                            <a href="<?= url('/levels') ?>" class="nav-link <?= isActive('/levels', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>الفرق</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($canDivisions): ?>
                        <li class="nav-item">
                            <a href="<?= url('/divisions') ?>" class="nav-link <?= isActive('/divisions', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>الشُعب</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($canSections): ?>
                        <li class="nav-item">
                            <a href="<?= url('/sections') ?>" class="nav-link <?= isActive('/sections', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>السكاشن</p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <?php if ($showResourcesMenu): ?>
                <li class="nav-header">الموارد</li>
                <li class="nav-item has-treeview <?= menuOpenAny($resourcesPaths, $current) ?>">
                    <a href="#" class="nav-link <?= activeAny($resourcesPaths, $current) ?>">
                        <i class="nav-icon fas fa-boxes"></i>
                        <p>القاعات والفترات <i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if ($canClassrooms): ?>
                        <li class="nav-item">
                            <a href="<?= url('/classrooms') ?>" class="nav-link <?= isActive('/classrooms', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>القاعات</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($canSessions): ?>
                        <li class="nav-item">
                            <a href="<?= url('/sessions') ?>" class="nav-link <?= isActive('/sessions', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>الفترات الزمنية</p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <?php if ($showSystemMenu): ?>
                <li class="nav-header">إدارة النظام</li>
                <li class="nav-item has-treeview <?= menuOpenAny($systemPaths, $current) ?>">
                    <a href="#" class="nav-link <?= activeAny($systemPaths, $current) ?>">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>الضبط والمتابعة <i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if ($canNotifications): ?>
                        <li class="nav-item">
                            <a href="<?= url('/notifications') ?>" class="nav-link <?= isActive('/notifications', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>الإشعارات</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($canUsers): ?>
                        <li class="nav-item">
                            <a href="<?= url('/users') ?>" class="nav-link <?= isActive('/users', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>المستخدمون</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($canAudit): ?>
                        <li class="nav-item">
                            <a href="<?= url('/audit-logs') ?>" class="nav-link <?= isActive('/audit-logs', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>سجل المراجعة</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($canSettings): ?>
                        <li class="nav-item">
                            <a href="<?= url('/settings') ?>" class="nav-link <?= isActive('/settings', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>الإعدادات</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($canBackup): ?>
                        <li class="nav-item">
                            <a href="<?= url('/backups') ?>" class="nav-link <?= isActive('/backups', $current) ?>">
                                <i class="far fa-circle nav-icon"></i> <p>النسخ الاحتياطي</p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <li class="nav-header">الحساب</li>
                <li class="nav-item">
                    <a href="<?= url('/profile') ?>" class="nav-link <?= isActive('/profile', $current) ?>">
                        <i class="nav-icon fas fa-id-badge"></i>
                        <p>الملف الشخصي</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>

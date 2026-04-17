<!-- Main Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>

    <ul class="navbar-nav mr-auto">
        <!-- Notifications -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-bell"></i>
                <?php if (($notifications_count ?? 0) > 0): ?>
                    <span class="badge badge-warning navbar-badge"><?= $notifications_count ?></span>
                <?php endif; ?>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-left">
                <span class="dropdown-item dropdown-header">الإشعارات</span>
                <?php if (($notifications_count ?? 0) > 0): ?>
                    <a href="<?= url('/notifications') ?>" class="dropdown-item text-center">
                        <small>عرض <?= $notifications_count ?> إشعار</small>
                    </a>
                <?php else: ?>
                    <a href="#" class="dropdown-item text-center text-muted">
                        <small>لا توجد إشعارات جديدة</small>
                    </a>
                <?php endif; ?>
            </div>
        </li>

        <!-- User Menu -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="fas fa-user-circle"></i>
                <span class="mx-1 d-none d-sm-inline"><?= e($auth['username'] ?? 'المستخدم') ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-left">
                <a href="<?= url('/profile') ?>" class="dropdown-item">
                    <i class="fas fa-user ml-2"></i> الملف الشخصي
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="<?= url('/logout') ?>" class="px-0 m-0">
                    <?= csrf_field() ?>
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt ml-2"></i> تسجيل الخروج
                    </button>
                </form>
            </div>
        </li>
    </ul>
</nav>

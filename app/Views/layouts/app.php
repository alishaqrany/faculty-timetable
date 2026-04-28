<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($__page_title ?? 'لوحة التحكم') ?> | <?= e(config('app.name', 'نظام الجداول')) ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Changa:wght@500;700&family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Bootstrap RTL + AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
    <link rel="icon" type="image/svg+xml" href="<?= asset('assets/favicon.svg') ?>">

    <style>
        /* Minimal inline overrides — main styles in custom.css */
    </style>
    <link rel="stylesheet" href="<?= asset('assets/css/custom.css') ?>">
    <?php $this->yield('styles'); ?>
</head>
<body class="hold-transition layout-fixed layout-navbar-fixed app-modern-theme">
<div class="wrapper">

    <!-- Navbar -->
    <?php $this->include('partials.navbar'); ?>

    <!-- Sidebar -->
    <?php $this->include('partials.sidebar'); ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header (Breadcrumb) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 align-items-center">
                    <div class="col-sm-6 mb-2 mb-sm-0">
                        <h1 class="m-0"><?= e($__page_title ?? '') ?></h1>
                    </div>
                    <div class="col-sm-6 d-flex justify-content-sm-end justify-content-start">
                        <?php if (!empty($__breadcrumb)): ?>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>"><i class="fas fa-home"></i></a></li>
                            <?php foreach ($__breadcrumb as $crumb): ?>
                                <?php if (!empty($crumb['url'])): ?>
                                    <li class="breadcrumb-item"><a href="<?= url($crumb['url']) ?>"><?= e($crumb['label']) ?></a></li>
                                <?php else: ?>
                                    <li class="breadcrumb-item active"><?= e($crumb['label']) ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ol>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Flash Messages -->
                <?php $this->include('partials.flash'); ?>

                <!-- Page Content -->
                <?php $this->yield('content'); ?>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="main-footer app-footer">
        <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between">
            <div>
                <strong>&copy; <?= date('Y') ?> <a href="<?= url('/dashboard') ?>"><?= e(config('app.name', 'نظام الجداول')) ?></a></strong>
                <span class="d-block d-sm-inline text-muted">جميع الحقوق محفوظة.</span>
            </div>
            <div class="text-muted small mt-2 mt-sm-0">
                لوحة إدارة الجداول والجدولة الأكاديمية
            </div>
        </div>
    </footer>

</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<!-- Toastr -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- App JS -->
<script src="<?= asset('assets/js/app.js') ?>"></script>
<?php $this->yield('scripts'); ?>
</body>
</html>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($__page_title ?? 'لوحة التحكم') ?> | <?= e(config('app.name', 'نظام الجداول')) ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- AdminLTE + Bootstrap RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.rtl.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">

    <style>
        body, .main-sidebar, .content-wrapper, .main-header, .main-footer {
            font-family: 'Tajawal', sans-serif !important;
        }
        .content-wrapper { margin-right: 250px; margin-left: 0 !important; }
        .main-sidebar { right: 0; left: auto; }
        .main-header { margin-right: 250px; margin-left: 0 !important; }
        .main-footer { margin-right: 250px; margin-left: 0 !important; }
        @media (max-width: 991.98px) {
            .content-wrapper, .main-header, .main-footer { margin-right: 0 !important; }
        }
        .sidebar-collapse .content-wrapper,
        .sidebar-collapse .main-header,
        .sidebar-collapse .main-footer { margin-right: 0 !important; }
        .nav-sidebar .nav-link { text-align: right; }
        .nav-sidebar .nav-link i,
        .nav-sidebar .nav-link .nav-icon { margin-left: 0.5rem; margin-right: 0; }
        .brand-link { text-align: right; }
        .table th, .table td { text-align: right; }
        .breadcrumb { direction: rtl; }
        .dropdown-menu { text-align: right; }
        .select2-container { width: 100% !important; }
        .card-title { font-size: 1.1rem; font-weight: 500; }
        .btn { border-radius: 4px; }
        .small-box .icon { left: 10px; right: auto; }
        .small-box .inner { text-align: right; }
    </style>
    <?php $this->yield('styles'); ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
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
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><?= e($__page_title ?? '') ?></h1>
                    </div>
                    <div class="col-sm-6">
                        <?php if (!empty($__breadcrumb)): ?>
                        <ol class="breadcrumb float-sm-left">
                            <li class="breadcrumb-item"><a href="<?= url('/') ?>"><i class="fas fa-home"></i></a></li>
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
    <footer class="main-footer">
        <strong>&copy; <?= date('Y') ?> <a href="<?= url('/') ?>"><?= e(config('app.name', 'نظام الجداول')) ?></a>.</strong>
        جميع الحقوق محفوظة.
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

<script>
    // Toastr RTL config
    toastr.options = {
        positionClass: 'toast-top-left',
        closeButton: true,
        progressBar: true,
        rtl: true,
        timeOut: 4000,
    };

    // DataTables RTL defaults
    $.extend(true, $.fn.dataTable.defaults, {
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
        },
        dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rtip',
        pageLength: 15,
    });

    // Initialize DataTables
    $(function () {
        $('.data-table').DataTable();
        $('.select2').select2({ theme: 'bootstrap4', dir: 'rtl' });
    });

    // Delete confirmation
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var form = $(this).closest('form');
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: 'لا يمكن التراجع عن هذا الإجراء!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'نعم، احذف',
            cancelButtonText: 'إلغاء'
        }).then(function(result) {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
</script>
<?php $this->yield('scripts'); ?>
</body>
</html>

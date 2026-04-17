/**
 * App-wide JavaScript utilities
 */
(function ($) {
    'use strict';

    // ── Toastr RTL Config ────────────────────────────────────────
    toastr.options = {
        positionClass: 'toast-top-left',
        closeButton: true,
        progressBar: true,
        rtl: true,
        timeOut: 4000,
    };

    // ── DataTables Arabic defaults ───────────────────────────────
    $.extend(true, $.fn.dataTable.defaults, {
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
        },
        dom: '<"row align-items-center mb-2"<"col-md-6 mb-2 mb-md-0"l><"col-md-6"f>>rt<"row align-items-center mt-2"<"col-md-6 mb-2 mb-md-0"i><"col-md-6"p>>',
        pageLength: 15,
        autoWidth: false,
        scrollX: true,
    });

    // ── Init on DOM Ready ────────────────────────────────────────
    $(function () {
        if (window.matchMedia('(max-width: 991.98px)').matches) {
            $('body').addClass('sidebar-collapse').removeClass('sidebar-open');
        }

        // Auto-init DataTables and Select2
        $('.data-table').each(function () {
            if (!$.fn.DataTable.isDataTable(this)) {
                $(this).DataTable();
            }
        });

        $('.select2').each(function () {
            var $el = $(this);
            if ($el.data('select2')) {
                return;
            }
            var $modal = $el.closest('.modal');
            var opts = { theme: 'bootstrap4', dir: 'rtl', width: '100%' };
            if ($modal.length) {
                opts.dropdownParent = $modal;
            }
            $el.select2(opts);
        });

        $(document).on('shown.bs.modal', '.modal', function () {
            var $modal = $(this);
            $modal.find('.select2').each(function () {
                var $el = $(this);
                if ($el.data('select2')) {
                    $el.select2('destroy');
                }
                $el.select2({
                    theme: 'bootstrap4',
                    dir: 'rtl',
                    width: '100%',
                    dropdownParent: $modal
                });
            });
        });

        // On mobile, close sidebar after selecting a real navigation link.
        $(document).on('click', '.nav-sidebar .nav-link', function () {
            var href = $(this).attr('href') || '';
            if (window.matchMedia('(max-width: 991.98px)').matches && href !== '#' && href !== '') {
                $('body').removeClass('sidebar-open').addClass('sidebar-collapse');
            }
        });

        // SweetAlert delete confirmation
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
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // Set-current confirmation (for academic years / semesters)
        $(document).on('click', '.btn-set-current', function (e) {
            e.preventDefault();
            var form = $(this).closest('form');
            var label = $(this).data('label') || '';
            Swal.fire({
                title: 'تعيين كافتراضي؟',
                text: label ? 'سيتم تعيين "' + label + '" كالحالي.' : 'هل تريد المتابعة؟',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'نعم',
                cancelButtonText: 'إلغاء'
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // Auto-dismiss alerts after 6 seconds
        setTimeout(function () {
            $('.alert-dismissible').fadeOut(500);
        }, 6000);
    });

})(jQuery);

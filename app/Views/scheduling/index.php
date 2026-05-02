<?php
$this->layout('layouts.app');
$__page_title = 'التسكين';
$__breadcrumb = [['label' => 'التسكين']];

$priorityMode = $priorityState['mode'] ?? 'disabled';
$priorityLabel = $priorityState['mode_label'] ?? 'معطل';
?>

<!-- Switch Navigation -->
<div class="d-flex flex-wrap align-items-center mb-3" style="gap: 0.5rem;">
    <span class="btn btn-primary btn-sm disabled">
        <i class="fas fa-list ml-1"></i> التسكين الكلاسيكي
    </span>
    <a href="<?= url('/grid-scheduling') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-th ml-1"></i> التسكين الشبكي
    </a>
    <?php if (can('scheduling.admin')): ?>
    <a href="<?= url('/admin-scheduling') ?>" class="btn btn-outline-info btn-sm">
        <i class="fas fa-user-shield ml-1"></i> التسكين الإداري
    </a>
    <?php endif; ?>
</div>

<!-- Priority State Info -->
<?php if ($priorityMode !== 'disabled'): ?>
<div class="alert alert-info d-flex flex-wrap align-items-center justify-content-between">
    <div class="mb-2 mb-sm-0">
        <i class="fas fa-sort-amount-up ml-1"></i>
        <strong>نظام الأولوية:</strong> <?= e($priorityLabel) ?>
        <?php if ($priorityMode === 'parallel_dept'): ?>
            — المجموعة تختلف حسب القسم
        <?php elseif ($priorityState['current_group']): ?>
            — المجموعة الحالية: <strong><?= e($priorityState['current_group']['group_name']) ?></strong>
        <?php endif; ?>
        <?php if ($priorityState['current_department']): ?>
            — القسم الحالي: <strong><?= e($priorityState['current_department']['department_name']) ?></strong>
        <?php endif; ?>
    </div>
    <?php if ($isAdmin): ?>
    <a href="<?= url('/priority') ?>" class="btn btn-outline-info btn-sm">
        <i class="fas fa-cog ml-1"></i> إدارة الأولوية
    </a>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Status Banner -->
<?php if ($canSchedule): ?>
<div class="alert alert-success d-flex flex-wrap align-items-center justify-content-between">
    <div class="mb-2 mb-sm-0">
        <i class="fas fa-check-circle ml-1"></i> <strong>دورك الآن!</strong> يمكنك إضافة محاضراتك في الجدول.
    </div>
    <?php if ($priorityMode === 'disabled'): ?>
    <form method="POST" action="<?= url('/scheduling/pass-role') ?>" class="m-0">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-dark btn-sm" onclick="return confirm('هل تريد تمرير الدور للعضو التالي؟')">
            <i class="fas fa-forward ml-1"></i> تمرير الدور
        </button>
    </form>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="alert alert-warning">
    <i class="fas fa-clock ml-1"></i>
    <?php if ($priorityMode !== 'disabled'): ?>
        ليس دورك حالياً بحسب نظام الأولوية. يمكنك عرض محاضراتك المسجلة فقط.
    <?php else: ?>
        ليس دورك حالياً. يمكنك عرض محاضراتك المسجلة فقط.
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($isAdmin && empty($myCourses)): ?>
<!-- Admin-specific guidance banner -->
<div class="alert alert-info border-0 d-flex flex-wrap align-items-center justify-content-between" style="background: #e8f4fd; border-right: 4px solid #2196F3 !important; color: #0c5460;">
    <div class="mb-2 mb-sm-0 d-flex align-items-center">
        <i class="fas fa-info-circle ml-2" style="font-size: 1.4rem; color: #2196F3;"></i>
        <strong style="font-size: 1.1rem;">أنت تتصفح كمدير نظام</strong>
    </div>
    <div class="d-flex flex-wrap" style="gap: 0.5rem;">
        <a href="<?= url('/admin-scheduling') ?>" class="btn btn-primary btn-sm" style="text-decoration: none;">
            <i class="fas fa-user-shield ml-1"></i> التسكين الإداري
        </a>
        <a href="<?= url('/grid-scheduling') ?>" class="btn btn-info btn-sm text-white" style="text-decoration: none;">
            <i class="fas fa-th ml-1"></i> التسكين الشبكي
        </a>
        <a href="<?= url('/timetable') ?>" class="btn btn-dark btn-sm" style="text-decoration: none;">
            <i class="far fa-clock ml-1"></i> عرض الجدول الكامل
        </a>
    </div>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-filter ml-1"></i> تصفية</h3></div>
    <form id="schedulingFilterForm" method="GET" action="<?= url('/scheduling') ?>">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>السنة الدراسية</label>
                        <select name="academic_year_id" id="academicYearFilter" class="form-control select2">
                            <option value="">-- الكل --</option>
                            <?php foreach (($academicYears ?? []) as $year): ?>
                                <option value="<?= $year['id'] ?>" <?= ($filters['academic_year_id'] ?? '') == $year['id'] ? 'selected' : '' ?>>
                                    <?= e($year['year_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>الفصل الدراسي</label>
                        <select name="semester_id" id="semesterFilter" class="form-control select2">
                            <option value="">-- الكل --</option>
                            <?php foreach (($semesters ?? []) as $semester): ?>
                                <option
                                    value="<?= $semester['id'] ?>"
                                    data-year-id="<?= (int)($semester['academic_year_id'] ?? 0) ?>"
                                    <?= ($filters['semester_id'] ?? '') == $semester['id'] ? 'selected' : '' ?>
                                >
                                    <?= e($semester['semester_name']) ?> - <?= e($semester['year_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search ml-1"></i> تطبيق</button>
                        <a href="<?= url('/scheduling') ?>" class="btn btn-secondary" id="btnResetFilter">إعادة تعيين</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Add Entry Form (kept as traditional POST for CSRF safety) -->
<?php if ($canSchedule): ?>
<div class="card card-primary card-outline" id="addEntryCard" <?= empty($myCourses) ? 'style="display:none"' : '' ?>>
    <div class="card-header"><h3 class="card-title"><i class="fas fa-plus ml-1"></i> إضافة محاضرة جديدة</h3></div>
    <form method="POST" action="<?= url('/scheduling') ?>">
        <?= csrf_field() ?>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>المقرر / الشعبة <span class="text-danger">*</span></label>
                        <select name="member_course_id" id="courseSelect" class="form-control select2" required>
                            <option value="">-- اختر --</option>
                            <?php foreach ($myCourses as $mc): ?>
                                <option value="<?= $mc['member_course_id'] ?>">
                                    <?= e($mc['subject_name']) ?> — <?= e($mc['section_name'] ?: $mc['division_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>القاعة <span class="text-danger">*</span></label>
                        <select name="classroom_id" class="form-control select2" required>
                            <option value="">-- اختر --</option>
                            <?php foreach ($classrooms as $cr): ?>
                                <option value="<?= $cr['classroom_id'] ?>"><?= e($cr['classroom_name']) ?> (<?= e($cr['classroom_type'] ?? '') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>الفترة <span class="text-danger">*</span></label>
                        <select name="session_id" class="form-control select2" required>
                            <option value="">-- اختر --</option>
                            <?php foreach ($sessions as $sess): ?>
                                <option value="<?= $sess['session_id'] ?>"><?= e($sess['day']) ?> — <?= e($sess['session_name'] ?? '') ?> (<?= $sess['start_time'] ?>-<?= $sess['end_time'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> إضافة للجدول</button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- Loading Overlay -->
<div id="ajaxLoadingOverlay" class="d-none">
    <div class="text-center py-5">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="sr-only">جاري التحميل...</span>
        </div>
        <p class="mt-3 text-muted">جاري تحميل البيانات...</p>
    </div>
</div>

<!-- My Scheduled Entries -->
<div id="schedulingResults">
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list ml-1"></i> محاضراتي في الجدول (<span id="entriesCount"><?= count($myEntries) ?></span>)</h3>
    </div>
    <div class="card-body table-responsive p-0" id="entriesTableContainer">
        <?php if (!empty($myEntries)): ?>
        <table class="table table-hover table-striped" id="myEntriesTable">
            <thead>
                <tr><th>#</th><th>المقرر</th><th>الشعبة</th><th>القاعة</th><th>اليوم</th><th>الفترة</th><th>إجراءات</th></tr>
            </thead>
            <tbody id="entriesBody">
                <?php foreach ($myEntries as $i => $e): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($e['subject_name'] ?? '') ?></td>
                    <td><?= e($e['section_name'] ?? '') ?></td>
                    <td><?= e($e['classroom_name'] ?? '') ?></td>
                    <td><?= e($e['day'] ?? '') ?></td>
                    <td><?= e($e['start_time'] ?? '') ?> - <?= e($e['end_time'] ?? '') ?></td>
                    <td>
                        <?php if ($canSchedule): ?>
                        <a href="<?= url("/scheduling/{$e['timetable_id']}/edit") ?>" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="<?= url("/scheduling/{$e['timetable_id']}/delete") ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger btn-xs btn-delete"><i class="fas fa-trash"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-center text-muted p-4">لا توجد محاضرات مسجلة حالياً</p>
        <?php endif; ?>
    </div>
</div>
</div>

<style>
    #ajaxLoadingOverlay { min-height: 200px; }
    .fade-in { animation: fadeIn 0.3s ease-in; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>

<?php $this->section('scripts'); ?>
<script>
(function() {
    'use strict';

    var BASE_URL = <?= json_encode(rtrim(url(''), '/')) ?>;
    var AJAX_URL = BASE_URL + '/scheduling/ajax-filter';
    var SEMESTERS_URL = BASE_URL + '/semesters/by-year';
    var CAN_SCHEDULE = <?= $canSchedule ? 'true' : 'false' ?>;
    var CSRF_TOKEN = <?= json_encode(csrf_token()) ?>;

    var form = document.getElementById('schedulingFilterForm');
    var resultsContainer = document.getElementById('schedulingResults');
    var loadingOverlay = document.getElementById('ajaxLoadingOverlay');
    var yearSelect = document.getElementById('academicYearFilter');
    var semesterSelect = document.getElementById('semesterFilter');
    var addEntryCard = document.getElementById('addEntryCard');
    var courseSelect = document.getElementById('courseSelect');

    // Debounce
    var debounceTimer;
    function debounce(fn, delay) {
        return function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(fn, delay);
        };
    }

    // Collect filter values
    function getFilterParams() {
        var params = {};
        var selects = form.querySelectorAll('select[name]');
        selects.forEach(function(sel) {
            if (sel.value) params[sel.name] = sel.value;
        });
        return params;
    }

    // Build query string
    function buildQueryString(params) {
        return Object.keys(params).map(function(k) {
            return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]);
        }).join('&');
    }

    // Escape HTML
    function esc(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Update course select (for the add-entry form)
    function updateCourseSelect(myCourses) {
        if (!courseSelect) return;

        var hasSelect2 = $(courseSelect).hasClass('select2-hidden-accessible');
        if (hasSelect2) $(courseSelect).select2('destroy');

        courseSelect.innerHTML = '<option value="">-- اختر --</option>';
        myCourses.forEach(function(mc) {
            var opt = document.createElement('option');
            opt.value = mc.member_course_id;
            opt.textContent = mc.subject_name + ' — ' + (mc.section_name || mc.division_name || '');
            courseSelect.appendChild(opt);
        });

        // Show/hide add entry card
        if (addEntryCard) {
            addEntryCard.style.display = myCourses.length > 0 ? '' : 'none';
        }

        if (hasSelect2 || $.fn.select2) {
            $(courseSelect).select2({ theme: 'bootstrap4', dir: 'rtl', allowClear: true, placeholder: '-- اختر --' });
        }
    }

    // Render entries table
    function renderEntriesTable(myEntries) {
        var countEl = document.getElementById('entriesCount');
        if (countEl) countEl.textContent = myEntries.length;

        var container = document.getElementById('entriesTableContainer');
        if (!container) return;

        if (myEntries.length === 0) {
            container.innerHTML = '<p class="text-center text-muted p-4 fade-in">لا توجد محاضرات مسجلة حالياً</p>';
            return;
        }

        var html = '<table class="table table-hover table-striped fade-in" id="myEntriesTable">';
        html += '<thead><tr><th>#</th><th>المقرر</th><th>الشعبة</th><th>القاعة</th><th>اليوم</th><th>الفترة</th><th>إجراءات</th></tr></thead>';
        html += '<tbody id="entriesBody">';
        myEntries.forEach(function(e, i) {
            html += '<tr>';
            html += '<td>' + (i + 1) + '</td>';
            html += '<td>' + esc(e.subject_name) + '</td>';
            html += '<td>' + esc(e.section_name) + '</td>';
            html += '<td>' + esc(e.classroom_name) + '</td>';
            html += '<td>' + esc(e.day) + '</td>';
            html += '<td>' + esc(e.start_time) + ' - ' + esc(e.end_time) + '</td>';
            html += '<td>';
            if (CAN_SCHEDULE) {
                html += '<a href="' + BASE_URL + '/scheduling/' + e.timetable_id + '/edit" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a> ';
                html += '<form method="POST" action="' + BASE_URL + '/scheduling/' + e.timetable_id + '/delete" class="d-inline">';
                html += '<input type="hidden" name="csrf_token" value="' + esc(CSRF_TOKEN) + '">';
                html += '<button type="submit" class="btn btn-danger btn-xs btn-delete"><i class="fas fa-trash"></i></button>';
                html += '</form>';
            }
            html += '</td></tr>';
        });
        html += '</tbody></table>';
        container.innerHTML = html;

        // Re-bind delete confirmations
        container.querySelectorAll('.btn-delete').forEach(function(btn) {
            btn.addEventListener('click', function(ev) {
                ev.preventDefault();
                var deleteForm = btn.closest('form');
                Swal.fire({
                    title: 'تأكيد الحذف',
                    text: 'هل أنت متأكد من حذف هذه المحاضرة؟',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'حذف',
                    cancelButtonText: 'إلغاء'
                }).then(function(result) {
                    if (result.isConfirmed) deleteForm.submit();
                });
            });
        });
    }

    // Main AJAX filter function
    function applyFilter() {
        var params = getFilterParams();
        var hasFilter = Object.keys(params).length > 0;

        // Update URL
        var newUrl = BASE_URL + '/scheduling' + (hasFilter ? '?' + buildQueryString(params) : '');
        history.pushState(params, '', newUrl);

        // Show loading
        var container = document.getElementById('entriesTableContainer');
        if (container) {
            container.innerHTML = '';
            loadingOverlay.classList.remove('d-none');
            container.appendChild(loadingOverlay);
        }

        fetch(AJAX_URL + '?' + buildQueryString(params), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            loadingOverlay.classList.add('d-none');

            if (!data.success) {
                if (container) container.innerHTML = '<p class="text-center text-danger p-4 fade-in">حدث خطأ في جلب البيانات.</p>';
                return;
            }

            // Update courses select
            updateCourseSelect(data.myCourses);
            // Update entries table
            renderEntriesTable(data.myEntries);
        })
        .catch(function(err) {
            loadingOverlay.classList.add('d-none');
            if (container) container.innerHTML = '<p class="text-center text-danger p-4 fade-in">حدث خطأ: ' + esc(err.message) + '</p>';
        });
    }

    // Load semesters via AJAX
    function loadSemestersByYear(yearId) {
        var savedValue = semesterSelect.value;
        var hasSelect2 = $(semesterSelect).hasClass('select2-hidden-accessible');

        var url = SEMESTERS_URL + (yearId ? '?academic_year_id=' + yearId : '');
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) return;
            if (hasSelect2) $(semesterSelect).select2('destroy');

            semesterSelect.innerHTML = '<option value="">-- الكل --</option>';
            var foundSaved = false;
            data.semesters.forEach(function(sem) {
                var opt = document.createElement('option');
                opt.value = sem.id;
                opt.setAttribute('data-year-id', sem.academic_year_id || '');
                opt.textContent = sem.semester_name + (sem.year_name ? ' - ' + sem.year_name : '');
                if (String(sem.id) === String(savedValue)) {
                    opt.selected = true;
                    foundSaved = true;
                }
                semesterSelect.appendChild(opt);
            });
            if (!foundSaved) semesterSelect.value = '';

            if (hasSelect2 || $.fn.select2) {
                $(semesterSelect).select2({ theme: 'bootstrap4', dir: 'rtl', allowClear: true, placeholder: '-- الكل --' });
            }
        })
        .catch(function() {});
    }

    // Intercept form submit
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        applyFilter();
    });

    // Auto-submit on filter change
    var filterSelects = form.querySelectorAll('select[name]');
    filterSelects.forEach(function(sel) {
        $(sel).on('change', debounce(applyFilter, 300));
    });

    // Year change → load semesters
    $(yearSelect).on('change', function() {
        loadSemestersByYear(this.value);
    });

    // Browser back/forward
    window.addEventListener('popstate', function(e) {
        if (e.state) {
            Object.keys(e.state).forEach(function(key) {
                var sel = form.querySelector('[name="' + key + '"]');
                if (sel) {
                    $(sel).val(e.state[key]).trigger('change.select2');
                }
            });
        }
        applyFilter();
    });

    // Reset button
    var resetBtn = document.getElementById('btnResetFilter');
    if (resetBtn) {
        resetBtn.addEventListener('click', function(e) {
            e.preventDefault();
            filterSelects.forEach(function(sel) {
                $(sel).val('').trigger('change.select2');
            });
            history.pushState({}, '', BASE_URL + '/scheduling');
            loadSemestersByYear('');
            applyFilter();
        });
    }

})();
</script>
<?php $this->endSection(); ?>

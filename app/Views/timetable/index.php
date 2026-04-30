<?php
$this->layout('layouts.app');
$__page_title = 'الجدول الدراسي';
$__breadcrumb = [['label' => 'الجدول الدراسي']];
?>

<!-- Filters -->
<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-filter ml-1"></i> تصفية</h3></div>
    <form id="timetableFilterForm" method="GET" action="<?= url('/timetable') ?>">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>القسم</label>
                        <select name="department_id" id="filterDepartment" class="form-control select2">
                            <option value="">-- الكل --</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['department_id'] ?>" <?= ($filters['department_id'] ?? '') == $d['department_id'] ? 'selected' : '' ?>><?= e($d['department_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>الفرقة</label>
                        <select name="level_id" id="filterLevel" class="form-control select2">
                            <option value="">-- الكل --</option>
                            <?php foreach ($levels as $l): ?>
                                <option value="<?= $l['level_id'] ?>" <?= ($filters['level_id'] ?? '') == $l['level_id'] ? 'selected' : '' ?>><?= e($l['level_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>عضو هيئة التدريس</label>
                        <select name="member_id" id="filterMember" class="form-control select2">
                            <option value="">-- الكل --</option>
                            <?php foreach ($members as $m): ?>
                                <option value="<?= $m['member_id'] ?>" <?= ($filters['member_id'] ?? '') == $m['member_id'] ? 'selected' : '' ?>><?= e($m['member_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>القاعة</label>
                        <select name="classroom_id" id="filterClassroom" class="form-control select2">
                            <option value="">-- الكل --</option>
                            <?php foreach ($classrooms as $c): ?>
                                <option value="<?= $c['classroom_id'] ?>" <?= ($filters['classroom_id'] ?? '') == $c['classroom_id'] ? 'selected' : '' ?>><?= e($c['classroom_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
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
                <div class="col-md-3">
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
            </div>
        </div>
        <div class="card-footer d-flex flex-wrap align-items-center justify-content-between">
            <div class="mb-2 mb-sm-0">
                <button type="submit" class="btn btn-primary" id="btnApplyFilter"><i class="fas fa-search ml-1"></i> عرض</button>
                <a href="<?= url('/timetable') ?>" class="btn btn-secondary" id="btnResetFilter">إعادة تعيين</a>
            </div>
            <div id="exportButtons" class="btn-group <?= !$hasFilter ? 'd-none' : '' ?>" role="group">
                <?php if (can('timetable.export')): ?>
                <a href="#" class="btn btn-danger btn-sm export-link" data-format="pdf" target="_blank">
                    <i class="fas fa-file-pdf ml-1"></i> PDF
                </a>
                <a href="#" class="btn btn-success btn-sm export-link" data-format="excel">
                    <i class="fas fa-file-excel ml-1"></i> Excel
                </a>
                <a href="#" class="btn btn-secondary btn-sm export-link" data-format="csv">
                    <i class="fas fa-file-csv ml-1"></i> CSV
                </a>
                <a href="#" class="btn btn-info btn-sm export-link" data-format="html" target="_blank">
                    <i class="fas fa-print ml-1"></i> طباعة
                </a>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<!-- Loading Overlay -->
<div id="ajaxLoadingOverlay" class="d-none">
    <div class="text-center py-5">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="sr-only">جاري التحميل...</span>
        </div>
        <p class="mt-3 text-muted">جاري تحميل البيانات...</p>
    </div>
</div>

<!-- Results Container -->
<div id="timetableResults">
<?php if ($hasFilter): ?>

    <!-- Pivot Table (if dept + level selected) -->
    <?php if ($pivotData): ?>
    <div class="card" id="pivotCard">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-table ml-1"></i> جدول التوزيع</h3></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered timetable-pivot" style="margin: 0;">
                <thead>
                    <tr>
                        <th style="width: 80px;">اليوم</th>
                        <?php foreach ($pivotData['sessions'] as $sess): ?>
                            <th><?= e($sess['session_name'] ?? '') ?><br><small><?= e($sess['start_time'] ?? '') ?> - <?= e($sess['end_time'] ?? '') ?></small></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pivotData['days'] as $day): ?>
                    <tr>
                        <td class="day-header"><?= e($day) ?></td>
                        <?php foreach ($pivotData['sessions'] as $sess): ?>
                            <td>
                                <?php
                                $cellEntries = $pivotData['data'][$day][$sess['slot_key']] ?? [];
                                if (!empty($cellEntries)):
                                ?>
                                    <?php foreach ($cellEntries as $idx => $cell): ?>
                                        <?php
                                        $courseType = strtolower($cell['subject_type'] ?? 'نظري');
                                        $cssClass = 'course-cell course-theory';
                                        if (strpos($courseType, 'عملي') !== false && strpos($courseType, 'نظري') !== false) {
                                            $cssClass = 'course-cell course-mixed';
                                        } elseif (strpos($courseType, 'عملي') !== false) {
                                            $cssClass = 'course-cell course-practical';
                                        } elseif (strpos($courseType, 'مختبر') !== false || strpos($courseType, 'lab') !== false) {
                                            $cssClass = 'course-cell course-lab';
                                        }
                                        ?>
                                        <div class="<?= $cssClass ?>">
                                            <div class="course-name"><?= e($cell['subject_name']) ?></div>
                                            <div class="course-instructor">أ/ <?= e($cell['member_name']) ?></div>
                                            <div class="course-room"><?= e($cell['classroom_name']) ?> <small>(<?= e($cell['section_name'] ?? 'محاضرة عامة') ?>)</small></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="empty-cell">—</div>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Flat List -->
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-list ml-1"></i> نتائج البحث (<span id="resultsCount"><?= count($entries) ?></span>)</h3></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped data-table" id="flatListTable">
                <thead>
                    <tr><th>#</th><th>المقرر</th><th>العضو</th><th>الشعبة</th><th>القاعة</th><th>اليوم</th><th>الفترة</th></tr>
                </thead>
                <tbody id="flatListBody">
                    <?php foreach ($entries as $i => $e): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= e($e['subject_name'] ?? '') ?></td>
                        <td><?= e($e['member_name'] ?? '') ?></td>
                        <td><?= e($e['section_name'] ?? '') ?></td>
                        <td><?= e($e['classroom_name'] ?? '') ?></td>
                        <td><?= e($e['day'] ?? '') ?></td>
                        <td><?= e($e['start_time'] ?? '') ?> - <?= e($e['end_time'] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php else: ?>
<div class="callout callout-info" id="noFilterMsg">
    <p><i class="fas fa-info-circle ml-1"></i> اختر معايير التصفية أعلاه ثم اضغط "عرض" لعرض الجدول.</p>
</div>
<?php endif; ?>
</div>

<style>
    .timetable-pivot { font-size: 0.95rem; }
    .timetable-pivot thead { background-color: #2c3e50; }
    .timetable-pivot th { color: #fff; font-weight: 600; padding: 12px 8px; }
    .timetable-pivot tbody td { padding: 8px 6px; vertical-align: middle; min-height: 80px; }
    .timetable-pivot .day-header { background-color: #ecf0f1; font-weight: bold; text-align: center; }
    .timetable-pivot .course-cell { border-radius: 4px; padding: 8px; margin-bottom: 4px; color: #fff; word-wrap: break-word; font-size: 0.9rem; line-height: 1.4; }
    .timetable-pivot .course-theory { background-color: #f39c12; } /* أصفر للنظري */
    .timetable-pivot .course-practical { background-color: #3498db; } /* أزرق للعملي */
    .timetable-pivot .course-mixed { background-color: #9b59b6; } /* بنفسجي للمختلط */
    .timetable-pivot .course-lab { background-color: #e67e22; } /* برتقالي للمختبر */
    .timetable-pivot .course-name { font-weight: bold; margin-bottom: 4px; }
    .timetable-pivot .course-instructor { font-size: 0.85rem; margin: 3px 0; }
    .timetable-pivot .course-room { font-size: 0.8rem; margin-top: 3px; }
    .timetable-pivot .empty-cell { background-color: #f8f9fa; color: #999; text-align: center; }
    #ajaxLoadingOverlay { min-height: 200px; }
    .fade-in { animation: fadeIn 0.3s ease-in; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>

<?php $this->section('scripts'); ?>
<script>
(function() {
    'use strict';

    var BASE_URL = <?= json_encode(rtrim(url(''), '/')) ?>;
    var AJAX_URL = BASE_URL + '/timetable/ajax-filter';
    var EXPORT_URL = BASE_URL + '/timetable/export';
    var SEMESTERS_URL = BASE_URL + '/semesters/by-year';

    var form = document.getElementById('timetableFilterForm');
    var resultsContainer = document.getElementById('timetableResults');
    var loadingOverlay = document.getElementById('ajaxLoadingOverlay');
    var exportBtnGroup = document.getElementById('exportButtons');
    var yearSelect = document.getElementById('academicYearFilter');
    var semesterSelect = document.getElementById('semesterFilter');

    // Debounce helper
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

    // Update export links
    function updateExportLinks(params) {
        var hasFilter = Object.keys(params).length > 0;
        if (exportBtnGroup) {
            exportBtnGroup.classList.toggle('d-none', !hasFilter);
        }
        document.querySelectorAll('.export-link').forEach(function(link) {
            var format = link.getAttribute('data-format');
            var exportParams = Object.assign({}, params, { format: format });
            link.href = EXPORT_URL + '?' + buildQueryString(exportParams);
        });
    }

    // Escape HTML
    function esc(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Determine course CSS class
    function getCourseClass(subjectType) {
        var type = (subjectType || 'نظري').toLowerCase();
        if (type.indexOf('عملي') !== -1 && type.indexOf('نظري') !== -1) return 'course-cell course-mixed';
        if (type.indexOf('عملي') !== -1) return 'course-cell course-practical';
        if (type.indexOf('مختبر') !== -1 || type.indexOf('lab') !== -1) return 'course-cell course-lab';
        return 'course-cell course-theory';
    }

    // Render pivot table HTML
    function renderPivotTable(pivotData) {
        if (!pivotData || !pivotData.sessions || pivotData.sessions.length === 0) return '';

        var html = '<div class="card fade-in" id="pivotCard">';
        html += '<div class="card-header"><h3 class="card-title"><i class="fas fa-table ml-1"></i> جدول التوزيع</h3></div>';
        html += '<div class="card-body table-responsive p-0">';
        html += '<table class="table table-bordered timetable-pivot" style="margin: 0;">';

        // Thead
        html += '<thead><tr><th style="width: 80px;">اليوم</th>';
        pivotData.sessions.forEach(function(sess) {
            html += '<th>' + esc(sess.session_name || '') + '<br><small>' + esc(sess.start_time || '') + ' - ' + esc(sess.end_time || '') + '</small></th>';
        });
        html += '</tr></thead>';

        // Tbody
        html += '<tbody>';
        pivotData.days.forEach(function(day) {
            html += '<tr><td class="day-header">' + esc(day) + '</td>';
            pivotData.sessions.forEach(function(sess) {
                html += '<td>';
                var cellEntries = (pivotData.data[day] && pivotData.data[day][sess.slot_key]) || [];
                if (cellEntries.length > 0) {
                    cellEntries.forEach(function(cell) {
                        html += '<div class="' + getCourseClass(cell.subject_type) + '">';
                        html += '<div class="course-name">' + esc(cell.subject_name) + '</div>';
                        html += '<div class="course-instructor">أ/ ' + esc(cell.member_name) + '</div>';
                        html += '<div class="course-room">' + esc(cell.classroom_name) + ' <small>(' + esc(cell.section_name || 'محاضرة عامة') + ')</small></div>';
                        html += '</div>';
                    });
                } else {
                    html += '<div class="empty-cell">—</div>';
                }
                html += '</td>';
            });
            html += '</tr>';
        });
        html += '</tbody></table></div></div>';
        return html;
    }

    // Render flat list HTML
    function renderFlatList(entries) {
        var html = '<div class="card fade-in">';
        html += '<div class="card-header"><h3 class="card-title"><i class="fas fa-list ml-1"></i> نتائج البحث (<span id="resultsCount">' + entries.length + '</span>)</h3></div>';
        html += '<div class="card-body table-responsive p-0">';
        html += '<table class="table table-hover table-striped" id="flatListTable">';
        html += '<thead><tr><th>#</th><th>المقرر</th><th>العضو</th><th>الشعبة</th><th>القاعة</th><th>اليوم</th><th>الفترة</th></tr></thead>';
        html += '<tbody id="flatListBody">';
        entries.forEach(function(e, i) {
            html += '<tr>';
            html += '<td>' + (i + 1) + '</td>';
            html += '<td>' + esc(e.subject_name) + '</td>';
            html += '<td>' + esc(e.member_name) + '</td>';
            html += '<td>' + esc(e.section_name) + '</td>';
            html += '<td>' + esc(e.classroom_name) + '</td>';
            html += '<td>' + esc(e.day) + '</td>';
            html += '<td>' + esc(e.start_time) + ' - ' + esc(e.end_time) + '</td>';
            html += '</tr>';
        });
        html += '</tbody></table></div></div>';
        return html;
    }

    // Main AJAX filter function
    function applyFilter() {
        var params = getFilterParams();
        var hasFilter = Object.keys(params).length > 0;

        // Update URL without reload
        var newUrl = BASE_URL + '/timetable' + (hasFilter ? '?' + buildQueryString(params) : '');
        history.pushState(params, '', newUrl);

        // Update export links
        updateExportLinks(params);

        if (!hasFilter) {
            resultsContainer.innerHTML = '<div class="callout callout-info fade-in"><p><i class="fas fa-info-circle ml-1"></i> اختر معايير التصفية أعلاه ثم اضغط "عرض" لعرض الجدول.</p></div>';
            return;
        }

        // Show loading
        resultsContainer.innerHTML = '';
        loadingOverlay.classList.remove('d-none');
        resultsContainer.appendChild(loadingOverlay);

        fetch(AJAX_URL + '?' + buildQueryString(params), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            loadingOverlay.classList.add('d-none');
            resultsContainer.innerHTML = '';

            if (!data.success) {
                resultsContainer.innerHTML = '<div class="callout callout-danger fade-in"><p><i class="fas fa-exclamation-circle ml-1"></i> حدث خطأ في جلب البيانات.</p></div>';
                return;
            }

            if (data.count === 0) {
                resultsContainer.innerHTML = '<div class="callout callout-warning fade-in"><p><i class="fas fa-search ml-1"></i> لا توجد نتائج تطابق معايير البحث.</p></div>';
                return;
            }

            var html = '';
            // Pivot table
            if (data.pivotData) {
                html += renderPivotTable(data.pivotData);
            }
            // Flat list
            html += renderFlatList(data.entries);
            resultsContainer.innerHTML = html;

            // Reinitialize DataTable if plugin exists
            if ($.fn.DataTable && document.getElementById('flatListTable')) {
                $('#flatListTable').DataTable({
                    language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json' },
                    pageLength: 25,
                    order: []
                });
            }
        })
        .catch(function(err) {
            loadingOverlay.classList.add('d-none');
            resultsContainer.innerHTML = '<div class="callout callout-danger fade-in"><p><i class="fas fa-exclamation-circle ml-1"></i> حدث خطأ: ' + esc(err.message) + '</p></div>';
        });
    }

    // Load semesters via AJAX when year changes
    function loadSemestersByYear(yearId) {
        var savedValue = semesterSelect.value;

        // If Select2 is initialized, destroy and re-init after update
        var hasSelect2 = $(semesterSelect).hasClass('select2-hidden-accessible');

        var url = SEMESTERS_URL + (yearId ? '?academic_year_id=' + yearId : '');
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) return;

            if (hasSelect2) $(semesterSelect).select2('destroy');

            // Rebuild options
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

            // Re-init Select2
            if (hasSelect2 || $.fn.select2) {
                $(semesterSelect).select2({ theme: 'bootstrap4', dir: 'rtl', allowClear: true, placeholder: '-- الكل --' });
            }
        })
        .catch(function() {}); // Silent fail for semester loading
    }

    // Intercept form submit
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        applyFilter();
    });

    // Auto-submit on filter change (with debounce)
    var filterSelects = form.querySelectorAll('select[name]');
    filterSelects.forEach(function(sel) {
        $(sel).on('change', debounce(applyFilter, 300));
    });

    // Year change → load semesters from server + auto-filter
    $(yearSelect).on('change', function() {
        loadSemestersByYear(this.value);
    });

    // Handle browser back/forward
    window.addEventListener('popstate', function(e) {
        if (e.state) {
            // Restore filter values
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
            // Update URL
            history.pushState({}, '', BASE_URL + '/timetable');
            // Clear results
            resultsContainer.innerHTML = '<div class="callout callout-info fade-in"><p><i class="fas fa-info-circle ml-1"></i> اختر معايير التصفية أعلاه ثم اضغط "عرض" لعرض الجدول.</p></div>';
            if (exportBtnGroup) exportBtnGroup.classList.add('d-none');
            // Reload all semesters
            loadSemestersByYear('');
        });
    }

    // Initialize export links for current filters
    updateExportLinks(getFilterParams());

})();
</script>
<?php $this->endSection(); ?>

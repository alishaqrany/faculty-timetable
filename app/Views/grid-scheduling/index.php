<?php
$this->layout('layouts.app');
$__page_title = 'التسكين الشبكي';
$__breadcrumb = [['label' => 'التسكين الشبكي']];
?>

<style>
    .grid-timetable { border-collapse: collapse; width: 100%; min-width: 800px; }
    .grid-timetable th, .grid-timetable td { border: 1px solid rgba(19,64,98,0.16); padding: 0.45rem 0.5rem; vertical-align: top; text-align: center; }
    .grid-timetable thead th { background: linear-gradient(180deg, #eef6fc, #e4effa); color: #1f415b; font-weight: 700; font-size: 0.95rem; min-width: 140px; }
    .grid-timetable .slot-header { background: #f4f9ff; font-weight: 700; font-size: 0.88rem; color: #2a5070; min-width: 120px; white-space: nowrap; }
    .grid-cell { min-height: 60px; position: relative; cursor: pointer; transition: background 0.2s; border-radius: 6px; }
    .grid-cell:hover { background: rgba(15,159,145,0.06); }
    .grid-cell.has-entry { cursor: default; }
    .grid-entry { background: linear-gradient(135deg, rgba(42,115,181,0.12), rgba(42,115,181,0.06)); border: 1px solid rgba(42,115,181,0.22); border-radius: 10px; padding: 0.4rem 0.5rem; margin-bottom: 0.3rem; text-align: right; position: relative; font-size: 0.88rem; animation: gridFadeIn 0.3s ease; }
    .grid-entry .entry-subject { font-weight: 700; color: #1a3d5c; display: block; margin-bottom: 0.15rem; }
    .grid-entry .entry-member { color: #3a7caa; font-size: 0.82rem; }
    .grid-entry .entry-room { color: #6a8fa8; font-size: 0.78rem; }
    .grid-entry .entry-section { color: #8a6f40; font-size: 0.78rem; }
    .grid-entry .btn-del { position: absolute; top: 2px; left: 4px; background: rgba(217,92,88,0.1); border: none; color: #d95c58; border-radius: 6px; padding: 0.1rem 0.3rem; font-size: 0.72rem; cursor: pointer; opacity: 0.6; transition: opacity 0.2s; }
    .grid-entry .btn-del:hover { opacity: 1; background: rgba(217,92,88,0.2); }
    .grid-cell-empty { color: #afc5d6; font-size: 1.3rem; display: flex; align-items: center; justify-content: center; min-height: 60px; }
    .grid-cell-empty:hover { color: #0f9f91; }
    @keyframes gridFadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
    .grid-loading { text-align: center; padding: 2rem; }
    .color-0 { border-color: rgba(42,115,181,0.3) !important; background: linear-gradient(135deg, rgba(42,115,181,0.12), rgba(42,115,181,0.04)) !important; }
    .color-1 { border-color: rgba(31,186,136,0.3) !important; background: linear-gradient(135deg, rgba(31,186,136,0.12), rgba(31,186,136,0.04)) !important; }
    .color-2 { border-color: rgba(242,169,60,0.3) !important; background: linear-gradient(135deg, rgba(242,169,60,0.12), rgba(242,169,60,0.04)) !important; }
    .color-3 { border-color: rgba(217,92,88,0.3) !important; background: linear-gradient(135deg, rgba(217,92,88,0.12), rgba(217,92,88,0.04)) !important; }
    .color-4 { border-color: rgba(124,77,167,0.3) !important; background: linear-gradient(135deg, rgba(124,77,167,0.12), rgba(124,77,167,0.04)) !important; }
    .color-5 { border-color: rgba(20,158,162,0.3) !important; background: linear-gradient(135deg, rgba(20,158,162,0.12), rgba(20,158,162,0.04)) !important; }
</style>

<!-- Switch Navigation -->
<div class="d-flex flex-wrap align-items-center mb-3" style="gap: 0.5rem;">
    <a href="<?= url('/scheduling') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-list ml-1"></i> التسكين الكلاسيكي
    </a>
    <span class="btn btn-primary btn-sm disabled">
        <i class="fas fa-th ml-1"></i> التسكين الشبكي
    </span>
    <?php if ($canAdmin): ?>
    <a href="<?= url('/admin-scheduling') ?>" class="btn btn-outline-info btn-sm">
        <i class="fas fa-user-shield ml-1"></i> التسكين الإداري
    </a>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter ml-1"></i> تصفية الجدول</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>القسم</label>
                    <select id="filterDept" class="form-control select2">
                        <option value="">-- الكل --</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['department_id'] ?>"><?= e($d['department_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>الفرقة</label>
                    <select id="filterLevel" class="form-control select2">
                        <option value="">-- الكل --</option>
                        <?php foreach ($levels as $l): ?>
                            <option value="<?= $l['level_id'] ?>"><?= e($l['level_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>السنة الأكاديمية</label>
                    <select id="filterYear" class="form-control select2">
                        <option value="">-- الكل --</option>
                        <?php foreach ($academicYears as $y): ?>
                            <option value="<?= $y['id'] ?>"><?= e($y['year_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="form-group mb-0 w-100">
                    <button type="button" id="btnApplyFilter" class="btn btn-primary btn-block">
                        <i class="fas fa-search ml-1"></i> عرض الجدول
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grid Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-th ml-1"></i> الجدول الشبكي (<span id="gridCount">0</span> محاضرة)</h3>
    </div>
    <div class="card-body table-responsive p-0" id="gridContainer">
        <div class="text-center text-muted p-5">
            <i class="fas fa-th" style="font-size: 3rem; opacity: 0.3;"></i>
            <p class="mt-3">اختر القسم والفرقة ثم اضغط "عرض الجدول" لعرض الشبكة</p>
        </div>
    </div>
</div>

<!-- Add Entry Modal -->
<div class="modal fade" id="addEntryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle ml-1"></i> إضافة محاضرة</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">
                    <strong>اليوم:</strong> <span id="modalDay"></span> —
                    <strong>الفترة:</strong> <span id="modalSlot"></span>
                </p>
                <input type="hidden" id="modalSessionId">

                <?php if ($canAdmin): ?>
                <div class="form-group">
                    <label>العضو</label>
                    <select id="modalMember" class="form-control">
                        <option value="">-- اختر العضو --</option>
                        <?php foreach ($members as $m): ?>
                            <option value="<?= $m['member_id'] ?>"><?= e($m['member_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>المقرر <span class="text-danger">*</span></label>
                    <select id="modalCourse" class="form-control" required>
                        <option value="">-- اختر المقرر --</option>
                        <?php if (!$canAdmin): ?>
                            <?php foreach ($myCourses as $mc): ?>
                                <option value="<?= $mc['member_course_id'] ?>">
                                    <?= e($mc['subject_name']) ?> — <?= e($mc['section_name'] ?: $mc['division_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>القاعة <span class="text-danger">*</span></label>
                    <select id="modalClassroom" class="form-control" required>
                        <option value="">-- اختر القاعة --</option>
                        <?php foreach ($classrooms as $cr): ?>
                            <option value="<?= $cr['classroom_id'] ?>"><?= e($cr['classroom_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                <button type="button" id="btnModalSave" class="btn btn-primary">
                    <i class="fas fa-save ml-1"></i> إضافة
                </button>
            </div>
        </div>
    </div>
</div>

<?php $this->section('scripts'); ?>
<script>
(function() {
    'use strict';

    var BASE = <?= json_encode(rtrim(url(''), '/')) ?>;
    var CSRF = <?= json_encode(csrf_token()) ?>;
    var CAN_ADMIN = <?= $canAdmin ? 'true' : 'false' ?>;
    var CAN_SCHEDULE = <?= $canSchedule ? 'true' : 'false' ?>;
    var MY_MEMBER_ID = <?= $memberId ?: 'null' ?>;
    var DAYS = <?= json_encode($days) ?>;
    var TIME_SLOTS = <?= json_encode($timeSlots) ?>;
    var SESSIONS_MAP = <?= json_encode(array_column($sessions, null, 'session_id')) ?>;

    // Color map for members
    var memberColorMap = {};
    var colorIdx = 0;

    function getMemberColor(memberId) {
        if (!memberColorMap[memberId]) {
            memberColorMap[memberId] = 'color-' + (colorIdx % 6);
            colorIdx++;
        }
        return memberColorMap[memberId];
    }

    function esc(str) {
        if (!str) return '';
        var d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    // Find session_id for a given day + time slot
    function findSessionId(day, slotKey) {
        var parts = slotKey.split('|');
        var sessionName = parts[0], startTime = parts[1], endTime = parts[2];
        for (var sid in SESSIONS_MAP) {
            var s = SESSIONS_MAP[sid];
            if (s.day === day && s.session_name === sessionName && s.start_time === startTime && s.end_time === endTime) {
                return parseInt(sid);
            }
        }
        // Fallback: find session by day + start/end
        for (var sid2 in SESSIONS_MAP) {
            var s2 = SESSIONS_MAP[sid2];
            if (s2.day === day && s2.start_time === startTime && s2.end_time === endTime) {
                return parseInt(sid2);
            }
        }
        return null;
    }

    function loadGrid() {
        var dept = document.getElementById('filterDept').value;
        var level = document.getElementById('filterLevel').value;
        var year = document.getElementById('filterYear').value;

        if (!dept && !level) {
            toastr.info('اختر القسم أو الفرقة على الأقل');
            return;
        }

        var container = document.getElementById('gridContainer');
        container.innerHTML = '<div class="grid-loading"><span class="spinner-border text-primary" style="width:2.5rem;height:2.5rem;"></span><p class="mt-2 text-muted">جاري تحميل الشبكة...</p></div>';

        var params = [];
        if (dept) params.push('department_id=' + dept);
        if (level) params.push('level_id=' + level);
        if (year) params.push('academic_year_id=' + year);

        fetch(BASE + '/grid-scheduling/grid-data?' + params.join('&'), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) {
                container.innerHTML = '<p class="text-center text-danger p-4">خطأ في جلب البيانات</p>';
                return;
            }

            document.getElementById('gridCount').textContent = data.count || 0;
            memberColorMap = {};
            colorIdx = 0;
            renderGrid(data.grid || {});
        })
        .catch(function() {
            container.innerHTML = '<p class="text-center text-danger p-4">خطأ في الاتصال</p>';
        });
    }

    function renderGrid(gridData) {
        var container = document.getElementById('gridContainer');
        var html = '<table class="grid-timetable"><thead><tr><th class="slot-header">الفترة</th>';

        DAYS.forEach(function(day) {
            html += '<th>' + esc(day) + '</th>';
        });
        html += '</tr></thead><tbody>';

        TIME_SLOTS.forEach(function(slot) {
            var slotKey = slot.session_name + '|' + slot.start_time + '|' + slot.end_time;
            html += '<tr>';
            html += '<td class="slot-header">' + esc(slot.session_name) + '<br><small class="text-muted">' + esc(slot.start_time) + ' - ' + esc(slot.end_time) + '</small></td>';

            DAYS.forEach(function(day) {
                var cellEntries = (gridData[day] && gridData[day][slotKey]) ? gridData[day][slotKey] : [];
                html += '<td class="grid-cell ' + (cellEntries.length > 0 ? 'has-entry' : '') + '" data-day="' + esc(day) + '" data-slot="' + esc(slotKey) + '">';

                if (cellEntries.length > 0) {
                    cellEntries.forEach(function(e) {
                        var colorClass = getMemberColor(e.member_id);
                        html += '<div class="grid-entry ' + colorClass + '">';
                        html += '<span class="entry-subject">' + esc(e.subject_name) + '</span>';
                        html += '<span class="entry-member"><i class="fas fa-user-tie" style="font-size:0.7rem;"></i> ' + esc(e.member_name) + '</span><br>';
                        html += '<span class="entry-room"><i class="fas fa-door-open" style="font-size:0.7rem;"></i> ' + esc(e.classroom_name) + '</span>';
                        if (e.section_name) html += ' <span class="entry-section">(' + esc(e.section_name) + ')</span>';
                        if (CAN_ADMIN || CAN_SCHEDULE) {
                            html += '<button class="btn-del" data-id="' + e.timetable_id + '" title="حذف"><i class="fas fa-times"></i></button>';
                        }
                        html += '</div>';
                    });
                }

                // Empty cell click area
                if (CAN_ADMIN || CAN_SCHEDULE) {
                    html += '<div class="grid-cell-empty" data-day="' + esc(day) + '" data-slot="' + esc(slotKey) + '"><i class="fas fa-plus"></i></div>';
                }

                html += '</td>';
            });
            html += '</tr>';
        });

        html += '</tbody></table>';
        container.innerHTML = html;

        // Bind click on empty cells
        container.querySelectorAll('.grid-cell-empty').forEach(function(el) {
            el.addEventListener('click', function(ev) {
                ev.stopPropagation();
                var day = el.getAttribute('data-day');
                var slotKey = el.getAttribute('data-slot');
                openAddModal(day, slotKey);
            });
        });

        // Bind delete buttons
        container.querySelectorAll('.btn-del').forEach(function(btn) {
            btn.addEventListener('click', function(ev) {
                ev.stopPropagation();
                var tid = btn.getAttribute('data-id');
                Swal.fire({
                    title: 'تأكيد الحذف',
                    text: 'هل تريد حذف هذه المحاضرة من الجدول؟',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'حذف',
                    cancelButtonText: 'إلغاء'
                }).then(function(result) {
                    if (result.isConfirmed) deleteGridEntry(tid);
                });
            });
        });
    }

    function openAddModal(day, slotKey) {
        var parts = slotKey.split('|');
        document.getElementById('modalDay').textContent = day;
        document.getElementById('modalSlot').textContent = parts[0] + ' (' + parts[1] + ' - ' + parts[2] + ')';

        var sessionId = findSessionId(day, slotKey);
        document.getElementById('modalSessionId').value = sessionId || '';

        if (!sessionId) {
            toastr.error('لم يتم العثور على فترة زمنية مطابقة لهذا اليوم');
            return;
        }

        // Reset course select
        if (!CAN_ADMIN) {
            // Keep pre-filled courses for normal user
        } else {
            document.getElementById('modalCourse').innerHTML = '<option value="">-- اختر العضو أولاً --</option>';
        }

        $('#addEntryModal').modal('show');
    }

    // If admin, load courses when member changes in modal
    if (CAN_ADMIN) {
        var modalMember = document.getElementById('modalMember');
        if (modalMember) {
            modalMember.addEventListener('change', function() {
                var memberId = this.value;
                var courseSelect = document.getElementById('modalCourse');
                if (!memberId) {
                    courseSelect.innerHTML = '<option value="">-- اختر العضو أولاً --</option>';
                    return;
                }
                courseSelect.innerHTML = '<option value="">-- جاري التحميل... --</option>';

                fetch(BASE + '/admin-scheduling/member-courses/' + memberId, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    courseSelect.innerHTML = '<option value="">-- اختر المقرر --</option>';
                    if (data.success && data.courses) {
                        data.courses.forEach(function(c) {
                            var opt = document.createElement('option');
                            opt.value = c.member_course_id;
                            opt.textContent = c.subject_name + ' — ' + (c.section_name || c.division_name || '');
                            courseSelect.appendChild(opt);
                        });
                    }
                });
            });
        }
    }

    // Save from modal
    document.getElementById('btnModalSave').addEventListener('click', function() {
        var mcId = document.getElementById('modalCourse').value;
        var crId = document.getElementById('modalClassroom').value;
        var ssId = document.getElementById('modalSessionId').value;

        if (!mcId || !crId || !ssId) {
            toastr.error('يرجى ملء جميع الحقول');
            return;
        }

        var btn = this;
        btn.disabled = true;

        var formData = new FormData();
        formData.append('csrf_token', CSRF);
        formData.append('member_course_id', mcId);
        formData.append('classroom_id', crId);
        formData.append('session_id', ssId);

        fetch(BASE + '/grid-scheduling', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            if (data.success) {
                toastr.success(data.message);
                $('#addEntryModal').modal('hide');
                loadGrid();
            } else {
                toastr.error(data.message || 'خطأ');
            }
        })
        .catch(function() {
            btn.disabled = false;
            toastr.error('خطأ في الاتصال');
        });
    });

    function deleteGridEntry(timetableId) {
        var formData = new FormData();
        formData.append('csrf_token', CSRF);

        fetch(BASE + '/grid-scheduling/' + timetableId + '/delete', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                toastr.success(data.message);
                loadGrid();
            } else {
                toastr.error(data.message || 'خطأ');
            }
        });
    }

    // Apply filter button
    document.getElementById('btnApplyFilter').addEventListener('click', loadGrid);

    // Init Select2
    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap4', dir: 'rtl' });
    });

})();
</script>
<?php $this->endSection(); ?>

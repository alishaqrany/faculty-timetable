<?php
$this->layout('layouts.app');
$__page_title = 'التسكين الإداري';
$__breadcrumb = [['label' => 'التسكين الإداري']];
?>

<div class="alert alert-info d-flex align-items-center">
    <i class="fas fa-user-shield ml-2" style="font-size:1.3rem;"></i>
    <div>
        <strong>التسكين الإداري</strong> — يمكنك تسكين الجدول نيابة عن أي عضو هيئة تدريس.
        <?php if (!$isAdmin): ?>
            <span class="text-muted d-block">عرض أعضاء قسمك فقط.</span>
        <?php endif; ?>
    </div>
</div>

<!-- Filters -->
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter ml-1"></i> اختيار العضو</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <?php if ($isAdmin): ?>
            <div class="col-md-4">
                <div class="form-group">
                    <label>القسم</label>
                    <select id="deptFilter" class="form-control select2">
                        <option value="">-- جميع الأقسام --</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept['department_id'] ?>"><?= e($dept['department_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?php endif; ?>
            <div class="col-md-<?= $isAdmin ? '4' : '6' ?>">
                <div class="form-group">
                    <label>عضو هيئة التدريس <span class="text-danger">*</span></label>
                    <select id="memberSelect" class="form-control select2">
                        <option value="">-- اختر العضو --</option>
                        <?php foreach ($members as $m): ?>
                            <option value="<?= $m['member_id'] ?>" data-dept="<?= $m['department_id'] ?? '' ?>">
                                <?= e($m['member_name']) ?> <?= !empty($m['department_name']) ? '— ' . e($m['department_name']) : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-<?= $isAdmin ? '4' : '6' ?> d-flex align-items-end">
                <div class="form-group mb-0">
                    <span id="memberStatus" class="text-muted"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Entry Form (hidden until member selected) -->
<div class="card card-primary card-outline" id="addEntryCard" style="display:none;">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-plus ml-1"></i> إضافة محاضرة لـ <span id="selectedMemberName" class="text-primary"></span></h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>المقرر / الشعبة <span class="text-danger">*</span></label>
                    <select id="courseSelect" class="form-control select2" required>
                        <option value="">-- اختر المقرر --</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>القاعة <span class="text-danger">*</span></label>
                    <select id="classroomSelect" class="form-control select2" required>
                        <option value="">-- اختر القاعة --</option>
                        <?php foreach ($classrooms as $cr): ?>
                            <option value="<?= $cr['classroom_id'] ?>"><?= e($cr['classroom_name']) ?> (<?= e($cr['classroom_type'] ?? '') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>الفترة <span class="text-danger">*</span></label>
                    <select id="sessionSelect" class="form-control select2" required>
                        <option value="">-- اختر الفترة --</option>
                        <?php foreach ($sessions as $sess): ?>
                            <option value="<?= $sess['session_id'] ?>"><?= e($sess['day']) ?> — <?= e($sess['session_name'] ?? '') ?> (<?= $sess['start_time'] ?>-<?= $sess['end_time'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <button type="button" id="btnAddEntry" class="btn btn-primary">
            <i class="fas fa-save ml-1"></i> إضافة للجدول
        </button>
        <span id="addEntrySpinner" class="d-none mr-2">
            <span class="spinner-border spinner-border-sm"></span> جاري الإضافة...
        </span>
        <span id="addEntryMsg" class="mr-2"></span>
    </div>
</div>

<!-- Member's Entries Table -->
<div class="card" id="entriesCard" style="display:none;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list ml-1"></i> محاضرات <span id="entriesMemberName" class="text-primary"></span>
            (<span id="entriesCount">0</span>)
        </h3>
    </div>
    <div class="card-body table-responsive p-0" id="entriesContainer">
        <p class="text-center text-muted p-4">اختر عضو هيئة تدريس لعرض محاضراته</p>
    </div>
</div>

<?php $this->section('scripts'); ?>
<script>
(function() {
    'use strict';

    var BASE = <?= json_encode(rtrim(url(''), '/')) ?>;
    var CSRF = <?= json_encode(csrf_token()) ?>;

    var deptFilter = document.getElementById('deptFilter');
    var memberSelect = document.getElementById('memberSelect');
    var addEntryCard = document.getElementById('addEntryCard');
    var entriesCard = document.getElementById('entriesCard');
    var courseSelect = document.getElementById('courseSelect');
    var classroomSelect = document.getElementById('classroomSelect');
    var sessionSelect = document.getElementById('sessionSelect');
    var currentMemberId = null;

    function esc(str) {
        if (!str) return '';
        var d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    // Department filter
    if (deptFilter) {
        $(deptFilter).on('change', function() {
            var deptId = this.value;
            var $ms = $(memberSelect);
            $ms.find('option').each(function() {
                if (!this.value) return;
                var optDept = $(this).data('dept');
                if (!deptId || String(optDept) === String(deptId)) {
                    $(this).show().prop('disabled', false);
                } else {
                    $(this).hide().prop('disabled', true);
                }
            });
            $ms.val('').trigger('change.select2');
        });
    }

    // Member selection
    $(memberSelect).on('change', function() {
        currentMemberId = this.value;
        if (!currentMemberId) {
            addEntryCard.style.display = 'none';
            entriesCard.style.display = 'none';
            return;
        }

        var memberName = $(this).find('option:selected').text().trim();
        document.getElementById('selectedMemberName').textContent = memberName;
        document.getElementById('entriesMemberName').textContent = memberName;

        addEntryCard.style.display = '';
        entriesCard.style.display = '';

        loadMemberCourses(currentMemberId);
        loadMemberEntries(currentMemberId);
    });

    function loadMemberCourses(memberId) {
        var hasS2 = $(courseSelect).hasClass('select2-hidden-accessible');
        if (hasS2) $(courseSelect).select2('destroy');

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
            $(courseSelect).select2({ theme: 'bootstrap4', dir: 'rtl', placeholder: '-- اختر المقرر --' });
        });
    }

    function loadMemberEntries(memberId) {
        var container = document.getElementById('entriesContainer');
        container.innerHTML = '<div class="text-center p-4"><span class="spinner-border text-primary"></span></div>';

        fetch(BASE + '/admin-scheduling/member-entries/' + memberId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) {
                container.innerHTML = '<p class="text-center text-danger p-4">' + esc(data.message || 'خطأ') + '</p>';
                return;
            }

            document.getElementById('entriesCount').textContent = data.entries.length;

            if (data.entries.length === 0) {
                container.innerHTML = '<p class="text-center text-muted p-4">لا توجد محاضرات مسكّنة لهذا العضو</p>';
                return;
            }

            var html = '<table class="table table-hover table-striped"><thead><tr>';
            html += '<th>#</th><th>المقرر</th><th>الشعبة</th><th>القاعة</th><th>اليوم</th><th>الفترة</th><th>إجراءات</th>';
            html += '</tr></thead><tbody>';
            data.entries.forEach(function(e, i) {
                html += '<tr>';
                html += '<td>' + (i + 1) + '</td>';
                html += '<td>' + esc(e.subject_name) + '</td>';
                html += '<td>' + esc(e.section_name) + '</td>';
                html += '<td>' + esc(e.classroom_name) + '</td>';
                html += '<td>' + esc(e.day) + '</td>';
                html += '<td>' + esc(e.start_time) + ' - ' + esc(e.end_time) + '</td>';
                html += '<td><button class="btn btn-danger btn-xs btn-del-entry" data-id="' + e.timetable_id + '"><i class="fas fa-trash"></i></button></td>';
                html += '</tr>';
            });
            html += '</tbody></table>';
            container.innerHTML = html;

            // Bind delete
            container.querySelectorAll('.btn-del-entry').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var tid = btn.getAttribute('data-id');
                    Swal.fire({
                        title: 'تأكيد الحذف',
                        text: 'هل تريد حذف هذه المحاضرة؟',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'حذف',
                        cancelButtonText: 'إلغاء'
                    }).then(function(result) {
                        if (result.isConfirmed) deleteEntry(tid);
                    });
                });
            });
        });
    }

    // Add entry
    document.getElementById('btnAddEntry').addEventListener('click', function() {
        var mcId = courseSelect.value;
        var crId = classroomSelect.value;
        var ssId = sessionSelect.value;

        if (!mcId || !crId || !ssId) {
            toastr.error('يرجى ملء جميع الحقول');
            return;
        }

        var btn = this;
        var spinner = document.getElementById('addEntrySpinner');
        var msgEl = document.getElementById('addEntryMsg');
        btn.disabled = true;
        spinner.classList.remove('d-none');
        msgEl.innerHTML = '';

        var formData = new FormData();
        formData.append('csrf_token', CSRF);
        formData.append('member_course_id', mcId);
        formData.append('classroom_id', crId);
        formData.append('session_id', ssId);

        fetch(BASE + '/admin-scheduling', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            spinner.classList.add('d-none');

            if (data.success) {
                toastr.success(data.message);
                // Reset selects
                $(courseSelect).val('').trigger('change.select2');
                $(classroomSelect).val('').trigger('change.select2');
                $(sessionSelect).val('').trigger('change.select2');
                // Reload entries
                if (currentMemberId) loadMemberEntries(currentMemberId);
            } else {
                toastr.error(data.message || 'حدث خطأ');
            }
        })
        .catch(function(err) {
            btn.disabled = false;
            spinner.classList.add('d-none');
            toastr.error('خطأ في الاتصال');
        });
    });

    function deleteEntry(timetableId) {
        var formData = new FormData();
        formData.append('csrf_token', CSRF);

        fetch(BASE + '/admin-scheduling/' + timetableId + '/delete', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                toastr.success(data.message);
                if (currentMemberId) loadMemberEntries(currentMemberId);
            } else {
                toastr.error(data.message || 'خطأ');
            }
        });
    }

    // Init Select2
    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap4', dir: 'rtl' });
    });

})();
</script>
<?php $this->endSection(); ?>

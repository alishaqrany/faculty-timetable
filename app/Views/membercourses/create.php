<?php
$this->layout('layouts.app');
$__page_title = 'إضافة تكليف تدريس';
$__breadcrumb = [['label' => 'تكليفات التدريس', 'url' => '/member-courses'], ['label' => 'إضافة']];
$selectedAssignmentType = old('assignment_type', 'نظري');
$selectedPracticalScope = old('practical_scope', 'section');
$oldDivisionIdsRaw = old('division_id');
$oldDivisionIds = is_array($oldDivisionIdsRaw)
    ? array_map('intval', $oldDivisionIdsRaw)
    : ((string)$oldDivisionIdsRaw !== '' ? [(int)$oldDivisionIdsRaw] : []);
?>

<div class="row"><div class="col-md-8">
    <div class="card card-primary">
        <div class="card-header"><h3 class="card-title">بيانات التكليف الجديد</h3></div>
        <form method="POST" action="<?= url('/member-courses') ?>">
            <?= csrf_field() ?>
            <div class="card-body">
                <div class="form-group">
                    <label>عضو هيئة التدريس <span class="text-danger">*</span></label>
                    <select name="member_id" class="form-control select2" required>
                        <option value="">-- اختر --</option>
                        <?php foreach ($members as $m): ?>
                            <option value="<?= $m['member_id'] ?>" <?= old('member_id') == $m['member_id'] ? 'selected' : '' ?>><?= e($m['member_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>المقرر <span class="text-danger">*</span></label>
                    <select name="subject_id" id="subjectSelect" class="form-control select2" required>
                        <option value="">-- اختر --</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['subject_id'] ?>"
                                    data-subject-type="<?= e($s['subject_type'] ?? 'نظري') ?>"
                                    data-department-id="<?= (int)($s['department_id'] ?? 0) ?>"
                                    data-level-id="<?= (int)($s['level_id'] ?? 0) ?>"
                                    <?= old('subject_id') == $s['subject_id'] ? 'selected' : '' ?>>
                                <?= e($s['subject_name']) ?> (<?= e($s['department_name']) ?>) 
                                <?php if (!empty($s['subject_type'])): ?>[<?= e($s['subject_type']) ?>]<?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Assignment Type Selection -->
                <div class="form-group">
                    <label>نوع التكليف <span class="text-danger">*</span></label>
                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                        <label id="theoryLabel" class="btn btn-outline-info <?= $selectedAssignmentType === 'نظري' ? 'active' : '' ?>" style="flex:1">
                            <input id="theoryRadio" type="radio" name="assignment_type" value="نظري" <?= $selectedAssignmentType === 'نظري' ? 'checked' : '' ?>>
                            <i class="fas fa-chalkboard-teacher mr-1"></i> نظري (محاضرة)
                        </label>
                        <label id="practicalLabel" class="btn btn-outline-warning <?= $selectedAssignmentType === 'عملي' ? 'active' : '' ?>" style="flex:1">
                            <input id="practicalRadio" type="radio" name="assignment_type" value="عملي" <?= $selectedAssignmentType === 'عملي' ? 'checked' : '' ?>>
                            <i class="fas fa-flask mr-1"></i> عملي (معمل)
                        </label>
                    </div>
                    <small class="text-muted">النظري يُجدول على شعبة كاملة، العملي يُجدول على سكشن أو شعبة</small>
                    <small id="assignmentTypeHelp" class="form-text text-muted"></small>
                </div>
                
                <!-- Division Selection for Theory -->
                <div class="form-group" id="divisionGroup">
                    <label>الشعبة / الشعب <span class="text-danger">*</span></label>
                    <select name="division_id[]" id="divisionSelect" class="form-control select2" multiple="multiple" data-placeholder="-- اختر شعبة أو أكثر --">
                        <?php foreach ($divisions ?? [] as $div): ?>
                            <option value="<?= $div['division_id'] ?>" <?= in_array((int)$div['division_id'], $oldDivisionIds, true) ? 'selected' : '' ?>>
                                <?= e($div['division_name']) ?> — <?= e($div['department_name']) ?> / <?= e($div['level_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">للمحاضرات النظرية - يمكنك اختيار أكثر من شعبة (محاضرة مشتركة للدفعة)</small>
                </div>
                
                <!-- Practical Scope Selection -->
                <div class="form-group" id="practicalScopeGroup" style="display:none;">
                    <label>نطاق العملي</label>
                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                        <label class="btn btn-outline-secondary <?= $selectedPracticalScope === 'section' ? 'active' : '' ?>" style="flex:1">
                            <input type="radio" name="practical_scope" value="section" <?= $selectedPracticalScope === 'section' ? 'checked' : '' ?>>
                            <i class="fas fa-layer-group mr-1"></i> سكشن محدد
                        </label>
                        <label class="btn btn-outline-secondary <?= $selectedPracticalScope === 'division' ? 'active' : '' ?>" style="flex:1">
                            <input type="radio" name="practical_scope" value="division" <?= $selectedPracticalScope === 'division' ? 'checked' : '' ?>>
                            <i class="fas fa-users mr-1"></i> الشعبة كاملة
                        </label>
                    </div>
                    <small class="text-muted">اختر "الشعبة كاملة" إذا كان العملي لا يُقسّم إلى سكاشن</small>
                </div>

                <!-- Section Selection for Practical -->
                <div class="form-group" id="sectionGroup" style="display:none;">
                    <label>السكشن (مجموعة العملي) <span class="text-danger">*</span></label>
                    <select name="section_id" id="sectionSelect" class="form-control select2" disabled>
                        <option value="">-- اختر سكشن --</option>
                        <?php foreach ($sections ?? [] as $sec): ?>
                            <option value="<?= $sec['section_id'] ?>" <?= old('section_id') == $sec['section_id'] ? 'selected' : '' ?>>
                                <?= e($sec['section_name']) ?> 
                                (<?= e($sec['division_name'] ?? 'بدون شعبة') ?>) — 
                                <?= e($sec['department_name']) ?> / <?= e($sec['level_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">للمعامل - مجموعة أصغر من الشعبة</small>
                </div>

                <!-- Division Selection for Practical (whole division) -->
                <div class="form-group" id="practicalDivisionGroup" style="display:none;">
                    <label>الشعبة (للعملي) <span class="text-danger">*</span></label>
                    <select name="practical_division_id" id="practicalDivisionSelect" class="form-control select2" disabled>
                        <option value="">-- اختر شعبة --</option>
                        <?php foreach ($divisions ?? [] as $div): ?>
                            <option value="<?= $div['division_id'] ?>" <?= old('practical_division_id') == $div['division_id'] ? 'selected' : '' ?>>
                                <?= e($div['division_name']) ?> — <?= e($div['department_name']) ?> / <?= e($div['level_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">الشعبة كاملة ستحضر العملي معًا بدون تقسيم سكاشن</small>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                <a href="<?= url('/member-courses') ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const assignmentRadios = document.querySelectorAll('input[name="assignment_type"]');
    const practicalScopeRadios = document.querySelectorAll('input[name="practical_scope"]');
    const subjectSelect = document.getElementById('subjectSelect');
    const theoryRadio = document.getElementById('theoryRadio');
    const practicalRadio = document.getElementById('practicalRadio');
    const theoryLabel = document.getElementById('theoryLabel');
    const practicalLabel = document.getElementById('practicalLabel');
    const assignmentTypeHelp = document.getElementById('assignmentTypeHelp');
    const divisionGroup = document.getElementById('divisionGroup');
    const sectionGroup = document.getElementById('sectionGroup');
    const practicalScopeGroup = document.getElementById('practicalScopeGroup');
    const practicalDivisionGroup = document.getElementById('practicalDivisionGroup');
    const divisionSelect = document.getElementById('divisionSelect');
    const sectionSelect = document.getElementById('sectionSelect');
    const practicalDivisionSelect = document.getElementById('practicalDivisionSelect');
    
    function toggleSections() {
        const selectedType = document.querySelector('input[name="assignment_type"]:checked').value;
        
        if (selectedType === 'نظري') {
            divisionGroup.style.display = 'block';
            practicalScopeGroup.style.display = 'none';
            sectionGroup.style.display = 'none';
            practicalDivisionGroup.style.display = 'none';
            divisionSelect.disabled = false;
            sectionSelect.disabled = true;
            practicalDivisionSelect.disabled = true;
        } else {
            divisionGroup.style.display = 'none';
            practicalScopeGroup.style.display = 'block';
            divisionSelect.disabled = true;
            togglePracticalScope();
        }
    }

    function syncAssignmentButtons() {
        theoryLabel.classList.toggle('active', theoryRadio.checked);
        practicalLabel.classList.toggle('active', practicalRadio.checked);
    }

    function enforceAssignmentBySubjectType() {
        const option = subjectSelect.options[subjectSelect.selectedIndex];
        const subjectType = option ? (option.dataset.subjectType || '') : '';

        theoryRadio.disabled = false;
        practicalRadio.disabled = false;
        theoryLabel.classList.remove('disabled');
        practicalLabel.classList.remove('disabled');

        if (subjectType === 'نظري') {
            practicalRadio.disabled = true;
            practicalLabel.classList.add('disabled');
            if (practicalRadio.checked) {
                theoryRadio.checked = true;
            }
            assignmentTypeHelp.textContent = 'هذا المقرر نظري فقط، لذلك التكليف العملي غير متاح.';
        } else if (subjectType === 'عملي') {
            theoryRadio.disabled = true;
            theoryLabel.classList.add('disabled');
            if (theoryRadio.checked) {
                practicalRadio.checked = true;
            }
            assignmentTypeHelp.textContent = 'هذا المقرر عملي فقط، لذلك التكليف النظري غير متاح.';
        } else {
            assignmentTypeHelp.textContent = 'يمكن اختيار نظري أو عملي حسب خطة المقرر.';
        }

        syncAssignmentButtons();
        toggleSections();
    }
    
    function togglePracticalScope() {
        const scope = document.querySelector('input[name="practical_scope"]:checked').value;
        if (scope === 'section') {
            sectionGroup.style.display = 'block';
            practicalDivisionGroup.style.display = 'none';
            sectionSelect.disabled = false;
            practicalDivisionSelect.disabled = true;
        } else {
            sectionGroup.style.display = 'none';
            practicalDivisionGroup.style.display = 'block';
            sectionSelect.disabled = true;
            practicalDivisionSelect.disabled = false;
        }
    }
    
    assignmentRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            syncAssignmentButtons();
            toggleSections();
        });
    });
    practicalScopeRadios.forEach(radio => {
        radio.addEventListener('change', togglePracticalScope);
    });

    subjectSelect.addEventListener('change', enforceAssignmentBySubjectType);
    
    enforceAssignmentBySubjectType();
});
</script>

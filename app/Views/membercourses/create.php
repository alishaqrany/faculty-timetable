<?php
$this->layout('layouts.app');
$__page_title = 'إضافة تكليف تدريس';
$__breadcrumb = [['label' => 'تكليفات التدريس', 'url' => '/member-courses'], ['label' => 'إضافة']];
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
                    <select name="subject_id" id="subject_id" class="form-control select2" required>
                        <option value="">-- اختر --</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['subject_id'] ?>" data-subject-type="<?= e($s['subject_type'] ?? 'نظري') ?>" <?= old('subject_id') == $s['subject_id'] ? 'selected' : '' ?>><?= e($s['subject_name']) ?> (<?= e($s['department_name']) ?>) - <?= e($s['subject_type'] ?? 'نظري') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>المجموعة (شعبة/سكشن) <span class="text-danger">*</span></label>
                    <select name="section_id" id="section_id" class="form-control select2" required>
                        <option value="">-- اختر --</option>
                        <?php foreach ($sections as $sec): ?>
                            <option value="<?= $sec['section_id'] ?>" data-section-type="<?= e($sec['section_type'] ?? 'شعبة') ?>" <?= old('section_id') == $sec['section_id'] ? 'selected' : '' ?>><?= e($sec['section_name']) ?> [<?= e($sec['section_type'] ?? 'شعبة') ?>]<?= !empty($sec['parent_section_name']) ? ' - تابع لـ ' . e($sec['parent_section_name']) : '' ?> — <?= e($sec['department_name']) ?> / <?= e($sec['level_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted" id="section_hint">المقرر النظري يرتبط بشعبة، والعملي يرتبط بسكشن.</small>
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
document.addEventListener('DOMContentLoaded', function () {
    var subjectSelect = document.getElementById('subject_id');
    var sectionSelect = document.getElementById('section_id');
    var hint = document.getElementById('section_hint');

    function getSubjectType() {
        var subjectOption = subjectSelect.options[subjectSelect.selectedIndex];
        if (!subjectOption) return '';
        return (subjectOption.getAttribute('data-subject-type') || '').trim();
    }

    function syncSectionOptions() {
        var subjectType = getSubjectType();
        var allowedType = '';

        if (subjectType === 'نظري') {
            allowedType = 'شعبة';
            hint.textContent = 'هذا مقرر نظري: اختر شعبة.';
        } else if (subjectType === 'عملي') {
            allowedType = 'سكشن';
            hint.textContent = 'هذا مقرر عملي: اختر سكشن.';
        } else {
            hint.textContent = 'هذا مقرر نظري وعملي: يمكن الاختيار من شعبة أو سكشن.';
        }

        for (var i = 0; i < sectionSelect.options.length; i++) {
            var option = sectionSelect.options[i];
            if (!option.value) continue;

            var secType = option.getAttribute('data-section-type') || 'شعبة';
            option.hidden = !!allowedType && secType !== allowedType;
        }

        var selectedOption = sectionSelect.options[sectionSelect.selectedIndex];
        if (selectedOption && selectedOption.hidden) {
            sectionSelect.value = '';
        }
    }

    subjectSelect.addEventListener('change', syncSectionOptions);
    syncSectionOptions();
});
</script>

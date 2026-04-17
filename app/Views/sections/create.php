<?php
$this->layout('layouts.app');
$__page_title = 'إضافة شعبة';
$__breadcrumb = [['label' => 'الشُعب', 'url' => '/sections'], ['label' => 'إضافة']];
?>

<div class="row"><div class="col-md-8">
    <div class="card card-primary">
        <div class="card-header"><h3 class="card-title">بيانات الشعبة الجديدة</h3></div>
        <form method="POST" action="<?= url('/sections') ?>">
            <?= csrf_field() ?>
            <div class="card-body">
                <div class="form-group">
                    <label>اسم المجموعة <span class="text-danger">*</span></label>
                    <input type="text" name="section_name" class="form-control" value="<?= e(old('section_name')) ?>" required>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>النوع <span class="text-danger">*</span></label>
                            <select name="section_type" id="section_type" class="form-control" required>
                                <option value="شعبة" <?= old('section_type', 'شعبة') === 'شعبة' ? 'selected' : '' ?>>شعبة (للنظري)</option>
                                <option value="سكشن" <?= old('section_type') === 'سكشن' ? 'selected' : '' ?>>سكشن (للعملي)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6" id="parent_wrapper" style="display:none;">
                        <div class="form-group">
                            <label>الشعبة الأب <span class="text-danger">*</span></label>
                            <select name="parent_section_id" id="parent_section_id" class="form-control select2">
                                <option value="">-- اختر --</option>
                                <?php foreach ($parentSections as $parent): ?>
                                    <option value="<?= $parent['section_id'] ?>" data-department-id="<?= $parent['department_id'] ?>" data-level-id="<?= $parent['level_id'] ?>" <?= old('parent_section_id') == $parent['section_id'] ? 'selected' : '' ?>>
                                        <?= e($parent['section_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>القسم <span class="text-danger">*</span></label>
                            <select name="department_id" id="department_id" class="form-control select2" required>
                                <option value="">-- اختر --</option>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?= $d['department_id'] ?>" <?= old('department_id') == $d['department_id'] ? 'selected' : '' ?>><?= e($d['department_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>المستوى <span class="text-danger">*</span></label>
                            <select name="level_id" id="level_id" class="form-control select2" required>
                                <option value="">-- اختر --</option>
                                <?php foreach ($levels as $l): ?>
                                    <option value="<?= $l['level_id'] ?>" <?= old('level_id') == $l['level_id'] ? 'selected' : '' ?>><?= e($l['level_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>السعة</label>
                    <input type="number" name="capacity" class="form-control" value="<?= e(old('capacity', '30')) ?>" min="1">
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                <a href="<?= url('/sections') ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var typeSelect = document.getElementById('section_type');
    var parentWrapper = document.getElementById('parent_wrapper');
    var parentSelect = document.getElementById('parent_section_id');
    var departmentSelect = document.getElementById('department_id');
    var levelSelect = document.getElementById('level_id');

    function applyParentContext() {
        var option = parentSelect.options[parentSelect.selectedIndex];
        if (!option) return;

        var dep = option.getAttribute('data-department-id');
        var lvl = option.getAttribute('data-level-id');

        if (dep) departmentSelect.value = dep;
        if (lvl) levelSelect.value = lvl;
    }

    function syncTypeState() {
        var isLabSection = typeSelect.value === 'سكشن';
        parentWrapper.style.display = isLabSection ? '' : 'none';
        parentSelect.required = isLabSection;

        if (isLabSection) {
            applyParentContext();
        } else {
            parentSelect.value = '';
        }
    }

    typeSelect.addEventListener('change', syncTypeState);
    parentSelect.addEventListener('change', applyParentContext);
    syncTypeState();
});
</script>

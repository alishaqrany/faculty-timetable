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
                    <select name="subject_id" class="form-control select2" required>
                        <option value="">-- اختر --</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['subject_id'] ?>" <?= old('subject_id') == $s['subject_id'] ? 'selected' : '' ?>>
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
                        <label class="btn btn-outline-info active" style="flex:1">
                            <input type="radio" name="assignment_type" value="نظري" <?= old('assignment_type', 'نظري') === 'نظري' ? 'checked' : '' ?>> 
                            <i class="fas fa-chalkboard-teacher mr-1"></i> نظري (محاضرة)
                        </label>
                        <label class="btn btn-outline-warning" style="flex:1">
                            <input type="radio" name="assignment_type" value="عملي" <?= old('assignment_type') === 'عملي' ? 'checked' : '' ?>> 
                            <i class="fas fa-flask mr-1"></i> عملي (معمل)
                        </label>
                    </div>
                    <small class="text-muted">النظري يُجدول على شعبة كاملة، العملي يُجدول على سكشن</small>
                </div>
                
                <!-- Division Selection for Theory -->
                <div class="form-group" id="divisionGroup">
                    <label>الشعبة / الشعب <span class="text-danger">*</span></label>
                    <select name="division_id[]" id="divisionSelect" class="form-control select2" multiple="multiple" data-placeholder="-- اختر شعبة أو أكثر --">
                        <?php foreach ($divisions ?? [] as $div): ?>
                            <option value="<?= $div['division_id'] ?>" <?= (is_array(old('division_id')) && in_array($div['division_id'], old('division_id'))) ? 'selected' : (old('division_id') == $div['division_id'] ? 'selected' : '') ?>>
                                <?= e($div['division_name']) ?> — <?= e($div['department_name']) ?> / <?= e($div['level_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">للمحاضرات النظرية - يمكنك اختيار أكثر من شعبة (محاضرة مشتركة للدفعة)</small>
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
    const divisionGroup = document.getElementById('divisionGroup');
    const sectionGroup = document.getElementById('sectionGroup');
    const divisionSelect = document.getElementById('divisionSelect');
    const sectionSelect = document.getElementById('sectionSelect');
    
    function toggleSections() {
        const selectedType = document.querySelector('input[name="assignment_type"]:checked').value;
        
        if (selectedType === 'نظري') {
            divisionGroup.style.display = 'block';
            sectionGroup.style.display = 'none';
            divisionSelect.disabled = false;
            sectionSelect.disabled = true;
        } else {
            divisionGroup.style.display = 'none';
            sectionGroup.style.display = 'block';
            divisionSelect.disabled = true;
            sectionSelect.disabled = false;
        }
    }
    
    assignmentRadios.forEach(radio => {
        radio.addEventListener('change', toggleSections);
    });
    
    toggleSections(); // Initial state
});
</script>

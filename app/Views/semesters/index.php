<?php
$this->layout('layouts.app');
$__page_title = 'الفصول الدراسية';
$__breadcrumb = [['label' => 'الفصول الدراسية']];
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">قائمة الفصول الدراسية</h3>
        <?php if (can('semesters.manage')): ?>
        <div class="card-tools">
            <button type="button" class="btn btn-success btn-sm ml-1" data-toggle="modal" data-target="#generateSemestersModal">
                <i class="fas fa-magic ml-1"></i> توليد فصول لسنة
            </button>
            <a href="<?= url('/semesters/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus ml-1"></i> إضافة فصل دراسي
            </a>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-hover datatable">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th>اسم الفصل</th>
                    <th>السنة الأكاديمية</th>
                    <th>تاريخ البداية</th>
                    <th>تاريخ النهاية</th>
                    <th>الحالة</th>
                    <th width="15%">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($semesters as $i => $s): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                        <?= e($s['semester_name']) ?>
                        <?php if ($s['is_current']): ?>
                            <span class="badge badge-success mr-1">الحالي</span>
                        <?php endif; ?>
                    </td>
                    <td><?= e($s['year_name']) ?></td>
                    <td><?= e($s['start_date']) ?></td>
                    <td><?= e($s['end_date']) ?></td>
                    <td>
                        <?php if ($s['is_active'] ?? 1): ?>
                            <span class="badge badge-success">مفعّل</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">معطّل</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (can('semesters.manage')): ?>
                            <?php if (!$s['is_current']): ?>
                            <form method="POST" action="<?= url("/semesters/{$s['id']}/set-current") ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-success btn-xs" title="تعيين كحالي">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                            <a href="<?= url("/semesters/{$s['id']}/edit") ?>" class="btn btn-warning btn-xs">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="<?= url("/semesters/{$s['id']}/delete") ?>" class="d-inline btn-delete">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-xs"><i class="fas fa-trash"></i></button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (can('semesters.manage')): ?>
<div class="modal fade" id="generateSemestersModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="<?= url('/semesters/generate-for-year') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">توليد الفصول الدراسية</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>السنة المستهدفة <span class="text-danger">*</span></label>
                        <select name="academic_year_id" class="form-control" required>
                            <option value="">-- اختر السنة --</option>
                            <?php foreach (($years ?? []) as $y): ?>
                            <option value="<?= (int)$y['id'] ?>"><?= e($y['year_name']) ?><?= !empty($y['is_current']) ? ' (الحالية)' : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>طريقة التوليد <span class="text-danger">*</span></label>
                        <select name="mode" id="semesterGenerateMode" class="form-control" required>
                            <option value="two">فصلين افتراضيين (أول/ثان)</option>
                            <option value="copy">نسخ نفس فصول سنة أخرى</option>
                        </select>
                    </div>

                    <div class="form-group d-none" id="sourceYearWrapper">
                        <label>سنة المصدر للنسخ <span class="text-danger">*</span></label>
                        <select name="source_year_id" id="sourceYearSelect" class="form-control">
                            <option value="">-- اختر سنة المصدر --</option>
                            <?php foreach (($years ?? []) as $y): ?>
                            <option value="<?= (int)$y['id'] ?>"><?= e($y['year_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" id="replaceExisting" name="replace_existing" value="1">
                        <label class="custom-control-label" for="replaceExisting">استبدال الفصول الحالية في السنة المستهدفة</label>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setFirstCurrent" name="set_first_current" value="1" checked>
                        <label class="custom-control-label" for="setFirstCurrent">تعيين أول فصل مُنشأ كفصل حالي</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">تنفيذ التوليد</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    var modeSelect = document.getElementById('semesterGenerateMode');
    var sourceWrapper = document.getElementById('sourceYearWrapper');
    var sourceSelect = document.getElementById('sourceYearSelect');
    if (!modeSelect || !sourceWrapper || !sourceSelect) {
        return;
    }

    var syncVisibility = function () {
        var isCopy = modeSelect.value === 'copy';
        sourceWrapper.classList.toggle('d-none', !isCopy);
        sourceSelect.required = isCopy;
    };

    modeSelect.addEventListener('change', syncVisibility);
    syncVisibility();
})();
</script>
<?php endif; ?>

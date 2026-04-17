<?php
$this->layout('layouts.app');
$__page_title = 'إدارة السكاشن (مجموعات العملي)';
$__breadcrumb = [['label' => 'السكاشن']];
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">قائمة السكاشن</h3>
        <?php if (can('sections.create')): ?>
        <div class="card-tools">
            <button type="button" class="btn btn-success btn-sm ml-1" data-toggle="modal" data-target="#autoSectionsModal">
                <i class="fas fa-magic ml-1"></i> إنشاء وتوزيع تلقائي
            </button>
            <a href="<?= url('/sections/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus ml-1"></i> إضافة سكشن
            </a>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>السكشن:</strong> هو مجموعة عملي داخل الشعبة - لتقسيم الطلاب في المعامل.
            <br>
            <small>كل شعبة يمكن أن تحتوي على عدة سكاشن (سكشن 1، سكشن 2، ...)</small>
        </div>
        
        <?php if (empty($groupedSections)): ?>
        <div class="alert alert-warning">
            لا توجد سكاشن مسجلة. 
            <a href="<?= url('/divisions') ?>">أضف شعبة أولاً</a> ثم أضف سكاشن لها.
        </div>
        <?php else: ?>
        
        <?php foreach ($groupedSections as $group): ?>
        <div class="card card-outline card-secondary mb-3">
            <div class="card-header py-2">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users text-primary"></i>
                    <?= e($group['division_name']) ?>
                    <small class="text-muted mr-2">
                        (<?= e($group['department_name'] ?? '') ?> - <?= e($group['level_name'] ?? '') ?>)
                    </small>
                </h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width:30px"></th>
                            <th>اسم السكشن</th>
                            <th>السعة</th>
                            <th>الحالة</th>
                            <th style="width:100px">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($group['sections'] as $sec): ?>
                        <tr>
                            <td><i class="fas fa-layer-group text-secondary"></i></td>
                            <td><?= e($sec['section_name']) ?></td>
                            <td><?= (int)($sec['capacity'] ?? 0) ?></td>
                            <td>
                                <span class="badge badge-<?= $sec['is_active'] ? 'success' : 'secondary' ?>">
                                    <?= $sec['is_active'] ? 'فعّال' : 'معطّل' ?>
                                </span>
                            </td>
                            <td>
                                <?php if (can('sections.edit')): ?>
                                <a href="<?= url("/sections/{$sec['section_id']}/edit") ?>" class="btn btn-warning btn-xs">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (can('sections.delete')): ?>
                                <form method="POST" action="<?= url("/sections/{$sec['section_id']}/delete") ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-danger btn-xs btn-delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php endif; ?>
    </div>
</div>

<?php if (can('sections.create')): ?>
<div class="modal fade" id="autoSectionsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="<?= url('/sections/generate-auto') ?>" id="autoSectionsForm">
                <?= csrf_field() ?>
                <div class="modal-header bg-success">
                    <h5 class="modal-title"><i class="fas fa-magic ml-1"></i> إنشاء وتوزيع السكاشن تلقائياً</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2">
                        <strong>التسمية الافتراضية:</strong> سيتم الإنشاء بصيغة <code>سكشن 1</code>، <code>سكشن 2</code>...
                    </div>

                    <div class="form-group">
                        <label>نطاق الإنشاء</label>
                        <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                            <label class="btn btn-outline-primary active" style="flex:1">
                                <input type="radio" name="target_type" value="division" checked> لشعبة واحدة
                            </label>
                            <label class="btn btn-outline-primary" style="flex:1">
                                <input type="radio" name="target_type" value="level"> لفرقة كاملة
                            </label>
                        </div>
                    </div>

                    <div id="divisionTargetGroup" class="form-group">
                        <label>اختر الشعبة <span class="text-danger">*</span></label>
                        <select name="division_id" id="autoDivisionId" class="form-control select2" required>
                            <option value="">-- اختر الشعبة --</option>
                            <?php foreach (($divisions ?? []) as $div): ?>
                                <option value="<?= $div['division_id'] ?>">
                                    <?= e($div['division_name']) ?> — <?= e($div['department_name']) ?> / <?= e($div['level_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="levelTargetGroup" style="display:none;">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>القسم <span class="text-danger">*</span></label>
                                <select name="department_id" id="autoDepartmentId" class="form-control select2" disabled>
                                    <option value="">-- اختر القسم --</option>
                                    <?php foreach (($departments ?? []) as $dep): ?>
                                        <option value="<?= $dep['department_id'] ?>"><?= e($dep['department_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>الفرقة <span class="text-danger">*</span></label>
                                <select name="level_id" id="autoLevelId" class="form-control select2" disabled>
                                    <option value="">-- اختر الفرقة --</option>
                                    <?php foreach (($levels ?? []) as $lvl): ?>
                                        <option value="<?= $lvl['level_id'] ?>"><?= e($lvl['level_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <small class="text-muted">سيتم إنشاء نفس عدد السكاشن لكل شعبة داخل الفرقة المختارة. وإذا لم توجد شعب سيتم إنشاء شعبة "عامة" تلقائياً.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>عدد السكاشن</label>
                            <input type="number" name="sections_count" class="form-control" value="2" min="1" max="30" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>سعة كل سكشن</label>
                            <input type="number" name="capacity" class="form-control" value="25" min="1" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>بداية الترقيم</label>
                            <input type="number" name="start_number" class="form-control" value="1" min="1" required>
                        </div>
                    </div>

                    <small id="distributionHelp" class="text-muted d-block">
                        سيتم إنشاء العدد المحدد للشعبة المختارة.
                    </small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-bolt ml-1"></i> تنفيذ الإنشاء التلقائي
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const targetRadios = document.querySelectorAll('input[name="target_type"]');
    const divisionTargetGroup = document.getElementById('divisionTargetGroup');
    const levelTargetGroup = document.getElementById('levelTargetGroup');
    const autoDivisionId = document.getElementById('autoDivisionId');
    const autoDepartmentId = document.getElementById('autoDepartmentId');
    const autoLevelId = document.getElementById('autoLevelId');
    const distributionHelp = document.getElementById('distributionHelp');

    function toggleTargetMode() {
        const selected = document.querySelector('input[name="target_type"]:checked').value;
        const isDivision = selected === 'division';

        divisionTargetGroup.style.display = isDivision ? 'block' : 'none';
        levelTargetGroup.style.display = isDivision ? 'none' : 'block';

        autoDivisionId.disabled = !isDivision;
        autoDivisionId.required = isDivision;

        autoDepartmentId.disabled = isDivision;
        autoDepartmentId.required = !isDivision;

        autoLevelId.disabled = isDivision;
        autoLevelId.required = !isDivision;

        distributionHelp.textContent = isDivision
            ? 'سيتم إنشاء العدد المحدد للشعبة المختارة.'
            : 'سيتم إنشاء العدد المحدد لكل شعبة داخل الفرقة المختارة.';
    }

    targetRadios.forEach(function (radio) {
        radio.addEventListener('change', toggleTargetMode);
    });

    toggleTargetMode();
});
</script>
<?php endif; ?>

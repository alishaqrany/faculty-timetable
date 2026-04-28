<?php
$this->layout('layouts.app');
$__page_title = 'إدارة الأولوية';
$__breadcrumb = [['label' => 'إدارة الأولوية']];
?>

<!-- Priority Status Banner -->
<div class="card card-outline <?= $state['mode'] === 'disabled' ? 'card-secondary' : 'card-success' ?>">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-sort-amount-up ml-1"></i> حالة نظام الأولوية</h3>
    </div>
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="info-box shadow-sm mb-0">
                    <span class="info-box-icon <?= $state['mode'] === 'disabled' ? 'bg-secondary' : 'bg-success' ?>">
                        <i class="fas <?= $state['mode'] === 'disabled' ? 'fa-pause-circle' : 'fa-play-circle' ?>"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">الوضع الحالي</span>
                        <span class="info-box-number"><?= e($state['mode_label']) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box shadow-sm mb-0">
                    <span class="info-box-icon bg-info">
                        <i class="fas fa-layer-group"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">التصنيف النشط</span>
                        <span class="info-box-number"><?= $state['active_category'] ? e($state['active_category']['category_name']) : 'غير محدد' ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box shadow-sm mb-0">
                    <span class="info-box-icon bg-warning">
                        <i class="fas fa-users"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">المجموعة الحالية</span>
                        <span class="info-box-number">
                            <?php if ($state['mode'] === 'parallel_dept'): ?>
                                تختلف حسب القسم
                            <?php else: ?>
                                <?= $state['current_group'] ? e($state['current_group']['group_name']) : 'غير محدد' ?>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($state['mode'] === 'sequential_dept' && $state['current_department']): ?>
        <div class="mt-3">
            <div class="info-box shadow-sm mb-0">
                <span class="info-box-icon bg-primary">
                    <i class="fas fa-building"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">القسم الحالي</span>
                    <span class="info-box-number"><?= e($state['current_department']['department_name']) ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <!-- Mode Selection -->
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-sliders-h ml-1"></i> تحديد وضع الأولوية</h3>
            </div>
            <form method="POST" action="<?= url('/priority/mode') ?>">
                <?= csrf_field() ?>
                <div class="card-body">
                    <?php foreach ($modes as $modeKey => $modeLabel): ?>
                    <div class="custom-control custom-radio mb-3">
                        <input class="custom-control-input" type="radio" name="priority_mode"
                               id="mode_<?= $modeKey ?>" value="<?= $modeKey ?>"
                               <?= $state['mode'] === $modeKey ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="mode_<?= $modeKey ?>">
                            <strong><?= e(explode(' — ', $modeLabel)[0]) ?></strong>
                            <?php if (count(explode(' — ', $modeLabel)) > 1): ?>
                                <br><small class="text-muted"><?= e(explode(' — ', $modeLabel)[1]) ?></small>
                            <?php endif; ?>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ الوضع</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Active Category & Advance -->
    <div class="col-md-6">
        <!-- Select Category -->
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list-alt ml-1"></i> التصنيف النشط</h3>
            </div>
            <form method="POST" action="<?= url('/priority/set-category') ?>">
                <?= csrf_field() ?>
                <div class="card-body">
                    <?php if (!empty($categories)): ?>
                    <div class="form-group">
                        <label>اختر التصنيف</label>
                        <select name="category_id" class="form-control select2">
                            <option value="">-- اختر تصنيف --</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($state['active_category'] && $state['active_category']['id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= e($cat['category_name']) ?> (<?= $cat['group_count'] ?> مجموعات)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php else: ?>
                        <p class="text-muted">لا توجد تصنيفات. <a href="<?= url('/priority/categories') ?>">أنشئ تصنيفاً</a></p>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-info"><i class="fas fa-check ml-1"></i> تعيين</button>
                </div>
            </form>
        </div>

        <!-- Advance Controls -->
        <?php if ($state['mode'] !== 'disabled'): ?>
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-step-forward ml-1"></i> التحكم في التقدم</h3>
            </div>
            <div class="card-body">
                <div class="btn-group-vertical w-100">
                    <form method="POST" action="<?= url('/priority/advance-group') ?>" class="mb-2">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-warning btn-block"
                                onclick="return confirm('هل تريد الانتقال للمجموعة التالية؟')">
                            <i class="fas fa-forward ml-1"></i> الانتقال للمجموعة التالية
                        </button>
                    </form>

                    <?php if ($state['mode'] === 'sequential_dept'): ?>
                    <form method="POST" action="<?= url('/priority/advance-dept') ?>" class="mb-2">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-warning btn-block"
                                onclick="return confirm('هل تريد الانتقال للقسم التالي؟')">
                            <i class="fas fa-building ml-1"></i> الانتقال للقسم التالي
                        </button>
                    </form>
                    <?php endif; ?>

                    <form method="POST" action="<?= url('/priority/reset') ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-danger btn-block"
                                onclick="return confirm('هل تريد إعادة تعيين نظام الأولوية بالكامل؟')">
                            <i class="fas fa-undo ml-1"></i> إعادة تعيين
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Links -->
<div class="row">
    <div class="col-md-3">
        <a href="<?= url('/priority/categories') ?>" class="small-box bg-info">
            <div class="inner">
                <h3><?= count($categories) ?></h3>
                <p>التصنيفات والمجموعات</p>
            </div>
            <div class="icon"><i class="fas fa-layer-group"></i></div>
            <span class="small-box-footer">إدارة التصنيفات <i class="fas fa-arrow-circle-left"></i></span>
        </a>
    </div>
    <div class="col-md-3">
        <a href="<?= url('/priority/exceptions') ?>" class="small-box bg-warning">
            <div class="inner">
                <h3><?= count($exceptions) ?></h3>
                <p>الاستثناءات النشطة</p>
            </div>
            <div class="icon"><i class="fas fa-user-shield"></i></div>
            <span class="small-box-footer">إدارة الاستثناءات <i class="fas fa-arrow-circle-left"></i></span>
        </a>
    </div>
    <div class="col-md-3">
        <a href="<?= url('/priority/dept-order') ?>" class="small-box bg-primary">
            <div class="inner">
                <h3><i class="fas fa-sort"></i></h3>
                <p>ترتيب الأقسام</p>
            </div>
            <div class="icon"><i class="fas fa-building"></i></div>
            <span class="small-box-footer">ترتيب أولوية الأقسام <i class="fas fa-arrow-circle-left"></i></span>
        </a>
    </div>
    <div class="col-md-3">
        <a href="<?= url('/scheduling') ?>" class="small-box bg-success">
            <div class="inner">
                <h3><i class="fas fa-calendar-check"></i></h3>
                <p>الجدولة</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-alt"></i></div>
            <span class="small-box-footer">الذهاب للجدولة <i class="fas fa-arrow-circle-left"></i></span>
        </a>
    </div>
</div>

<!-- Department Status (parallel or sequential mode) -->
<?php if (in_array($state['mode'], ['parallel_dept', 'sequential_dept']) && !empty($deptOrder)): ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-building ml-1"></i>
            <?= $state['mode'] === 'parallel_dept' ? 'حالة الأقسام (متوازي — كل قسم يتقدم باستقلال)' : 'ترتيب الأقسام الحالي (تسلسلي)' ?>
        </h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>القسم</th>
                    <?php if ($state['mode'] === 'parallel_dept'): ?>
                        <th>المجموعة الحالية</th>
                        <th>تقدم</th>
                    <?php else: ?>
                        <th>الحالة</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deptOrder as $i => $dept): ?>
                <?php
                    $isCurrentSeq = $state['mode'] === 'sequential_dept'
                        && !$dept['is_completed']
                        && $state['current_department']
                        && $dept['department_id'] == $state['current_department']['department_id'];
                ?>
                <tr class="<?= $isCurrentSeq ? 'table-success' : '' ?>">
                    <td><?= $i + 1 ?></td>
                    <td><?= e($dept['department_name']) ?></td>

                    <?php if ($state['mode'] === 'parallel_dept'): ?>
                        <td>
                            <?php
                            $deptGroupId = $dept['current_group_id'] ?? null;
                            $deptGroupName = '—';
                            if ($deptGroupId) {
                                $dg = \App\Models\PriorityGroup::find((int)$deptGroupId);
                                if ($dg) $deptGroupName = e($dg['group_name']);
                            }
                            ?>
                            <span class="badge badge-info"><?= $deptGroupName ?></span>
                        </td>
                        <td>
                            <form method="POST" action="<?= url("/priority/advance-dept-group/{$dept['department_id']}") ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-warning btn-xs"
                                        onclick="return confirm('هل تريد تقديم هذا القسم للمجموعة التالية؟')">
                                    <i class="fas fa-forward"></i> المجموعة التالية
                                </button>
                            </form>
                        </td>
                    <?php else: ?>
                        <td>
                            <?php if ($dept['is_completed']): ?>
                                <span class="badge badge-success"><i class="fas fa-check"></i> مكتمل</span>
                            <?php elseif ($isCurrentSeq): ?>
                                <span class="badge badge-warning"><i class="fas fa-play"></i> جاري الآن</span>
                            <?php else: ?>
                                <span class="badge badge-secondary"><i class="fas fa-clock"></i> في الانتظار</span>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Direct Registration Management -->
<?php if ($state['mode'] !== 'disabled' && can('priority.grant_register')): ?>
<div class="card card-outline card-success">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user-check ml-1"></i> منح / سحب صلاحية التسجيل المباشر</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <?php if (!empty($allMembers)): ?>
        <table class="table table-hover table-striped" id="directRegTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم العضو</th>
                    <th>القسم</th>
                    <th>الدرجة العلمية</th>
                    <th>حالة التسجيل</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allMembers as $i => $m): ?>
                <?php if (!$m['user_id']) continue; // skip members without user accounts ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($m['member_name']) ?></td>
                    <td><?= e($m['department_name'] ?? '—') ?></td>
                    <td><?= e($m['degree_name'] ?? '—') ?></td>
                    <td>
                        <?php if ((int)($m['registration_status'] ?? 0) === 1): ?>
                            <span class="badge badge-success"><i class="fas fa-check"></i> مفتوح</span>
                        <?php else: ?>
                            <span class="badge badge-secondary"><i class="fas fa-lock"></i> مغلق</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ((int)($m['registration_status'] ?? 0) === 1): ?>
                        <form method="POST" action="<?= url("/priority/revoke-register/{$m['member_id']}") ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger btn-xs"
                                    onclick="return confirm('هل تريد سحب صلاحية التسجيل من هذا العضو؟')">
                                <i class="fas fa-lock"></i> سحب
                            </button>
                        </form>
                        <?php else: ?>
                        <form method="POST" action="<?= url("/priority/grant-register/{$m['member_id']}") ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-success btn-xs"
                                    onclick="return confirm('هل تريد منح صلاحية التسجيل لهذا العضو؟')">
                                <i class="fas fa-unlock"></i> منح
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-center text-muted p-4">لا يوجد أعضاء مرتبطين بحسابات</p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

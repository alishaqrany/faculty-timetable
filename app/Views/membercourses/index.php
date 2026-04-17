<?php
$this->layout('layouts.app');
$__page_title = 'تكليفات التدريس';
$__breadcrumb = [['label' => 'تكليفات التدريس']];
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">قائمة تكليفات التدريس</h3>
        <?php if (can('membercourses.create')): ?>
        <div class="card-tools">
            <a href="<?= url('/member-courses/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus ml-1"></i> إضافة تكليف
            </a>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped data-table">
            <thead>
                <tr><th>#</th><th>عضو هيئة التدريس</th><th>المقرر</th><th>الشعبة</th><th>القسم</th><th>المستوى</th><th>إجراءات</th></tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $i => $c): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($c['member_name']) ?></td>
                    <td><?= e($c['subject_name']) ?></td>
                    <td><?= e($c['section_name']) ?></td>
                    <td><?= e($c['department_name'] ?? '') ?></td>
                    <td><?= e($c['level_name'] ?? '') ?></td>
                    <td>
                        <?php if (can('membercourses.edit')): ?>
                        <a href="<?= url("/member-courses/{$c['member_course_id']}/edit") ?>" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a>
                        <?php endif; ?>
                        <?php if (can('membercourses.delete')): ?>
                        <form method="POST" action="<?= url("/member-courses/{$c['member_course_id']}/delete") ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger btn-xs btn-delete"><i class="fas fa-trash"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

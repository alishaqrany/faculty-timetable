<?php
$this->layout('layouts.app');
$__page_title = 'إضافة قاعة';
$__breadcrumb = [['label' => 'القاعات', 'url' => '/classrooms'], ['label' => 'إضافة']];
?>

<div class="row"><div class="col-md-8">
    <div class="card card-primary">
        <div class="card-header"><h3 class="card-title">بيانات القاعة الجديدة</h3></div>
        <form method="POST" action="<?= url('/classrooms') ?>">
            <?= csrf_field() ?>
            <div class="card-body">
                <div class="form-group">
                    <label>اسم القاعة <span class="text-danger">*</span></label>
                    <input type="text" name="classroom_name" class="form-control" value="<?= e(old('classroom_name')) ?>" required>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>نوع القاعة</label>
                            <select name="classroom_type" class="form-control">
                                <option value="قاعة">قاعة</option>
                                <option value="معمل">معمل</option>
                                <option value="مدرج">مدرج</option>
                                <option value="قاعة اجتماعات">قاعة اجتماعات</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>السعة</label>
                            <input type="number" name="capacity" class="form-control" value="<?= e(old('capacity', '40')) ?>" min="1">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>المبنى</label>
                            <input type="text" name="building" class="form-control" value="<?= e(old('building')) ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>الطابق</label>
                            <input type="text" name="floor" class="form-control" value="<?= e(old('floor')) ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                <a href="<?= url('/classrooms') ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div></div>

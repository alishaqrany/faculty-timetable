<?php
$this->layout('layouts.app');
$__page_title = 'النسخ الاحتياطي والمزامنة';
$__breadcrumb = [['label' => 'النسخ الاحتياطي']];
?>

<!-- إحصائيات سريعة -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?= $stats['total_count'] ?? 0 ?></h3>
                <p>النسخ المحلية</p>
            </div>
            <div class="icon"><i class="fas fa-database"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?= $stats['total_size_formatted'] ?? '0 B' ?></h3>
                <p>حجم النسخ</p>
            </div>
            <div class="icon"><i class="fas fa-hdd"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?= ($cloudStatus['google_drive']['enabled'] ?? false) + ($cloudStatus['supabase']['enabled'] ?? false) + ($cloudStatus['firebase']['enabled'] ?? false) ?></h3>
                <p>خدمات سحابية مفعلة</p>
            </div>
            <div class="icon"><i class="fas fa-cloud"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3><?= $stats['last_backup']['created_at'] ?? '-' ?></h3>
                <p>آخر نسخة</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
        </div>
    </div>
</div>

<!-- أزرار الإجراءات -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus-circle ml-1"></i> إنشاء نسخة احتياطية جديدة</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <form method="POST" action="<?= url('/backups/create-sql') ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-file-code ml-1"></i> نسخة SQL
                            </button>
                        </form>
                    </div>
                    <div class="col-md-4 mb-2">
                        <form method="POST" action="<?= url('/backups/create-excel') ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-success btn-lg btn-block">
                                <i class="fas fa-file-excel ml-1"></i> نسخة Excel
                            </button>
                        </form>
                    </div>
                    <div class="col-md-4 mb-2">
                        <a href="<?= url('/backups/cloud-settings') ?>" class="btn btn-info btn-lg btn-block">
                            <i class="fas fa-cog ml-1"></i> إعدادات السحابة
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- حالة الخدمات السحابية -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card <?= ($cloudStatus['google_drive']['enabled'] ?? false) ? 'card-success' : 'card-secondary' ?> card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fab fa-google-drive ml-1"></i> Google Drive
                    <?php if ($cloudStatus['google_drive']['enabled'] ?? false): ?>
                        <?php if ($cloudStatus['google_drive']['authenticated'] ?? false): ?>
                            <span class="badge badge-success mr-2">متصل</span>
                        <?php else: ?>
                            <span class="badge badge-warning mr-2">غير مصادق</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="badge badge-secondary mr-2">غير مفعل</span>
                    <?php endif; ?>
                </h3>
            </div>
            <div class="card-body">
                <?php if ($cloudStatus['google_drive']['authenticated'] ?? false): ?>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="loadGoogleDriveFiles()">
                        <i class="fas fa-sync ml-1"></i> عرض الملفات
                    </button>
                <?php elseif ($cloudStatus['google_drive']['enabled'] ?? false): ?>
                    <a href="<?= url('/backups/google-drive/auth') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-sign-in-alt ml-1"></i> ربط الحساب
                    </a>
                <?php else: ?>
                    <a href="<?= url('/backups/cloud-settings') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-cog ml-1"></i> تفعيل
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card <?= ($cloudStatus['supabase']['enabled'] ?? false) ? 'card-success' : 'card-secondary' ?> card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-database ml-1"></i> Supabase
                    <?php if ($cloudStatus['supabase']['enabled'] ?? false): ?>
                        <span class="badge badge-success mr-2">مفعل</span>
                    <?php else: ?>
                        <span class="badge badge-secondary mr-2">غير مفعل</span>
                    <?php endif; ?>
                </h3>
            </div>
            <div class="card-body">
                <?php if ($cloudStatus['supabase']['enabled'] ?? false): ?>
                    <form method="POST" action="<?= url('/backups/sync-supabase') ?>" class="d-inline">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-sync ml-1"></i> مزامنة الجداول
                        </button>
                    </form>
                    <button type="button" class="btn btn-outline-info btn-sm" onclick="loadSupabaseFiles()">
                        <i class="fas fa-folder ml-1"></i> الملفات
                    </button>
                <?php else: ?>
                    <a href="<?= url('/backups/cloud-settings') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-cog ml-1"></i> تفعيل
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card <?= ($cloudStatus['firebase']['enabled'] ?? false) ? 'card-success' : 'card-secondary' ?> card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-fire ml-1"></i> Firebase
                    <?php if ($cloudStatus['firebase']['enabled'] ?? false): ?>
                        <span class="badge badge-success mr-2">مفعل</span>
                    <?php else: ?>
                        <span class="badge badge-secondary mr-2">غير مفعل</span>
                    <?php endif; ?>
                </h3>
            </div>
            <div class="card-body">
                <?php if ($cloudStatus['firebase']['enabled'] ?? false): ?>
                    <form method="POST" action="<?= url('/backups/sync-firebase') ?>" class="d-inline">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-sync ml-1"></i> مزامنة الجداول
                        </button>
                    </form>
                    <button type="button" class="btn btn-outline-info btn-sm" onclick="loadFirebaseFiles()">
                        <i class="fas fa-folder ml-1"></i> الملفات
                    </button>
                <?php else: ?>
                    <a href="<?= url('/backups/cloud-settings') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-cog ml-1"></i> تفعيل
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- النسخ الاحتياطية المحلية -->
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-hdd ml-1"></i> النسخ الاحتياطية المحلية</h3>
    </div>
    <div class="card-body">
        <?php if (empty($localBackups)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle ml-1"></i>
                لا توجد نسخ احتياطية محلية. قم بإنشاء نسخة جديدة من الأزرار أعلاه.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="thead-light">
                        <tr>
                            <th>اسم الملف</th>
                            <th>النوع</th>
                            <th>الحجم</th>
                            <th>تاريخ الإنشاء</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($localBackups as $backup): ?>
                        <tr>
                            <td>
                                <i class="fas fa-file<?= $backup['type'] === 'sql' ? '-code' : '-excel' ?> text-<?= $backup['type'] === 'sql' ? 'primary' : 'success' ?> ml-1"></i>
                                <?= e($backup['name']) ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= $backup['type'] === 'sql' ? 'primary' : 'success' ?>">
                                    <?= strtoupper($backup['type']) ?>
                                </span>
                            </td>
                            <td><?= $backup['size_formatted'] ?></td>
                            <td><?= $backup['created_at'] ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?= url('/backups/download/' . urlencode($backup['name'])) ?>" 
                                       class="btn btn-sm btn-outline-primary" title="تنزيل">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    
                                    <?php if ($backup['type'] === 'sql'): ?>
                                    <form method="POST" action="<?= url('/backups/restore/' . urlencode($backup['name'])) ?>" 
                                          class="d-inline" onsubmit="return confirm('هل تريد استعادة هذه النسخة؟ سيتم استبدال البيانات الحالية!')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="استعادة">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    
                                    <form method="POST" action="<?= url('/backups/upload-cloud/' . urlencode($backup['name'])) ?>" 
                                          class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-info" title="رفع للسحابة">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                        </button>
                                    </form>
                                    
                                    <form method="POST" action="<?= url('/backups/delete/' . urlencode($backup['name'])) ?>" 
                                          class="d-inline" onsubmit="return confirm('هل تريد حذف هذه النسخة؟')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal لعرض ملفات السحابة -->
<div class="modal fade" id="cloudFilesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-cloud ml-1"></i> ملفات السحابة</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="cloudFilesContent">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">جاري التحميل...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function loadGoogleDriveFiles() {
    $('#cloudFilesModal .modal-title').html('<i class="fab fa-google-drive ml-1"></i> ملفات Google Drive');
    $('#cloudFilesContent').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
    $('#cloudFilesModal').modal('show');
    
    fetch('<?= url('/backups/google-drive/files') ?>')
        .then(r => r.json())
        .then(data => {
            if (data.success && data.files.length > 0) {
                let html = '<table class="table table-sm"><thead><tr><th>الملف</th><th>الحجم</th><th>التاريخ</th><th></th></tr></thead><tbody>';
                data.files.forEach(f => {
                    html += `<tr>
                        <td>${f.name}</td>
                        <td>${formatBytes(f.size || 0)}</td>
                        <td>${f.createdTime ? new Date(f.createdTime).toLocaleString('ar-EG') : '-'}</td>
                        <td>
                            <form method="POST" action="<?= url('/backups/download-from-cloud') ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="service" value="google_drive">
                                <input type="hidden" name="filename" value="${f.name}">
                                <input type="hidden" name="file_id" value="${f.id}">
                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i></button>
                            </form>
                        </td>
                    </tr>`;
                });
                html += '</tbody></table>';
                $('#cloudFilesContent').html(html);
            } else {
                $('#cloudFilesContent').html('<div class="alert alert-info">لا توجد ملفات</div>');
            }
        })
        .catch(() => $('#cloudFilesContent').html('<div class="alert alert-danger">خطأ في جلب الملفات</div>'));
}

function loadSupabaseFiles() {
    $('#cloudFilesModal .modal-title').html('<i class="fas fa-database ml-1"></i> ملفات Supabase');
    $('#cloudFilesContent').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
    $('#cloudFilesModal').modal('show');
    
    fetch('<?= url('/backups/supabase/files') ?>')
        .then(r => r.json())
        .then(data => {
            if (data.success && data.files.length > 0) {
                let html = '<table class="table table-sm"><thead><tr><th>الملف</th><th>الحجم</th><th></th></tr></thead><tbody>';
                data.files.forEach(f => {
                    html += `<tr>
                        <td>${f.name}</td>
                        <td>${formatBytes(f.size || 0)}</td>
                        <td>
                            <form method="POST" action="<?= url('/backups/download-from-cloud') ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="service" value="supabase">
                                <input type="hidden" name="filename" value="${f.name}">
                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i></button>
                            </form>
                        </td>
                    </tr>`;
                });
                html += '</tbody></table>';
                $('#cloudFilesContent').html(html);
            } else {
                $('#cloudFilesContent').html('<div class="alert alert-info">لا توجد ملفات</div>');
            }
        })
        .catch(() => $('#cloudFilesContent').html('<div class="alert alert-danger">خطأ في جلب الملفات</div>'));
}

function loadFirebaseFiles() {
    $('#cloudFilesModal .modal-title').html('<i class="fas fa-fire ml-1"></i> ملفات Firebase');
    $('#cloudFilesContent').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
    $('#cloudFilesModal').modal('show');
    
    fetch('<?= url('/backups/firebase/files') ?>')
        .then(r => r.json())
        .then(data => {
            if (data.success && data.files.length > 0) {
                let html = '<table class="table table-sm"><thead><tr><th>الملف</th><th>الحجم</th><th></th></tr></thead><tbody>';
                data.files.forEach(f => {
                    html += `<tr>
                        <td>${f.name}</td>
                        <td>${formatBytes(f.size || 0)}</td>
                        <td>
                            <form method="POST" action="<?= url('/backups/download-from-cloud') ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="service" value="firebase">
                                <input type="hidden" name="filename" value="${f.name}">
                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i></button>
                            </form>
                        </td>
                    </tr>`;
                });
                html += '</tbody></table>';
                $('#cloudFilesContent').html(html);
            } else {
                $('#cloudFilesContent').html('<div class="alert alert-info">لا توجد ملفات</div>');
            }
        })
        .catch(() => $('#cloudFilesContent').html('<div class="alert alert-danger">خطأ في جلب الملفات</div>'));
}

function formatBytes(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>

<?php
$this->layout('layouts.app');
$__page_title = 'إعدادات السحابة';
$__breadcrumb = [
    ['label' => 'النسخ الاحتياطي', 'url' => url('/backups')],
    ['label' => 'إعدادات السحابة']
];
?>

<form method="POST" action="<?= url('/backups/cloud-settings') ?>">
    <?= csrf_field() ?>
    
    <div class="row">
        <!-- Google Drive -->
        <div class="col-lg-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fab fa-google-drive ml-1"></i> Google Drive
                    </h3>
                    <div class="card-tools">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="gdEnabled" name="google_drive[enabled]" value="1"
                                <?= ($cloudConfig['google_drive']['enabled'] ?? false) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="gdEnabled">تفعيل</label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle ml-1"></i>
                        للحصول على بيانات الاعتماد:
                        <ol class="mb-0 mt-2">
                            <li>انتقل إلى <a href="https://console.cloud.google.com" target="_blank">Google Cloud Console</a></li>
                            <li>أنشئ مشروع جديد أو استخدم مشروع موجود</li>
                            <li>فعّل Google Drive API</li>
                            <li>أنشئ OAuth 2.0 Credentials</li>
                            <li>أضف URI التالي في Authorized redirect URIs:<br>
                                <code><?= url('/backups/google-drive/callback') ?></code>
                            </li>
                        </ol>
                    </div>
                    
                    <div class="form-group">
                        <label>Client ID</label>
                        <input type="text" name="google_drive[client_id]" class="form-control" dir="ltr"
                            value="<?= e($cloudConfig['google_drive']['client_id'] ?? '') ?>"
                            placeholder="xxxx.apps.googleusercontent.com">
                    </div>
                    
                    <div class="form-group">
                        <label>Client Secret</label>
                        <input type="password" name="google_drive[client_secret]" class="form-control" dir="ltr"
                            value="<?= e($cloudConfig['google_drive']['client_secret'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Folder ID (اختياري)</label>
                        <input type="text" name="google_drive[folder_id]" class="form-control" dir="ltr"
                            value="<?= e($cloudConfig['google_drive']['folder_id'] ?? '') ?>"
                            placeholder="مجلد النسخ الاحتياطية في Drive">
                        <small class="text-muted">اتركه فارغاً للحفظ في Root</small>
                    </div>
                    
                    <?php if (!empty($cloudConfig['google_drive']['access_token'])): ?>
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle ml-1"></i> متصل بـ Google Drive
                        </div>
                    <?php elseif (!empty($cloudConfig['google_drive']['client_id'])): ?>
                        <a href="<?= url('/backups/google-drive/auth') ?>" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt ml-1"></i> ربط الحساب
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Supabase -->
        <div class="col-lg-6">
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-database ml-1"></i> Supabase
                    </h3>
                    <div class="card-tools">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="sbEnabled" name="supabase[enabled]" value="1"
                                <?= ($cloudConfig['supabase']['enabled'] ?? false) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="sbEnabled">تفعيل</label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle ml-1"></i>
                        للحصول على بيانات Supabase:
                        <ol class="mb-0 mt-2">
                            <li>سجل دخول في <a href="https://supabase.com" target="_blank">Supabase</a></li>
                            <li>أنشئ مشروع جديد</li>
                            <li>اذهب إلى Settings &gt; API</li>
                            <li>انسخ Project URL و API Keys</li>
                            <li>أنشئ Storage Bucket باسم "backups"</li>
                        </ol>
                    </div>
                    
                    <div class="form-group">
                        <label>Project URL</label>
                        <input type="url" name="supabase[url]" class="form-control" dir="ltr"
                            value="<?= e($cloudConfig['supabase']['url'] ?? '') ?>"
                            placeholder="https://xxxx.supabase.co">
                    </div>
                    
                    <div class="form-group">
                        <label>Anon Key</label>
                        <input type="password" name="supabase[anon_key]" class="form-control" dir="ltr"
                            value="<?= e($cloudConfig['supabase']['anon_key'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Service Role Key (للعمليات الإدارية)</label>
                        <input type="password" name="supabase[service_role_key]" class="form-control" dir="ltr"
                            value="<?= e($cloudConfig['supabase']['service_role_key'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Storage Bucket</label>
                        <input type="text" name="supabase[bucket]" class="form-control" dir="ltr"
                            value="<?= e($cloudConfig['supabase']['bucket'] ?? 'backups') ?>">
                    </div>
                    
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="sbSync" name="supabase[sync_tables]" value="1"
                            <?= ($cloudConfig['supabase']['sync_tables'] ?? false) ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="sbSync">تفعيل مزامنة الجداول</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Firebase -->
        <div class="col-lg-6">
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-fire ml-1"></i> Firebase
                    </h3>
                    <div class="card-tools">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="fbEnabled" name="firebase[enabled]" value="1"
                                <?= ($cloudConfig['firebase']['enabled'] ?? false) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="fbEnabled">تفعيل</label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle ml-1"></i>
                        للحصول على بيانات Firebase:
                        <ol class="mb-0 mt-2">
                            <li>انتقل إلى <a href="https://console.firebase.google.com" target="_blank">Firebase Console</a></li>
                            <li>أنشئ مشروع أو استخدم مشروع موجود</li>
                            <li>فعّل Cloud Storage و/أو Realtime Database</li>
                            <li>للعمليات المتقدمة: أنشئ Service Account وحمّل ملف JSON</li>
                        </ol>
                    </div>
                    
                    <div class="form-group">
                        <label>Project ID</label>
                        <input type="text" name="firebase[project_id]" class="form-control" dir="ltr"
                            value="<?= e($cloudConfig['firebase']['project_id'] ?? '') ?>"
                            placeholder="my-project-id">
                    </div>
                    
                    <div class="form-group">
                        <label>API Key (اختياري)</label>
                        <input type="password" name="firebase[api_key]" class="form-control" dir="ltr"
                            value="<?= e($cloudConfig['firebase']['api_key'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Storage Bucket</label>
                        <input type="text" name="firebase[storage_bucket]" class="form-control" dir="ltr"
                            value="<?= e($cloudConfig['firebase']['storage_bucket'] ?? '') ?>"
                            placeholder="my-project.appspot.com">
                    </div>
                    
                    <div class="form-group">
                        <label>Realtime Database URL (اختياري)</label>
                        <input type="url" name="firebase[database_url]" class="form-control" dir="ltr"
                            value="<?= e($cloudConfig['firebase']['database_url'] ?? '') ?>"
                            placeholder="https://my-project.firebaseio.com">
                    </div>
                    
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="fbSync" name="firebase[sync_tables]" value="1"
                            <?= ($cloudConfig['firebase']['sync_tables'] ?? false) ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="fbSync">تفعيل مزامنة الجداول</label>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- إعدادات النسخ الاحتياطي -->
        <div class="col-lg-6">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cog ml-1"></i> إعدادات عامة
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>أقصى عدد للنسخ المحلية</label>
                        <input type="number" name="backup[max_local_backups]" class="form-control" min="1" max="100"
                            value="<?= (int)($cloudConfig['backup']['max_local_backups'] ?? 10) ?>">
                        <small class="text-muted">سيتم حذف النسخ الأقدم تلقائياً عند تجاوز هذا العدد</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="autoBackup" name="backup[auto_backup]" value="1"
                                <?= ($cloudConfig['backup']['auto_backup'] ?? false) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="autoBackup">نسخ احتياطي تلقائي</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>تكرار النسخ التلقائي</label>
                        <select name="backup[auto_backup_interval]" class="form-control">
                            <option value="daily" <?= ($cloudConfig['backup']['auto_backup_interval'] ?? '') === 'daily' ? 'selected' : '' ?>>يومياً</option>
                            <option value="weekly" <?= ($cloudConfig['backup']['auto_backup_interval'] ?? '') === 'weekly' ? 'selected' : '' ?>>أسبوعياً</option>
                            <option value="monthly" <?= ($cloudConfig['backup']['auto_backup_interval'] ?? '') === 'monthly' ? 'selected' : '' ?>>شهرياً</option>
                        </select>
                    </div>
                    
                    <hr>
                    
                    <h5><i class="fas fa-sync ml-1"></i> الجداول المراد مزامنتها</h5>
                    <p class="text-muted small">هذه الجداول سيتم مزامنتها مع Supabase و Firebase عند تفعيل المزامنة</p>
                    
                    <div class="row">
                        <?php 
                        $syncTables = $cloudConfig['sync']['tables_to_sync'] ?? [];
                        $allTables = ['departments', 'levels', 'faculty_members', 'subjects', 'sections', 'classrooms', 'sessions', 'timetable', 'divisions', 'member_courses'];
                        foreach ($allTables as $table): 
                        ?>
                        <div class="col-6">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" 
                                    id="sync_<?= $table ?>" 
                                    name="sync[tables_to_sync][]" 
                                    value="<?= $table ?>"
                                    <?= in_array($table, $syncTables) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="sync_<?= $table ?>"><?= $table ?></label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mb-4">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-save ml-1"></i> حفظ الإعدادات
        </button>
        <a href="<?= url('/backups') ?>" class="btn btn-secondary btn-lg">
            <i class="fas fa-arrow-right ml-1"></i> رجوع
        </a>
    </div>
</form>

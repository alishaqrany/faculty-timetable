<?php
/**
 * Cloud Services Configuration
 * 
 * Settings for backup and sync with cloud providers:
 * - Google Drive
 * - Supabase
 * - Firebase
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Google Drive Configuration
    |--------------------------------------------------------------------------
    */
    'google_drive' => [
        'enabled' => false,
        'client_id' => '', // من Google Cloud Console
        'client_secret' => '',
        'redirect_uri' => '', // سيتم تعيينه تلقائياً
        'access_token' => '', // سيتم تخزينه بعد المصادقة
        'refresh_token' => '',
        'folder_id' => '', // مجلد النسخ الاحتياطية
    ],

    /*
    |--------------------------------------------------------------------------
    | Supabase Configuration
    |--------------------------------------------------------------------------
    */
    'supabase' => [
        'enabled' => false,
        'url' => '', // مثال: https://xxxx.supabase.co
        'anon_key' => '',
        'service_role_key' => '', // للعمليات الإدارية
        'bucket' => 'backups', // اسم Storage Bucket
        'sync_tables' => true, // مزامنة الجداول
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Configuration
    |--------------------------------------------------------------------------
    */
    'firebase' => [
        'enabled' => false,
        'project_id' => '',
        'api_key' => '',
        'auth_domain' => '', // مثال: project-id.firebaseapp.com
        'storage_bucket' => '', // مثال: project-id.appspot.com
        'service_account_json' => '', // مسار ملف credentials JSON
        'database_url' => '', // للـ Realtime Database
        'sync_tables' => true, // مزامنة الجداول
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Settings
    |--------------------------------------------------------------------------
    */
    'backup' => [
        'local_path' => APP_ROOT . '/storage/backups',
        'max_local_backups' => 10, // أقصى عدد نسخ محلية
        'auto_backup' => false, // نسخ احتياطي تلقائي
        'auto_backup_interval' => 'daily', // daily, weekly, monthly
        'compress' => true, // ضغط الملفات
        'encrypt' => false, // تشفير النسخ
        'encryption_key' => '', // مفتاح التشفير
    ],

    /*
    |--------------------------------------------------------------------------
    | Sync Settings
    |--------------------------------------------------------------------------
    */
    'sync' => [
        'enabled' => false,
        'direction' => 'both', // upload, download, both
        'conflict_resolution' => 'local_wins', // local_wins, remote_wins, newer_wins
        'sync_interval' => 'realtime', // realtime, hourly, daily
        'tables_to_sync' => [
            'departments',
            'levels',
            'faculty_members',
            'subjects',
            'sections',
            'classrooms',
            'sessions',
            'timetable',
        ],
    ],
];

<?php
/**
 * Application Routes
 *
 * @var Router $router
 */

// ── Public routes (no auth) ────────────────────────────────────────
$router->get('/', 'HomeController@index');
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@doLogin');
$router->post('/logout', 'AuthController@logout');

// ── Authenticated routes ───────────────────────────────────────────
$router->group('', ['AuthMiddleware', 'CsrfMiddleware'], function (Router $router) {

    // Dashboard
    $router->get('/dashboard', 'DashboardController@index');

    // Profile
    $router->get('/profile', 'AuthController@profile');
    $router->post('/profile', 'AuthController@updateProfile');

    // CRUD Resources
    $router->resource('/departments', 'DepartmentController');
    $router->post('/levels/seed-defaults', 'LevelController@seedDefaults');
    $router->resource('/levels', 'LevelController');
    $router->resource('/members', 'MemberController');
    $router->resource('/subjects', 'SubjectController');
    $router->resource('/divisions', 'DivisionController');
    $router->get('/api/divisions/by-dept-level', 'DivisionController@byDepartmentLevel');
    $router->post('/sections/generate-auto', 'SectionController@generateAuto');
    $router->resource('/sections', 'SectionController');
    $router->get('/api/sections/by-division', 'SectionController@byDivision');
    $router->resource('/classrooms', 'ClassroomController');
    $router->post('/sessions/generate', 'SessionController@generate');
    $router->resource('/sessions', 'SessionController');
    $router->resource('/member-courses', 'MemberCourseController');

    // Scheduling
    $router->get('/scheduling', 'SchedulingController@index');
    $router->post('/scheduling', 'SchedulingController@store');
    $router->get('/scheduling/{id}/edit', 'SchedulingController@edit');
    $router->post('/scheduling/{id}', 'SchedulingController@update');
    $router->post('/scheduling/{id}/delete', 'SchedulingController@destroy');
    $router->post('/scheduling/pass-role', 'SchedulingController@passRole');

    // Priority Management
    $router->get('/priority', 'PriorityController@index');
    $router->post('/priority/mode', 'PriorityController@updateMode');
    $router->post('/priority/set-category', 'PriorityController@setActiveCategory');
    $router->get('/priority/categories', 'PriorityController@categories');
    $router->post('/priority/categories', 'PriorityController@storeCategory');
    $router->get('/priority/categories/{id}/edit', 'PriorityController@editCategory');
    $router->post('/priority/categories/{id}', 'PriorityController@updateCategory');
    $router->post('/priority/categories/{id}/delete', 'PriorityController@deleteCategory');
    $router->get('/priority/categories/{id}/groups', 'PriorityController@groups');
    $router->post('/priority/groups', 'PriorityController@storeGroup');
    $router->get('/priority/groups/{id}/edit', 'PriorityController@editGroup');
    $router->post('/priority/groups/{id}', 'PriorityController@updateGroup');
    $router->post('/priority/groups/{id}/delete', 'PriorityController@deleteGroup');
    $router->get('/priority/groups/{id}/members', 'PriorityController@groupMembers');
    $router->post('/priority/groups/{id}/members', 'PriorityController@addGroupMember');
    $router->post('/priority/group-members/{id}/delete', 'PriorityController@removeGroupMember');
    $router->get('/priority/exceptions', 'PriorityController@exceptions');
    $router->post('/priority/exceptions', 'PriorityController@grantException');
    $router->post('/priority/exceptions/{id}/revoke', 'PriorityController@revokeException');
    $router->get('/priority/dept-order', 'PriorityController@deptOrder');
    $router->post('/priority/dept-order', 'PriorityController@saveDeptOrder');
    $router->post('/priority/advance-group', 'PriorityController@advanceGroup');
    $router->post('/priority/advance-dept', 'PriorityController@advanceDept');
    $router->post('/priority/advance-dept-group/{id}', 'PriorityController@advanceDeptGroup');
    $router->post('/priority/reset', 'PriorityController@resetPriority');
    $router->post('/priority/grant-register/{id}', 'PriorityController@grantMemberRegister');
    $router->post('/priority/revoke-register/{id}', 'PriorityController@revokeMemberRegister');

    // Timetable
    $router->get('/timetable', 'TimetableController@index');
    $router->get('/timetable/export', 'TimetableController@export');

    // Academic Years
    $router->resource('/academic-years', 'AcademicYearController');
    $router->post('/academic-years/{id}/set-current', 'AcademicYearController@setCurrent');

    // Semesters
    $router->post('/semesters/generate-for-year', 'SemesterController@generateForYear');
    $router->resource('/semesters', 'SemesterController');
    $router->post('/semesters/{id}/set-current', 'SemesterController@setCurrent');

    // Users Management
    $router->resource('/users', 'UserController');

    // Audit Logs
    $router->get('/audit-logs', 'AuditLogController@index');
    $router->get('/audit-logs/{id}', 'AuditLogController@show');

    // Notifications
    $router->get('/notifications', 'NotificationController@index');
    $router->post('/notifications/send', 'NotificationController@send');
    $router->post('/notifications/{id}/read', 'NotificationController@markRead');
    $router->post('/notifications/read-all', 'NotificationController@markAllRead');
    $router->post('/notifications/{id}/delete', 'NotificationController@destroy');

    // Settings
    $router->get('/settings', 'SettingController@index');
    $router->post('/settings', 'SettingController@update');
    $router->get('/settings/data-transfer/export-sql', 'DataTransferController@exportSql');
    $router->get('/settings/data-transfer/export-excel', 'DataTransferController@exportExcel');
    $router->get('/settings/data-transfer/sample-sql', 'DataTransferController@downloadSampleSql');
    $router->post('/settings/data-transfer/import-sql', 'DataTransferController@importSql');
    $router->post('/settings/data-transfer/import-excel', 'DataTransferController@importExcel');

    // Backups & Cloud Sync
    $router->get('/backups', 'BackupController@index');
    $router->post('/backups/create-sql', 'BackupController@createSqlBackup');
    $router->post('/backups/create-excel', 'BackupController@createExcelBackup');
    $router->get('/backups/download/{filename}', 'BackupController@download');
    $router->post('/backups/delete/{filename}', 'BackupController@delete');
    $router->post('/backups/restore/{filename}', 'BackupController@restore');
    $router->post('/backups/upload-cloud/{filename}', 'BackupController@uploadToCloud');
    $router->get('/backups/cloud-settings', 'BackupController@cloudSettings');
    $router->post('/backups/cloud-settings', 'BackupController@saveCloudSettings');
    $router->get('/backups/google-drive/auth', 'BackupController@googleDriveAuth');
    $router->get('/backups/google-drive/callback', 'BackupController@googleDriveCallback');
    $router->get('/backups/google-drive/files', 'BackupController@googleDriveFiles');
    $router->get('/backups/supabase/files', 'BackupController@supabaseFiles');
    $router->get('/backups/firebase/files', 'BackupController@firebaseFiles');
    $router->post('/backups/sync-supabase', 'BackupController@syncSupabase');
    $router->post('/backups/sync-firebase', 'BackupController@syncFirebase');
    $router->post('/backups/download-from-cloud', 'BackupController@downloadFromCloud');
});

// ── API routes ─────────────────────────────────────────────────────
// API login (no auth required, but rate limited)
$router->group('/api/auth', ['RateLimitMiddleware'], function (Router $router) {
    $router->post('/login', 'Api\\AuthController@login');
});

// API authenticated routes (rate limited)
$router->group('/api', ['RateLimitMiddleware', 'ApiAuthMiddleware'], function (Router $router) {
    $router->get('/timetable', 'Api\\TimetableController@index');
    $router->get('/departments', 'Api\\TimetableController@departments');
    $router->get('/members', 'Api\\TimetableController@members');
    $router->get('/classrooms', 'Api\\TimetableController@classrooms');
    $router->get('/subjects', 'Api\\TimetableController@subjects');
    $router->get('/sections', 'Api\\TimetableController@sections');
    $router->get('/sessions', 'Api\\TimetableController@sessions');
});

// ── Mobile API v1 ───────────────────────────────────────────────────
$router->group('/api/v1/auth', ['RateLimitMiddleware'], function (Router $router) {
    $router->post('/login', 'Api\\V1\\AuthController@login');
});

$router->group('/api/v1', ['RateLimitMiddleware', 'ApiAuthMiddleware'], function (Router $router) {
    $router->post('/auth/logout', 'Api\\V1\\AuthController@logout');
    $router->get('/me', 'Api\\V1\\AuthController@me');

    $router->get('/students/timetable', 'Api\\V1\\StudentController@timetable');
    $router->get('/students/section-timetable', 'Api\\V1\\StudentController@sectionTimetable');
    $router->get('/students/today-lectures', 'Api\\V1\\StudentController@todayLectures');

    $router->get('/lookups/departments', 'Api\\V1\\LookupController@departments');
    $router->get('/lookups/levels', 'Api\\V1\\LookupController@levels');
    $router->get('/lookups/sections', 'Api\\V1\\LookupController@sections');
    $router->get('/lookups/subjects', 'Api\\V1\\LookupController@subjects');
    $router->get('/lookups/sessions', 'Api\\V1\\LookupController@sessions');
    $router->get('/lookups/classrooms', 'Api\\V1\\LookupController@classrooms');
});

$router->group('/api/v1/admin', ['RateLimitMiddleware', 'ApiAuthMiddleware', 'ApiRoleMiddleware'], function (Router $router) {
    $router->get('/departments', 'Api\\V1\\AdminController@departments');
    $router->post('/departments', 'Api\\V1\\AdminController@storeDepartment');
    $router->post('/departments/{id}', 'Api\\V1\\AdminController@updateDepartment');
    $router->post('/departments/{id}/delete', 'Api\\V1\\AdminController@deleteDepartment');

    $router->get('/subjects', 'Api\\V1\\AdminController@subjects');
    $router->post('/subjects', 'Api\\V1\\AdminController@storeSubject');
    $router->post('/subjects/{id}', 'Api\\V1\\AdminController@updateSubject');
    $router->post('/subjects/{id}/delete', 'Api\\V1\\AdminController@deleteSubject');

    $router->get('/sections', 'Api\\V1\\AdminController@sections');
    $router->post('/sections', 'Api\\V1\\AdminController@storeSection');
    $router->post('/sections/{id}', 'Api\\V1\\AdminController@updateSection');
    $router->post('/sections/{id}/delete', 'Api\\V1\\AdminController@deleteSection');

    $router->get('/sessions', 'Api\\V1\\AdminController@sessions');
    $router->post('/sessions', 'Api\\V1\\AdminController@storeSession');
    $router->post('/sessions/{id}', 'Api\\V1\\AdminController@updateSession');
    $router->post('/sessions/{id}/delete', 'Api\\V1\\AdminController@deleteSession');
});

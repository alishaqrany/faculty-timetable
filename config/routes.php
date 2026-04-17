<?php
/**
 * Application Routes
 *
 * @var Router $router
 */

// ── Public routes (no auth) ────────────────────────────────────────
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@doLogin');
$router->post('/logout', 'AuthController@logout');

// ── Authenticated routes ───────────────────────────────────────────
$router->group('', ['AuthMiddleware', 'CsrfMiddleware'], function (Router $router) {

    // Dashboard
    $router->get('/', 'DashboardController@index');

    // Profile
    $router->get('/profile', 'AuthController@profile');
    $router->post('/profile', 'AuthController@updateProfile');

    // CRUD Resources
    $router->resource('/departments', 'DepartmentController');
    $router->resource('/levels', 'LevelController');
    $router->resource('/members', 'MemberController');
    $router->resource('/subjects', 'SubjectController');
    $router->resource('/sections', 'SectionController');
    $router->resource('/classrooms', 'ClassroomController');
    $router->resource('/sessions', 'SessionController');
    $router->post('/sessions/generate', 'SessionController@generate');
    $router->resource('/member-courses', 'MemberCourseController');

    // Scheduling
    $router->get('/scheduling', 'SchedulingController@index');
    $router->post('/scheduling', 'SchedulingController@store');
    $router->get('/scheduling/{id}/edit', 'SchedulingController@edit');
    $router->post('/scheduling/{id}', 'SchedulingController@update');
    $router->post('/scheduling/{id}/delete', 'SchedulingController@destroy');
    $router->post('/scheduling/pass-role', 'SchedulingController@passRole');

    // Timetable
    $router->get('/timetable', 'TimetableController@index');
    $router->get('/timetable/export', 'TimetableController@export');

    // Academic Years
    $router->resource('/academic-years', 'AcademicYearController');
    $router->post('/academic-years/{id}/set-current', 'AcademicYearController@setCurrent');

    // Semesters
    $router->resource('/semesters', 'SemesterController');
    $router->post('/semesters/{id}/set-current', 'SemesterController@setCurrent');

    // Users Management
    $router->resource('/users', 'UserController');

    // Audit Logs
    $router->get('/audit-logs', 'AuditLogController@index');
    $router->get('/audit-logs/{id}', 'AuditLogController@show');

    // Notifications
    $router->get('/notifications', 'NotificationController@index');
    $router->post('/notifications/{id}/read', 'NotificationController@markRead');
    $router->post('/notifications/read-all', 'NotificationController@markAllRead');
    $router->post('/notifications/{id}/delete', 'NotificationController@destroy');

    // Settings
    $router->get('/settings', 'SettingController@index');
    $router->post('/settings', 'SettingController@update');
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

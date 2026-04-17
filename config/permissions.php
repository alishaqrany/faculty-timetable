<?php

/**
 * Permissions map for RBAC.
 * key = permission slug (module.action)
 * value = Arabic label
 */
return [
    // Dashboard
    'dashboard.view'         => 'عرض لوحة التحكم',

    // Departments
    'departments.view'       => 'عرض الأقسام',
    'departments.create'     => 'إنشاء قسم',
    'departments.edit'       => 'تعديل قسم',
    'departments.delete'     => 'حذف قسم',

    // Levels
    'levels.view'            => 'عرض المستويات',
    'levels.create'          => 'إنشاء مستوى',
    'levels.edit'            => 'تعديل مستوى',
    'levels.delete'          => 'حذف مستوى',

    // Members
    'members.view'           => 'عرض أعضاء هيئة التدريس',
    'members.create'         => 'إنشاء عضو',
    'members.edit'           => 'تعديل عضو',
    'members.delete'         => 'حذف عضو',

    // Subjects
    'subjects.view'          => 'عرض المقررات',
    'subjects.create'        => 'إنشاء مقرر',
    'subjects.edit'          => 'تعديل مقرر',
    'subjects.delete'        => 'حذف مقرر',

    // Sections
    'sections.view'          => 'عرض الشعب',
    'sections.create'        => 'إنشاء شعبة',
    'sections.edit'          => 'تعديل شعبة',
    'sections.delete'        => 'حذف شعبة',

    // Classrooms
    'classrooms.view'        => 'عرض القاعات',
    'classrooms.create'      => 'إنشاء قاعة',
    'classrooms.edit'        => 'تعديل قاعة',
    'classrooms.delete'      => 'حذف قاعة',

    // Sessions
    'sessions.view'          => 'عرض الفترات الزمنية',
    'sessions.create'        => 'إنشاء فترة',
    'sessions.edit'          => 'تعديل فترة',
    'sessions.delete'        => 'حذف فترة',

    // Member Courses
    'membercourses.view'     => 'عرض تكليفات التدريس',
    'membercourses.create'   => 'إنشاء تكليف',
    'membercourses.edit'     => 'تعديل تكليف',
    'membercourses.delete'   => 'حذف تكليف',

    // Scheduling
    'scheduling.view'        => 'عرض الجدولة',
    'scheduling.manage'      => 'إدارة الجدولة',
    'scheduling.pass_role'   => 'تمرير الدور',

    // Timetable
    'timetable.view'         => 'عرض الجدول الزمني',
    'timetable.export'       => 'تصدير الجدول',

    // Users
    'users.view'             => 'عرض المستخدمين',
    'users.create'           => 'إنشاء مستخدم',
    'users.edit'             => 'تعديل مستخدم',
    'users.delete'           => 'حذف مستخدم',

    // Academic Years & Semesters
    'academic_years.view'    => 'عرض السنوات الأكاديمية',
    'academic_years.manage'  => 'إدارة السنوات الأكاديمية',
    'semesters.view'         => 'عرض الفصول الدراسية',
    'semesters.manage'       => 'إدارة الفصول الدراسية',

    // Settings
    'settings.view'          => 'عرض الإعدادات',
    'settings.manage'        => 'إدارة الإعدادات',

    // Audit Log
    'audit.view'             => 'عرض سجل التدقيق',

    // Notifications
    'notifications.view'     => 'عرض الإشعارات',

    // API
    'api.access'             => 'الوصول إلى واجهة API',
];

<?php
return function (Database $db) {
    // Roles
    $roles = [
        ['role_name' => 'مدير النظام', 'role_slug' => 'admin', 'description' => 'صلاحية كاملة على النظام', 'is_system' => 1],
        ['role_name' => 'رئيس قسم', 'role_slug' => 'dept_head', 'description' => 'إدارة القسم والأعضاء والمقررات', 'is_system' => 1],
        ['role_name' => 'عضو هيئة تدريس', 'role_slug' => 'faculty', 'description' => 'جدولة المقررات المكلف بها وعرض الجدول', 'is_system' => 1],
        ['role_name' => 'مراقب', 'role_slug' => 'viewer', 'description' => 'عرض فقط بدون تعديل', 'is_system' => 1],
    ];

    foreach ($roles as $role) {
        $existing = $db->fetch("SELECT id FROM roles WHERE role_slug = ?", [$role['role_slug']]);
        if (!$existing) {
            $db->insert('roles', $role);
        }
    }

    // Permissions
    $permissions = require APP_ROOT . '/config/permissions.php';
    foreach ($permissions as $slug => $name) {
        $module = explode('.', $slug)[0];
        $existing = $db->fetch("SELECT id FROM permissions WHERE permission_slug = ?", [$slug]);
        if (!$existing) {
            $db->insert('permissions', [
                'permission_name' => $name,
                'permission_slug' => $slug,
                'module' => $module,
            ]);
        }
    }

    // Assign all permissions to admin
    $adminRole = $db->fetch("SELECT id FROM roles WHERE role_slug = 'admin'");
    if ($adminRole) {
        $allPerms = $db->fetchAll("SELECT id FROM permissions");
        foreach ($allPerms as $perm) {
            $existing = $db->fetch("SELECT id FROM role_permissions WHERE role_id = ? AND permission_id = ?", [$adminRole['id'], $perm['id']]);
            if (!$existing) {
                $db->insert('role_permissions', ['role_id' => $adminRole['id'], 'permission_id' => $perm['id']]);
            }
        }
    }

    // Assign permissions to dept_head
    $deptHead = $db->fetch("SELECT id FROM roles WHERE role_slug = 'dept_head'");
    if ($deptHead) {
        $deptPerms = [
            'dashboard.view', 'departments.view', 'levels.view',
            'members.view', 'members.create', 'members.edit',
            'subjects.view', 'subjects.create', 'subjects.edit', 'subjects.delete',
            'sections.view', 'sections.create', 'sections.edit', 'sections.delete',
            'classrooms.view', 'sessions.view',
            'membercourses.view', 'membercourses.create', 'membercourses.edit', 'membercourses.delete',
            'divisions.view', 'divisions.create', 'divisions.edit', 'divisions.delete',
            'scheduling.view', 'scheduling.manage', 'scheduling.pass_role', 'scheduling.admin',
            'priority.view', 'priority.manage', 'priority.grant_exception', 'priority.grant_register',
            'timetable.view', 'timetable.export',
            'notifications.view',
        ];
        foreach ($deptPerms as $slug) {
            $perm = $db->fetch("SELECT id FROM permissions WHERE permission_slug = ?", [$slug]);
            if ($perm) {
                $existing = $db->fetch("SELECT id FROM role_permissions WHERE role_id = ? AND permission_id = ?", [$deptHead['id'], $perm['id']]);
                if (!$existing) {
                    $db->insert('role_permissions', ['role_id' => $deptHead['id'], 'permission_id' => $perm['id']]);
                }
            }
        }
    }

    // Assign permissions to faculty
    $faculty = $db->fetch("SELECT id FROM roles WHERE role_slug = 'faculty'");
    if ($faculty) {
        $facultyPerms = [
            'dashboard.view', 'scheduling.view', 'scheduling.manage',
            'timetable.view', 'timetable.export', 'notifications.view',
            'membercourses.view',
        ];
        foreach ($facultyPerms as $slug) {
            $perm = $db->fetch("SELECT id FROM permissions WHERE permission_slug = ?", [$slug]);
            if ($perm) {
                $existing = $db->fetch("SELECT id FROM role_permissions WHERE role_id = ? AND permission_id = ?", [$faculty['id'], $perm['id']]);
                if (!$existing) {
                    $db->insert('role_permissions', ['role_id' => $faculty['id'], 'permission_id' => $perm['id']]);
                }
            }
        }
    }

    // Assign permissions to viewer
    $viewer = $db->fetch("SELECT id FROM roles WHERE role_slug = 'viewer'");
    if ($viewer) {
        $viewerPerms = [
            'dashboard.view', 'departments.view', 'levels.view', 'members.view',
            'subjects.view', 'sections.view', 'classrooms.view', 'sessions.view',
            'membercourses.view', 'scheduling.view', 'timetable.view', 'notifications.view',
        ];
        foreach ($viewerPerms as $slug) {
            $perm = $db->fetch("SELECT id FROM permissions WHERE permission_slug = ?", [$slug]);
            if ($perm) {
                $existing = $db->fetch("SELECT id FROM role_permissions WHERE role_id = ? AND permission_id = ?", [$viewer['id'], $perm['id']]);
                if (!$existing) {
                    $db->insert('role_permissions', ['role_id' => $viewer['id'], 'permission_id' => $perm['id']]);
                }
            }
        }
    }

    // Academic Degrees
    $degrees = [
        ['degree_name' => 'أستاذ', 'sort_order' => 1],
        ['degree_name' => 'أستاذ مشارك', 'sort_order' => 2],
        ['degree_name' => 'أستاذ مساعد', 'sort_order' => 3],
        ['degree_name' => 'محاضر', 'sort_order' => 4],
        ['degree_name' => 'معيد', 'sort_order' => 5],
    ];
    foreach ($degrees as $deg) {
        $existing = $db->fetch("SELECT id FROM academic_degrees WHERE degree_name = ?", [$deg['degree_name']]);
        if (!$existing) {
            $db->insert('academic_degrees', $deg);
        }
    }

    // Default settings
    $settings = [
        ['setting_key' => 'institution_name', 'setting_value' => 'الجامعة', 'setting_group' => 'general'],
        ['setting_key' => 'academic_year', 'setting_value' => '2025-2026', 'setting_group' => 'general'],
        ['setting_key' => 'semester', 'setting_value' => 'الفصل الأول', 'setting_group' => 'general'],
        ['setting_key' => 'sessions_per_day', 'setting_value' => '5', 'setting_group' => 'scheduling'],
        ['setting_key' => 'working_days', 'setting_value' => 'الأحد,الاثنين,الثلاثاء,الأربعاء,الخميس', 'setting_group' => 'scheduling'],
    ];
    foreach ($settings as $s) {
        $existing = $db->fetch("SELECT id FROM settings WHERE setting_key = ?", [$s['setting_key']]);
        if (!$existing) {
            $db->insert('settings', $s);
        }
    }

    // Create default admin user
    $adminUser = $db->fetch("SELECT id FROM users WHERE username = 'admin'");
    if (!$adminUser) {
        // Create admin faculty member first
        $memberId = $db->insert('faculty_members', [
            'member_name' => 'مدير النظام',
            'role' => 'مدير',
            'ranking' => 0,
            'is_active' => 1,
        ]);

        $adminRoleId = $db->fetchColumn("SELECT id FROM roles WHERE role_slug = 'admin'");
        $db->insert('users', [
            'username' => 'admin',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'member_id' => $memberId,
            'role_id' => $adminRoleId,
            'registration_status' => 1,
            'is_active' => 1,
        ]);
    }

    // Default academic year
    $yearExists = $db->fetch("SELECT id FROM academic_years LIMIT 1");
    if (!$yearExists) {
        $yearId = $db->insert('academic_years', [
            'year_name' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'is_current' => 1,
        ]);
        $db->insert('semesters', [
            'semester_name' => 'الفصل الدراسي الأول',
            'academic_year_id' => $yearId,
            'start_date' => '2025-09-01',
            'end_date' => '2026-01-15',
            'is_current' => 1,
        ]);
        $db->insert('semesters', [
            'semester_name' => 'الفصل الدراسي الثاني',
            'academic_year_id' => $yearId,
            'start_date' => '2026-02-01',
            'end_date' => '2026-06-30',
            'is_current' => 0,
        ]);
    }
};

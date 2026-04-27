<?php
return function (Database $db) {
    $prioritySettings = [
        ['setting_key' => 'priority_mode', 'setting_value' => 'disabled', 'setting_group' => 'priority'],
        ['setting_key' => 'priority_active_category_id', 'setting_value' => '', 'setting_group' => 'priority'],
        ['setting_key' => 'priority_current_group_id', 'setting_value' => '', 'setting_group' => 'priority'],
        ['setting_key' => 'priority_current_dept_id', 'setting_value' => '', 'setting_group' => 'priority'],
    ];

    foreach ($prioritySettings as $s) {
        $existing = $db->fetch("SELECT id FROM settings WHERE setting_key = ?", [$s['setting_key']]);
        if (!$existing) {
            $db->insert('settings', $s);
        }
    }
};

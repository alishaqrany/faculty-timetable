<?php
return function (Database $db) {
    $indexes = [
        ['subjects', 'idx_subjects_dept_level', 'department_id, level_id'],
        ['sessions', 'idx_sessions_day_start', 'day, start_time'],
        ['member_courses', 'idx_member_courses_section', 'section_id'],
        ['member_courses', 'idx_member_courses_subject', 'subject_id'],
        ['timetable', 'idx_timetable_member_course_session', 'member_course_id, session_id'],
    ];

    foreach ($indexes as [$table, $name, $columns]) {
        $exists = $db->fetch(
            "SHOW INDEX FROM `$table` WHERE Key_name = ?",
            [$name]
        );
        if (!$exists) {
            $db->raw("CREATE INDEX `$name` ON `$table` ($columns)");
        }
    }
};

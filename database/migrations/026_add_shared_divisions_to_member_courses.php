<?php
return function (\Database $db) {
    $cols = $db->fetchAll("SHOW COLUMNS FROM `member_courses` WHERE Field = 'is_shared'");
    if (empty($cols)) {
        $db->raw("ALTER TABLE `member_courses` ADD COLUMN `is_shared` TINYINT(1) DEFAULT 0 AFTER `assignment_type`");
    }

    $db->raw("CREATE TABLE IF NOT EXISTS `member_course_shared_divisions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `member_course_id` INT NOT NULL,
        `division_id` INT NOT NULL,
        FOREIGN KEY (`member_course_id`) REFERENCES `member_courses`(`member_course_id`) ON DELETE CASCADE,
        FOREIGN KEY (`division_id`) REFERENCES `divisions`(`division_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};
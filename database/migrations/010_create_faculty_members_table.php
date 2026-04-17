<?php
return function (Database $db) {
    $db->raw("CREATE TABLE IF NOT EXISTS `faculty_members` (
        `member_id` INT AUTO_INCREMENT PRIMARY KEY,
        `member_name` VARCHAR(200) NOT NULL,
        `email` VARCHAR(200) NULL,
        `phone` VARCHAR(30) NULL,
        `department_id` INT NULL,
        `degree_id` INT NULL,
        `degree` VARCHAR(100) NULL,
        `join_date` DATE NULL,
        `ranking` INT DEFAULT 0,
        `role` VARCHAR(50) DEFAULT 'عضو هيئة تدريس',
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`department_id`) REFERENCES `departments`(`department_id`) ON DELETE SET NULL,
        FOREIGN KEY (`degree_id`) REFERENCES `academic_degrees`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};

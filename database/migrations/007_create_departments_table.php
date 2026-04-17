<?php
return function (Database $db) {
    $db->raw("CREATE TABLE IF NOT EXISTS `departments` (
        `department_id` INT AUTO_INCREMENT PRIMARY KEY,
        `department_name` VARCHAR(200) NOT NULL,
        `department_code` VARCHAR(20) NULL,
        `description` TEXT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};

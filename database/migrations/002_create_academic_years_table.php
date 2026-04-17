<?php
return function (Database $db) {
    $db->raw("CREATE TABLE IF NOT EXISTS `academic_years` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `year_name` VARCHAR(100) NOT NULL,
        `start_date` DATE NULL,
        `end_date` DATE NULL,
        `is_current` TINYINT(1) DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};

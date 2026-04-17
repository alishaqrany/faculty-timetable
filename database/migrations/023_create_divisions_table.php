<?php
/**
 * Migration: Create divisions table
 * 
 * الشعب - تقسيم الفرقة إلى شعب (شعبة أ، شعبة ب، أو شعبة عامة)
 * كل شعبة تنتمي لقسم وفرقة معينة
 */
return function (Database $db) {
    $db->raw("CREATE TABLE IF NOT EXISTS `divisions` (
        `division_id` INT AUTO_INCREMENT PRIMARY KEY,
        `division_name` VARCHAR(100) NOT NULL,
        `department_id` INT NOT NULL,
        `level_id` INT NOT NULL,
        `capacity` INT DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`department_id`) REFERENCES `departments`(`department_id`) ON DELETE CASCADE,
        FOREIGN KEY (`level_id`) REFERENCES `levels`(`level_id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_division` (`department_id`, `level_id`, `division_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};

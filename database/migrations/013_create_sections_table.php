<?php
return function (Database $db) {
    $db->raw("CREATE TABLE IF NOT EXISTS `sections` (
        `section_id` INT AUTO_INCREMENT PRIMARY KEY,
        `section_name` VARCHAR(100) NOT NULL,
        `section_type` ENUM('شعبة','سكشن') NOT NULL DEFAULT 'شعبة',
        `department_id` INT NOT NULL,
        `level_id` INT NOT NULL,
        `parent_section_id` INT NULL,
        `capacity` INT DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`department_id`) REFERENCES `departments`(`department_id`) ON DELETE CASCADE,
        FOREIGN KEY (`level_id`) REFERENCES `levels`(`level_id`) ON DELETE CASCADE,
        FOREIGN KEY (`parent_section_id`) REFERENCES `sections`(`section_id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};

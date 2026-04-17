<?php
return function (Database $db) {
    $db->raw("CREATE TABLE IF NOT EXISTS `classrooms` (
        `classroom_id` INT AUTO_INCREMENT PRIMARY KEY,
        `classroom_name` VARCHAR(150) NOT NULL,
        `capacity` INT DEFAULT 0,
        `classroom_type` ENUM('قاعة','معمل','مدرج','قاعة اجتماعات') DEFAULT 'قاعة',
        `building` VARCHAR(100) NULL,
        `floor` VARCHAR(20) NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};

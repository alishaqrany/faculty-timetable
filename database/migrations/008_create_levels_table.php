<?php
return function (Database $db) {
    $db->raw("CREATE TABLE IF NOT EXISTS `levels` (
        `level_id` INT AUTO_INCREMENT PRIMARY KEY,
        `level_name` VARCHAR(200) NOT NULL,
        `level_code` VARCHAR(20) NULL,
        `sort_order` INT DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};

<?php
return function (Database $db) {
    $db->raw("CREATE TABLE IF NOT EXISTS `priority_categories` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `category_name` VARCHAR(200) NOT NULL,
        `description` TEXT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `sort_order` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};

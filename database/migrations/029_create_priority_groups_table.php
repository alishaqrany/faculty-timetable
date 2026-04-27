<?php
return function (Database $db) {
    $db->raw("CREATE TABLE IF NOT EXISTS `priority_groups` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `category_id` INT NOT NULL,
        `group_name` VARCHAR(200) NOT NULL,
        `sort_order` INT DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`category_id`) REFERENCES `priority_categories`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};

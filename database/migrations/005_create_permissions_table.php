<?php
return function (Database $db) {
    $db->raw("CREATE TABLE IF NOT EXISTS `permissions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `permission_name` VARCHAR(150) NOT NULL,
        `permission_slug` VARCHAR(100) NOT NULL,
        `module` VARCHAR(50) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `permission_slug_unique` (`permission_slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};

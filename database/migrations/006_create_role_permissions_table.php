<?php
return function (Database $db) {
    $db->raw("CREATE TABLE IF NOT EXISTS `role_permissions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `role_id` INT NOT NULL,
        `permission_id` INT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `role_permission_unique` (`role_id`, `permission_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};

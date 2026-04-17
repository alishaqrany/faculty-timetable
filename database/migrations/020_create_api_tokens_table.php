<?php
return function (Database $db) {
    $db->raw("CREATE TABLE IF NOT EXISTS `api_tokens` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `token` VARCHAR(128) NOT NULL,
        `name` VARCHAR(100) DEFAULT 'default',
        `expires_at` DATETIME NOT NULL,
        `last_used_at` DATETIME NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `token_unique` (`token`),
        INDEX `idx_token_user` (`user_id`),
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};

<?php
return function (Database $db) {
    $db->raw("CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(100) NOT NULL,
        `email` VARCHAR(200) NULL,
        `password` VARCHAR(255) NOT NULL,
        `member_id` INT NULL,
        `role_id` INT NULL,
        `registration_status` TINYINT(1) DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `last_login_at` DATETIME NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `username_unique` (`username`),
        FOREIGN KEY (`member_id`) REFERENCES `faculty_members`(`member_id`) ON DELETE SET NULL,
        FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};

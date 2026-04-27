<?php
return function (Database $db) {
    $db->raw("CREATE TABLE IF NOT EXISTS `priority_exceptions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `exception_type` ENUM('member','group') NOT NULL DEFAULT 'member',
        `member_id` INT NULL,
        `group_id` INT NULL,
        `granted_by` INT NOT NULL,
        `reason` TEXT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `expires_at` DATETIME NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`member_id`) REFERENCES `faculty_members`(`member_id`) ON DELETE CASCADE,
        FOREIGN KEY (`group_id`) REFERENCES `priority_groups`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`granted_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};

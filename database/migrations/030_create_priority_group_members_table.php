<?php
return function (Database $db) {
    $db->raw("CREATE TABLE IF NOT EXISTS `priority_group_members` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `group_id` INT NOT NULL,
        `member_id` INT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `group_member_unique` (`group_id`, `member_id`),
        FOREIGN KEY (`group_id`) REFERENCES `priority_groups`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`member_id`) REFERENCES `faculty_members`(`member_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};

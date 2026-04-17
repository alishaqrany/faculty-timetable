<?php
return function (Database $db) {
    $db->raw("CREATE TABLE IF NOT EXISTS `audit_logs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NULL,
        `action` VARCHAR(50) NOT NULL,
        `module` VARCHAR(50) NOT NULL,
        `record_id` INT NULL,
        `old_values` JSON NULL,
        `new_values` JSON NULL,
        `ip_address` VARCHAR(45) NULL,
        `user_agent` VARCHAR(500) NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_audit_user` (`user_id`),
        INDEX `idx_audit_module` (`module`),
        INDEX `idx_audit_created` (`created_at`),
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};

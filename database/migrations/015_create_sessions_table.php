<?php
return function (Database $db) {
    $db->raw("CREATE TABLE IF NOT EXISTS `sessions` (
        `session_id` INT AUTO_INCREMENT PRIMARY KEY,
        `day` VARCHAR(20) NOT NULL,
        `session_name` VARCHAR(100) NOT NULL,
        `start_time` TIME NOT NULL,
        `end_time` TIME NOT NULL,
        `duration` INT DEFAULT 2,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};

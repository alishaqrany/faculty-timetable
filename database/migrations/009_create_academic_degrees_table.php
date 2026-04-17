<?php
return function (Database $db) {
    $db->raw("CREATE TABLE IF NOT EXISTS `academic_degrees` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `degree_name` VARCHAR(150) NOT NULL,
        `sort_order` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};

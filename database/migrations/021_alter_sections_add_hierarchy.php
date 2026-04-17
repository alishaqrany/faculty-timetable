<?php
return function (Database $db) {
    $hasSectionType = $db->fetchColumn("SHOW COLUMNS FROM `sections` LIKE 'section_type'");
    if (!$hasSectionType) {
        $db->raw("ALTER TABLE `sections` ADD COLUMN `section_type` ENUM('شعبة','سكشن') NOT NULL DEFAULT 'شعبة' AFTER `section_name`");
    }

    $hasParentId = $db->fetchColumn("SHOW COLUMNS FROM `sections` LIKE 'parent_section_id'");
    if (!$hasParentId) {
        $db->raw("ALTER TABLE `sections` ADD COLUMN `parent_section_id` INT NULL AFTER `level_id`");
    }

    $hasParentIndex = $db->fetchColumn("SHOW INDEX FROM `sections` WHERE Key_name = 'idx_sections_parent_section_id'");
    if (!$hasParentIndex) {
        $db->raw("ALTER TABLE `sections` ADD INDEX `idx_sections_parent_section_id` (`parent_section_id`)");
    }

    $hasParentFk = $db->fetchColumn("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sections'
        AND COLUMN_NAME = 'parent_section_id' AND REFERENCED_TABLE_NAME = 'sections' LIMIT 1");
    if (!$hasParentFk) {
        $db->raw("ALTER TABLE `sections`
            ADD CONSTRAINT `fk_sections_parent_section`
            FOREIGN KEY (`parent_section_id`) REFERENCES `sections`(`section_id`)
            ON DELETE SET NULL ON UPDATE CASCADE");
    }
};

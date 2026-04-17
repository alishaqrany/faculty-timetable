<?php
/**
 * Migration: Make section_id nullable in member_courses
 * 
 * Originally section_id was NOT NULL, but theory assignments and
 * division-level practical assignments need section_id = NULL.
 */
return function (Database $db) {
    // Drop existing FK constraint on section_id (if exists)
    $fks = $db->fetchAll(
        "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = 'member_courses'
           AND COLUMN_NAME = 'section_id'
           AND REFERENCED_TABLE_NAME IS NOT NULL"
    );
    foreach ($fks as $fk) {
        $name = $fk['CONSTRAINT_NAME'];
        $db->raw("ALTER TABLE `member_courses` DROP FOREIGN KEY `{$name}`");
    }

    // Make section_id nullable
    $db->raw("ALTER TABLE `member_courses` MODIFY COLUMN `section_id` INT NULL DEFAULT NULL");

    // Re-add FK with ON DELETE SET NULL
    $db->raw("ALTER TABLE `member_courses` ADD CONSTRAINT `fk_mc_section_id`
        FOREIGN KEY (`section_id`) REFERENCES `sections`(`section_id`) ON DELETE SET NULL");
};

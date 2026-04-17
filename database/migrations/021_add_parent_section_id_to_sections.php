<?php
/**
 * Migration: Add parent_section_id and section_type to sections table
 * 
 * Creates hierarchy for divisions (شعب) and practical groups (سكاشن):
 * - parent_section_id NULL = Division (شعبة) - students attend theory together
 * - parent_section_id SET = Practical Group (سكشن) - subset for lab sessions
 * 
 * Note: This migration is idempotent - safe to run multiple times
 */
return function (Database $db) {
    // Check if parent_section_id column already exists
    $cols = $db->fetchAll("SHOW COLUMNS FROM `sections` WHERE Field = 'parent_section_id'");
    if (empty($cols)) {
        $db->raw("ALTER TABLE `sections` 
            ADD COLUMN `parent_section_id` INT NULL AFTER `level_id`");
        
        // Add foreign key constraint
        $db->raw("ALTER TABLE `sections` 
            ADD CONSTRAINT `fk_sections_parent` 
            FOREIGN KEY (`parent_section_id`) REFERENCES `sections`(`section_id`) 
            ON DELETE CASCADE");
    }
    
    // Check if section_type column already exists
    $cols = $db->fetchAll("SHOW COLUMNS FROM `sections` WHERE Field = 'section_type'");
    if (empty($cols)) {
        $db->raw("ALTER TABLE `sections` 
            ADD COLUMN `section_type` ENUM('شعبة','سكشن') DEFAULT 'شعبة' AFTER `parent_section_id`");
        
        // Update existing sections to be divisions (شعبة) - they have no parent
        $db->raw("UPDATE `sections` SET `section_type` = 'شعبة' WHERE `parent_section_id` IS NULL");
    }
};

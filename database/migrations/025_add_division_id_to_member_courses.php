<?php
/**
 * Migration: Add division_id to member_courses
 * 
 * للتكليفات النظرية: نربط بالشعبة (كل الشعبة تحضر معًا)
 * للتكليفات العملية: نربط بالسكشن (مجموعة أصغر)
 */
return function (Database $db) {
    // Add division_id column if not exists
    $cols = $db->fetchAll("SHOW COLUMNS FROM `member_courses` WHERE Field = 'division_id'");
    if (empty($cols)) {
        $db->raw("ALTER TABLE `member_courses` ADD COLUMN `division_id` INT NULL AFTER `subject_id`");
        
        // Add foreign key
        $db->raw("ALTER TABLE `member_courses` ADD CONSTRAINT `fk_member_courses_division` 
            FOREIGN KEY (`division_id`) REFERENCES `divisions`(`division_id`) ON DELETE SET NULL");
    }
    
    // Update existing records: set division_id from section's division
    $db->raw("UPDATE member_courses mc 
              JOIN sections s ON mc.section_id = s.section_id 
              SET mc.division_id = s.division_id 
              WHERE mc.division_id IS NULL AND s.division_id IS NOT NULL");
};

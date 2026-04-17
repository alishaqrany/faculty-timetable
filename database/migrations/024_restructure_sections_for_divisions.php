<?php
/**
 * Migration: Restructure sections table to link to divisions
 * 
 * السكاشن (مجموعات العملي) - كل سكشن ينتمي لشعبة معينة
 * الشعبة تحدد القسم والفرقة، فلا نحتاج تكرارها هنا
 */
return function (Database $db) {
    // Step 1: Add division_id column
    $cols = $db->fetchAll("SHOW COLUMNS FROM `sections` WHERE Field = 'division_id'");
    if (empty($cols)) {
        $db->raw("ALTER TABLE `sections` ADD COLUMN `division_id` INT NULL AFTER `section_id`");
    }
    
    // Step 2: Migrate existing data - create divisions from existing sections
    // Each unique (department_id, level_id) combination becomes a division named "عامة"
    $existingSections = $db->fetchAll("SELECT DISTINCT department_id, level_id FROM sections WHERE division_id IS NULL");
    
    foreach ($existingSections as $sec) {
        // Check if division already exists
        $existing = $db->fetch(
            "SELECT division_id FROM divisions WHERE department_id = ? AND level_id = ? AND division_name = 'عامة'",
            [$sec['department_id'], $sec['level_id']]
        );
        
        if (!$existing) {
            // Create a default "عامة" division
            $db->insert('divisions', [
                'division_name' => 'عامة',
                'department_id' => $sec['department_id'],
                'level_id' => $sec['level_id'],
                'capacity' => 100,
                'is_active' => 1
            ]);
            $divisionId = $db->lastInsertId();
        } else {
            $divisionId = $existing['division_id'];
        }
        
        // Update sections to link to this division
        $divisionIdInt = (int)$divisionId;
        $departmentIdInt = (int)$sec['department_id'];
        $levelIdInt = (int)$sec['level_id'];
        $db->raw(
            "UPDATE sections
             SET division_id = {$divisionIdInt}
             WHERE department_id = {$departmentIdInt}
               AND level_id = {$levelIdInt}
               AND division_id IS NULL"
        );
    }
    
    // Step 3: Add foreign key for division_id (only if not exists)
    try {
        $db->raw("ALTER TABLE `sections` ADD CONSTRAINT `fk_sections_division` 
            FOREIGN KEY (`division_id`) REFERENCES `divisions`(`division_id`) ON DELETE CASCADE");
    } catch (Exception $e) {
        // FK may already exist
    }
    
    // Step 4: Drop old columns that are no longer needed
    // Drop parent_section_id if exists
    $cols = $db->fetchAll("SHOW COLUMNS FROM `sections` WHERE Field = 'parent_section_id'");
    if (!empty($cols)) {
        // Drop any foreign key constraints that reference this column (name may differ by install)
        $constraints = $db->fetchAll(
            "SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'sections'
               AND COLUMN_NAME = 'parent_section_id'
               AND REFERENCED_TABLE_NAME IS NOT NULL"
        );

        foreach ($constraints as $constraint) {
            $name = $constraint['CONSTRAINT_NAME'] ?? '';
            if ($name !== '') {
                try {
                    $db->raw("ALTER TABLE `sections` DROP FOREIGN KEY `{$name}`");
                } catch (Exception $e) {
                    // ignore and continue
                }
            }
        }

        $db->raw("ALTER TABLE `sections` DROP COLUMN `parent_section_id`");
    }
    
    // Drop section_type if exists
    $cols = $db->fetchAll("SHOW COLUMNS FROM `sections` WHERE Field = 'section_type'");
    if (!empty($cols)) {
        $db->raw("ALTER TABLE `sections` DROP COLUMN `section_type`");
    }
    
    // Note: Keeping department_id and level_id for now for backwards compatibility
    // They can be made nullable or removed in a future migration
};

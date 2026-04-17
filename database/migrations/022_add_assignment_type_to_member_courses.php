<?php
/**
 * Migration: Add assignment_type to member_courses table
 * 
 * Distinguishes between theory and practical teaching assignments:
 * - نظري (Theory): Assigned to a division (شعبة), all students attend together
 * - عملي (Practical): Assigned to a practical group (سكشن), smaller subset
 * 
 * Note: This migration is idempotent - safe to run multiple times
 */
return function (Database $db) {
    // Check if column already exists
    $cols = $db->fetchAll("SHOW COLUMNS FROM `member_courses` WHERE Field = 'assignment_type'");
    if (empty($cols)) {
        $db->raw("ALTER TABLE `member_courses` 
            ADD COLUMN `assignment_type` ENUM('نظري','عملي') DEFAULT 'نظري' AFTER `section_id`");
    }
};

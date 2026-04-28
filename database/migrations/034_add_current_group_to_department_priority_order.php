<?php
return function (Database $db) {
    // Add current_group_id column to support asynchronous individual department state progression
    // in parallel_dept mode.
    try {
        $db->raw("ALTER TABLE `department_priority_order` ADD COLUMN IF NOT EXISTS `current_group_id` INT NULL DEFAULT NULL AFTER `sort_order`");
    } catch (\Exception $e) {
        // Column might already exist
        error_log("Migration 034 note: " . $e->getMessage());
    }
};

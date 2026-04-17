<?php
return function (Database $db) {
    $db->raw("CREATE TABLE IF NOT EXISTS `timetable` (
        `timetable_id` INT AUTO_INCREMENT PRIMARY KEY,
        `member_course_id` INT NOT NULL,
        `classroom_id` INT NOT NULL,
        `session_id` INT NOT NULL,
        `semester_id` INT NULL,
        `academic_year_id` INT NULL,
        `status` ENUM('مسودة','منشور') DEFAULT 'مسودة',
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`member_course_id`) REFERENCES `member_courses`(`member_course_id`) ON DELETE CASCADE,
        FOREIGN KEY (`classroom_id`) REFERENCES `classrooms`(`classroom_id`) ON DELETE CASCADE,
        FOREIGN KEY (`session_id`) REFERENCES `sessions`(`session_id`) ON DELETE CASCADE,
        FOREIGN KEY (`semester_id`) REFERENCES `semesters`(`id`) ON DELETE SET NULL,
        FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years`(`id`) ON DELETE SET NULL,
        FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};

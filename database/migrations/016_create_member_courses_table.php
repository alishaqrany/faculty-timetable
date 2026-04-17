<?php
return function (Database $db) {
    $db->raw("CREATE TABLE IF NOT EXISTS `member_courses` (
        `member_course_id` INT AUTO_INCREMENT PRIMARY KEY,
        `member_id` INT NOT NULL,
        `subject_id` INT NOT NULL,
        `section_id` INT NOT NULL,
        `semester_id` INT NULL,
        `academic_year_id` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`member_id`) REFERENCES `faculty_members`(`member_id`) ON DELETE CASCADE,
        FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`subject_id`) ON DELETE CASCADE,
        FOREIGN KEY (`section_id`) REFERENCES `sections`(`section_id`) ON DELETE CASCADE,
        FOREIGN KEY (`semester_id`) REFERENCES `semesters`(`id`) ON DELETE SET NULL,
        FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
};

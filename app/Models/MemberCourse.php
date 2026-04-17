<?php
namespace App\Models;
require_once APP_ROOT . '/core/Model.php';

class MemberCourse extends \Model
{
    protected static string $table = 'member_courses';
    protected static string $primaryKey = 'member_course_id';
    protected static array $fillable = ['member_id', 'subject_id', 'section_id', 'semester_id', 'academic_year_id'];

    public static function allWithDetails(string $orderBy = 'fm.member_name ASC'): array
    {
        return static::query(
            "SELECT mc.*, fm.member_name, s.subject_name, s.subject_type,
                    sec.section_name, sec.section_type, sec.parent_section_id,
                    parent.section_name AS parent_section_name,
                    d.department_name, l.level_name
             FROM member_courses mc
             JOIN faculty_members fm ON mc.member_id = fm.member_id
             JOIN subjects s ON mc.subject_id = s.subject_id
             JOIN sections sec ON mc.section_id = sec.section_id
             LEFT JOIN sections parent ON sec.parent_section_id = parent.section_id
             JOIN departments d ON s.department_id = d.department_id
             JOIN levels l ON s.level_id = l.level_id
             ORDER BY $orderBy"
        );
    }

    public static function findWithDetails(int $id): ?array
    {
        return static::queryOne(
            "SELECT mc.*, fm.member_name, s.subject_name, s.subject_type,
                    sec.section_name, sec.section_type, sec.parent_section_id,
                    parent.section_name AS parent_section_name
             FROM member_courses mc
             JOIN faculty_members fm ON mc.member_id = fm.member_id
             JOIN subjects s ON mc.subject_id = s.subject_id
             JOIN sections sec ON mc.section_id = sec.section_id
             LEFT JOIN sections parent ON sec.parent_section_id = parent.section_id
             WHERE mc.member_course_id = ?",
            [$id]
        );
    }

    public static function forMember(int $memberId): array
    {
        return static::query(
            "SELECT mc.*, s.subject_name, s.subject_type,
                    sec.section_name, sec.section_type, sec.parent_section_id,
                    parent.section_name AS parent_section_name
             FROM member_courses mc
             JOIN subjects s ON mc.subject_id = s.subject_id
             JOIN sections sec ON mc.section_id = sec.section_id
             LEFT JOIN sections parent ON sec.parent_section_id = parent.section_id
             WHERE mc.member_id = ?
             ORDER BY s.subject_name",
            [$memberId]
        );
    }
}

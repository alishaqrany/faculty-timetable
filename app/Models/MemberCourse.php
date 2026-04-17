<?php
namespace App\Models;
require_once APP_ROOT . '/core/Model.php';

class MemberCourse extends \Model
{
    protected static string $table = 'member_courses';
    protected static string $primaryKey = 'member_course_id';
    protected static array $fillable = ['member_id', 'subject_id', 'division_id', 'section_id', 'assignment_type', 'is_shared', 'semester_id', 'academic_year_id'];

    public static function allWithDetails(string $orderBy = 'fm.member_name ASC'): array
    {
        return static::query(
            "SELECT mc.*, fm.member_name, s.subject_name, s.subject_type,
                    (
                      CASE 
                        WHEN mc.is_shared = 1 THEN
                          (SELECT CONCAT('محاضرة مشتركة (', COUNT(*), ' شعب)') FROM member_course_shared_divisions WHERE member_course_id = mc.member_course_id)
                        ELSE dv.division_name
                      END
                    ) as division_name, sec.section_name,
                    d.department_name, l.level_name
             FROM member_courses mc
             JOIN faculty_members fm ON mc.member_id = fm.member_id
             JOIN subjects s ON mc.subject_id = s.subject_id
             LEFT JOIN divisions dv ON mc.division_id = dv.division_id
             LEFT JOIN sections sec ON mc.section_id = sec.section_id
             JOIN departments d ON s.department_id = d.department_id
             JOIN levels l ON s.level_id = l.level_id
             ORDER BY $orderBy"
        );
    }

    public static function findWithDetails(int $id): ?array
    {
        return static::queryOne(
            "SELECT mc.*, fm.member_name, s.subject_name, s.subject_type,
                    (
                      CASE 
                        WHEN mc.is_shared = 1 THEN
                          (SELECT CONCAT('محاضرة مشتركة (', COUNT(*), ' شعب)') FROM member_course_shared_divisions WHERE member_course_id = mc.member_course_id)
                        ELSE dv.division_name
                      END
                    ) as division_name, sec.section_name,
                    d.department_name, l.level_name
             FROM member_courses mc
             JOIN faculty_members fm ON mc.member_id = fm.member_id
             JOIN subjects s ON mc.subject_id = s.subject_id
             LEFT JOIN divisions dv ON mc.division_id = dv.division_id
             LEFT JOIN sections sec ON mc.section_id = sec.section_id
             LEFT JOIN departments d ON s.department_id = d.department_id
             LEFT JOIN levels l ON s.level_id = l.level_id
             WHERE mc.member_course_id = ?",
            [$id]
        );
    }

    public static function forMember(int $memberId): array
    {
        return static::query(
            "SELECT mc.*, s.subject_name, s.subject_type,
                    (
                      CASE 
                        WHEN mc.is_shared = 1 THEN
                          (SELECT CONCAT('محاضرة مشتركة (', COUNT(*), ' شعب)') FROM member_course_shared_divisions WHERE member_course_id = mc.member_course_id)
                        ELSE dv.division_name
                      END
                    ) as division_name, sec.section_name
             FROM member_courses mc
             JOIN subjects s ON mc.subject_id = s.subject_id
             LEFT JOIN divisions dv ON mc.division_id = dv.division_id
             LEFT JOIN sections sec ON mc.section_id = sec.section_id
             WHERE mc.member_id = ?
             ORDER BY s.subject_name",
            [$memberId]
        );
    }

    public static function theoryAssignments(): array
    {
        return static::query(
            "SELECT mc.*, fm.member_name, s.subject_name,
                    (
                      CASE 
                        WHEN mc.is_shared = 1 THEN
                          (SELECT CONCAT('محاضرة مشتركة (', COUNT(*), ' شعب)') FROM member_course_shared_divisions WHERE member_course_id = mc.member_course_id)
                        ELSE dv.division_name
                      END
                    ) as division_name,
                    d.department_name, l.level_name
             FROM member_courses mc
             JOIN faculty_members fm ON mc.member_id = fm.member_id
             JOIN subjects s ON mc.subject_id = s.subject_id
             LEFT JOIN divisions dv ON mc.division_id = dv.division_id
             JOIN departments d ON s.department_id = d.department_id
             JOIN levels l ON s.level_id = l.level_id
             WHERE mc.assignment_type = 'نظري'
             ORDER BY fm.member_name, s.subject_name"
        );
    }

    public static function practicalAssignments(): array
    {
        return static::query(
            "SELECT mc.*, fm.member_name, s.subject_name, sec.section_name,
                    COALESCE(dv2.division_name, dv.division_name) as division_name,
                    COALESCE(d2.department_name, d.department_name) as department_name,
                    COALESCE(l2.level_name, l.level_name) as level_name
             FROM member_courses mc
             JOIN faculty_members fm ON mc.member_id = fm.member_id
             JOIN subjects s ON mc.subject_id = s.subject_id
             LEFT JOIN sections sec ON mc.section_id = sec.section_id
             LEFT JOIN divisions dv ON sec.division_id = dv.division_id
             LEFT JOIN divisions dv2 ON mc.division_id = dv2.division_id
             LEFT JOIN departments d ON dv.department_id = d.department_id
             LEFT JOIN departments d2 ON dv2.department_id = d2.department_id
             LEFT JOIN levels l ON dv.level_id = l.level_id
             LEFT JOIN levels l2 ON dv2.level_id = l2.level_id
             WHERE mc.assignment_type = 'عملي'
             ORDER BY fm.member_name, s.subject_name"
        );
    }

    public static function forDivision(int $divisionId): array
    {
        return static::query(
            "SELECT mc.*, fm.member_name, s.subject_name, s.subject_type
             FROM member_courses mc
             JOIN faculty_members fm ON mc.member_id = fm.member_id
             JOIN subjects s ON mc.subject_id = s.subject_id
             WHERE (mc.division_id = ? OR (mc.is_shared = 1 AND ? IN (SELECT mcs.division_id FROM member_course_shared_divisions mcs WHERE mcs.member_course_id = mc.member_course_id)))
               AND mc.assignment_type = 'نظري'
             ORDER BY s.subject_name",
            [$divisionId, $divisionId]
        );
    }

    public static function forSection(int $sectionId): array
    {
        return static::query(
            "SELECT mc.*, fm.member_name, s.subject_name, s.subject_type
             FROM member_courses mc
             JOIN faculty_members fm ON mc.member_id = fm.member_id
             JOIN subjects s ON mc.subject_id = s.subject_id
             WHERE mc.section_id = ? AND mc.assignment_type = 'عملي'
             ORDER BY s.subject_name",
            [$sectionId]
        );
    }
}

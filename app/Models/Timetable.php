<?php
namespace App\Models;
require_once APP_ROOT . '/core/Model.php';

class Timetable extends \Model
{
    protected static string $table = 'timetable';
    protected static string $primaryKey = 'timetable_id';
    protected static array $fillable = [
        'member_course_id', 'classroom_id', 'session_id',
        'semester_id', 'academic_year_id', 'status', 'created_by',
    ];

    public static function allWithDetails(array $filters = []): array
    {
        $sql = "SELECT t.*, mc.member_id, mc.subject_id, mc.section_id, mc.assignment_type, mc.is_shared,
                       fm.member_name, s.subject_name,
                       (
                         CASE 
                           WHEN mc.assignment_type = 'عملي' AND mc.section_id IS NOT NULL THEN 
                             (SELECT sec.section_name FROM sections sec WHERE sec.section_id = mc.section_id LIMIT 1)
                           WHEN mc.assignment_type = 'عملي' AND mc.division_id IS NOT NULL THEN
                             CONCAT('عملي - ', (SELECT divi.division_name FROM divisions divi WHERE divi.division_id = mc.division_id LIMIT 1))
                           WHEN mc.assignment_type = 'نظري' AND mc.is_shared = 0 THEN
                             (SELECT divi.division_name FROM divisions divi WHERE divi.division_id = mc.division_id LIMIT 1)
                           WHEN mc.assignment_type = 'نظري' AND mc.is_shared = 1 THEN
                             (SELECT CONCAT('محاضرة مشتركة (', COUNT(*), ' شعب)') FROM member_course_shared_divisions mcs WHERE mcs.member_course_id = mc.member_course_id)
                           ELSE 'محاضرة عامة'
                         END
                       ) AS section_name,
                       c.classroom_name, sess.day, sess.session_name,
                       sess.start_time, sess.end_time,
                       d.department_name, d.department_id,
                       l.level_name, l.level_id
                FROM timetable t
                JOIN member_courses mc ON t.member_course_id = mc.member_course_id
                JOIN faculty_members fm ON mc.member_id = fm.member_id
                JOIN subjects s ON mc.subject_id = s.subject_id
                JOIN classrooms c ON t.classroom_id = c.classroom_id
                JOIN sessions sess ON t.session_id = sess.session_id
                JOIN departments d ON s.department_id = d.department_id
                JOIN levels l ON s.level_id = l.level_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['department_id'])) {
            $sql .= " AND d.department_id = ?";
            $params[] = $filters['department_id'];
        }
        if (!empty($filters['level_id'])) {
            $sql .= " AND l.level_id = ?";
            $params[] = $filters['level_id'];
        }
        if (!empty($filters['member_id'])) {
            $sql .= " AND fm.member_id = ?";
            $params[] = $filters['member_id'];
        }
        if (!empty($filters['classroom_id'])) {
            $sql .= " AND c.classroom_id = ?";
            $params[] = $filters['classroom_id'];
        }
        if (!empty($filters['academic_year_id'])) {
            $sql .= " AND t.academic_year_id = ?";
            $params[] = $filters['academic_year_id'];
        }
        if (!empty($filters['semester_id'])) {
            $sql .= " AND t.semester_id = ?";
            $params[] = $filters['semester_id'];
        }

        $sql .= " ORDER BY FIELD(sess.day,'الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس'), sess.start_time";

        return static::query($sql, $params);
    }

    /**
     * Build pivot table for display: rows = (day, session), cols = sections.
     */
    public static function pivotTable(int $departmentId, int $levelId, array $extraFilters = []): array
    {
        $entries = static::allWithDetails(array_merge([
            'department_id' => $departmentId,
            'level_id'      => $levelId,
        ], $extraFilters));

        $days = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];
        $sections = [];
        $pivot = [];
        $data = [];

        $sessions = \Database::getInstance()->fetchAll(
            "SELECT MIN(session_id) AS session_id,
                    session_name,
                    start_time,
                    end_time,
                    CONCAT(session_name, '|', start_time, '|', end_time) AS slot_key
             FROM sessions
             WHERE is_active = 1
             GROUP BY session_name, start_time, end_time
             ORDER BY start_time"
        );

        foreach ($entries as $entry) {
            $key = $entry['day'] . '|' . $entry['session_name'];
            $secName = $entry['section_name'] ?? 'محاضرة عامة';
            $sections[$secName] = true;
            $pivot[$key][$secName] = $entry;

            $slotKey = ($entry['session_name'] ?? '') . '|' . ($entry['start_time'] ?? '') . '|' . ($entry['end_time'] ?? '');
            if ($slotKey !== '||') {
                $data[$entry['day']][$slotKey][] = $entry;
            }
        }

        return [
            'days'     => $days,
            'sessions' => $sessions,
            'data'     => $data,
            'sections' => array_keys($sections),
            'pivot'    => $pivot,
            'entries'  => $entries,
        ];
    }
}

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
        $sql = "SELECT t.*, mc.member_id, mc.subject_id, mc.section_id,
                   fm.member_name, s.subject_name, sec.section_name, sec.section_type,
                   sec.parent_section_id, parent.section_name AS parent_section_name,
                       c.classroom_name, sess.day, sess.session_name,
                       sess.start_time, sess.end_time,
                       d.department_name, d.department_id,
                       l.level_name, l.level_id
                FROM timetable t
                JOIN member_courses mc ON t.member_course_id = mc.member_course_id
                JOIN faculty_members fm ON mc.member_id = fm.member_id
                JOIN subjects s ON mc.subject_id = s.subject_id
                JOIN sections sec ON mc.section_id = sec.section_id
                LEFT JOIN sections parent ON sec.parent_section_id = parent.section_id
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

        $sql .= " ORDER BY FIELD(sess.day,'الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس'), sess.start_time";

        return static::query($sql, $params);
    }

    /**
     * Build pivot table for display: rows = (day, session), cols = sections.
     */
    public static function pivotTable(int $departmentId, int $levelId): array
    {
        $entries = static::allWithDetails([
            'department_id' => $departmentId,
            'level_id'      => $levelId,
        ]);

        $days = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];
        $sections = [];
        $pivot = [];
        $data = [];

        $sessions = \Database::getInstance()->fetchAll(
            "SELECT session_id, session_name, day, start_time, end_time
             FROM sessions
             WHERE is_active = 1
             ORDER BY FIELD(day,'الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس'), start_time"
        );

        foreach ($entries as $entry) {
            $key = $entry['day'] . '|' . $entry['session_name'];
            $secName = $entry['section_name'];
            $sections[$secName] = true;
            $pivot[$key][$secName] = $entry;

            // For timetable view compatibility: one cell per (day, session_id)
            // may contain multiple entries when overlaps exist.
            $sessionId = (int)($entry['session_id'] ?? 0);
            if ($sessionId > 0) {
                $data[$entry['day']][$sessionId][] = $entry;
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

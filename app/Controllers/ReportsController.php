<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/Department.php';
require_once APP_ROOT . '/app/Models/Member.php';
require_once APP_ROOT . '/app/Models/Subject.php';
require_once APP_ROOT . '/app/Models/Section.php';
require_once APP_ROOT . '/app/Models/Classroom.php';
require_once APP_ROOT . '/app/Models/Level.php';

use App\Models\{Department, Member, Subject, Section, Classroom, Level};

class ReportsController extends \Controller
{
    public function index(): void
    {
        $db = \Database::getInstance();

        // ── Global counts ────────────────────────────────────────────
        $activeSessions = (int)($db->fetch("SELECT COUNT(*) AS c FROM sessions WHERE is_active=1")['c'] ?? 0);
        $totalDays = 5;
        $totalSlots = $activeSessions * $totalDays;

        // ── Section 1: General summary ───────────────────────────────
        $summary = [
            'departments'       => (int)($db->fetch("SELECT COUNT(*) c FROM departments WHERE is_active=1")['c'] ?? 0),
            'members'           => (int)($db->fetch("SELECT COUNT(*) c FROM faculty_members WHERE is_active=1")['c'] ?? 0),
            'subjects'          => (int)($db->fetch("SELECT COUNT(*) c FROM subjects WHERE is_active=1")['c'] ?? 0),
            'sections'          => (int)($db->fetch("SELECT COUNT(*) c FROM sections WHERE is_active=1")['c'] ?? 0),
            'classrooms'        => (int)($db->fetch("SELECT COUNT(*) c FROM classrooms WHERE is_active=1")['c'] ?? 0),
            'active_sessions'   => $activeSessions,
            'assignments'       => (int)($db->fetch("SELECT COUNT(*) c FROM member_courses")['c'] ?? 0),
            'placed'            => (int)($db->fetch("SELECT COUNT(DISTINCT member_course_id) c FROM timetable")['c'] ?? 0),
        ];
        $summary['unplaced']  = max(0, $summary['assignments'] - $summary['placed']);
        $summary['coverage']  = $summary['assignments'] > 0
            ? round(($summary['placed'] / $summary['assignments']) * 100, 1)
            : 0.0;

        // ── Section 2: Placement analysis by department ──────────────
        $placementByDept = $db->fetchAll(
            "SELECT d.department_name,
                    COUNT(DISTINCT mc.member_course_id)                              AS total_assignments,
                    COUNT(DISTINCT t.member_course_id)                               AS placed_assignments,
                    COUNT(DISTINCT mc.member_course_id) - COUNT(DISTINCT t.member_course_id) AS unplaced
             FROM departments d
             LEFT JOIN subjects s  ON s.department_id  = d.department_id
             LEFT JOIN member_courses mc ON mc.subject_id = s.subject_id
             LEFT JOIN timetable t  ON t.member_course_id = mc.member_course_id
             WHERE d.is_active = 1
             GROUP BY d.department_id, d.department_name
             ORDER BY total_assignments DESC"
        );

        // ── Section 3: Placement analysis by level ───────────────────
        $placementByLevel = $db->fetchAll(
            "SELECT l.level_name,
                    COUNT(DISTINCT mc.member_course_id) AS total_assignments,
                    COUNT(DISTINCT t.member_course_id)  AS placed_assignments
             FROM levels l
             LEFT JOIN subjects s  ON s.level_id = l.level_id
             LEFT JOIN member_courses mc ON mc.subject_id = s.subject_id
             LEFT JOIN timetable t  ON t.member_course_id = mc.member_course_id
             WHERE l.is_active = 1
             GROUP BY l.level_id, l.level_name
             ORDER BY total_assignments DESC"
        );

        // ── Section 4: Faculty workload ──────────────────────────────
        $memberWorkload = $db->fetchAll(
            "SELECT m.member_name, m.degree,
                    d.department_name,
                    COUNT(DISTINCT mc.member_course_id)    AS assigned_courses,
                    COUNT(DISTINCT t.timetable_id)         AS placed_lectures
             FROM faculty_members m
             LEFT JOIN departments d  ON d.department_id = m.department_id
             LEFT JOIN member_courses mc ON mc.member_id = m.member_id
             LEFT JOIN timetable t  ON t.member_course_id = mc.member_course_id
             WHERE m.is_active = 1
             GROUP BY m.member_id, m.member_name, m.degree, d.department_name
             ORDER BY placed_lectures DESC"
        );

        $membersNoAssignment = $db->fetchAll(
            "SELECT m.member_name, d.department_name
             FROM faculty_members m
             LEFT JOIN departments d ON d.department_id = m.department_id
             LEFT JOIN member_courses mc ON mc.member_id = m.member_id
             WHERE m.is_active = 1 AND mc.member_course_id IS NULL
             ORDER BY d.department_name, m.member_name"
        );

        // ── Section 5: Classroom utilization ────────────────────────
        $classroomUtil = $db->fetchAll(
            "SELECT c.classroom_name, c.classroom_type, c.capacity,
                    COUNT(t.timetable_id) AS used_slots,
                    ROUND(COUNT(t.timetable_id) / GREATEST(?,1) * 100, 1) AS utilization_pct
             FROM classrooms c
             LEFT JOIN timetable t ON t.classroom_id = c.classroom_id
             WHERE c.is_active = 1
             GROUP BY c.classroom_id, c.classroom_name, c.classroom_type, c.capacity
             ORDER BY utilization_pct DESC",
            [$totalSlots]
        );

        $unusedClassrooms = array_filter($classroomUtil, fn($r) => (int)$r['used_slots'] === 0);

        // ── Section 6: Subject stats ─────────────────────────────────
        $subjectStats = $db->fetchAll(
            "SELECT s.subject_name, s.subject_code, d.department_name,
                    COUNT(DISTINCT mc.member_course_id)  AS assignment_count,
                    COUNT(DISTINCT t.timetable_id)        AS placed_count
             FROM subjects s
             LEFT JOIN departments d  ON d.department_id = s.department_id
             LEFT JOIN member_courses mc ON mc.subject_id = s.subject_id
             LEFT JOIN timetable t  ON t.member_course_id = mc.member_course_id
             WHERE s.is_active = 1
             GROUP BY s.subject_id, s.subject_name, s.subject_code, d.department_name
             ORDER BY assignment_count DESC"
        );

        $subjectsNotPlaced = array_filter($subjectStats,
            fn($r) => (int)$r['assignment_count'] > 0 && (int)$r['placed_count'] === 0
        );

        // ── Section 7: Day & session distribution ────────────────────
        $dayDistribution = $db->fetchAll(
            "SELECT sess.day,
                    COUNT(t.timetable_id) AS lecture_count,
                    COUNT(DISTINCT t.classroom_id) AS classrooms_used
             FROM sessions sess
             LEFT JOIN timetable t ON t.session_id = sess.session_id
             WHERE sess.is_active = 1
             GROUP BY sess.day
             ORDER BY FIELD(sess.day,'الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس')"
        );

        $sessionDistribution = $db->fetchAll(
            "SELECT sess.session_name, sess.day, sess.start_time, sess.end_time,
                    COUNT(t.timetable_id) AS lecture_count
             FROM sessions sess
             LEFT JOIN timetable t ON t.session_id = sess.session_id
             WHERE sess.is_active = 1
             GROUP BY sess.session_id, sess.session_name, sess.day, sess.start_time, sess.end_time
             ORDER BY FIELD(sess.day,'الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس'), sess.start_time"
        );

        // ── Section 8: Sections & divisions ─────────────────────────
        $sectionsByDept = $db->fetchAll(
            "SELECT d.department_name, l.level_name,
                    COUNT(DISTINCT sec.section_id) AS section_count
             FROM departments d
             LEFT JOIN divisions dv ON dv.department_id = d.department_id AND dv.is_active = 1
             LEFT JOIN levels l ON l.level_id = dv.level_id
             LEFT JOIN sections sec ON sec.division_id = dv.division_id AND sec.is_active = 1
             WHERE d.is_active = 1
             GROUP BY d.department_id, d.department_name, l.level_id, l.level_name
             ORDER BY d.department_name, l.level_name"
        );

        // ── Section 9: Top conflicts / double-booked slots ───────────
        $conflictSlots = $db->fetchAll(
            "SELECT sess.day, sess.session_name,
                    COUNT(t.timetable_id) AS lecture_count,
                    COUNT(DISTINCT t.classroom_id) AS classrooms_used,
                    COUNT(DISTINCT t.timetable_id) - COUNT(DISTINCT t.classroom_id) AS potential_conflicts
             FROM sessions sess
             JOIN timetable t ON t.session_id = sess.session_id
             WHERE sess.is_active = 1
             GROUP BY sess.session_id, sess.day, sess.session_name
             HAVING lecture_count > 1
             ORDER BY lecture_count DESC
             LIMIT 10"
        );

        // ── Section 10: Unplaced assignments full list ───────────────
        $unplacedList = $db->fetchAll(
            "SELECT mc.member_course_id, m.member_name, s.subject_name, s.subject_code,
                    d.department_name, l.level_name
             FROM member_courses mc
             JOIN faculty_members m ON m.member_id = mc.member_id
             JOIN subjects s ON s.subject_id = mc.subject_id
             LEFT JOIN departments d ON d.department_id = s.department_id
             LEFT JOIN levels l ON l.level_id = s.level_id
             LEFT JOIN timetable t ON t.member_course_id = mc.member_course_id
             WHERE t.timetable_id IS NULL
             ORDER BY d.department_name, m.member_name"
        );

        $this->render('reports.index', [
            'summary'              => $summary,
            'totalSlots'           => $totalSlots,
            'placementByDept'      => $placementByDept,
            'placementByLevel'     => $placementByLevel,
            'memberWorkload'       => $memberWorkload,
            'membersNoAssignment'  => $membersNoAssignment,
            'classroomUtil'        => $classroomUtil,
            'unusedClassrooms'     => array_values($unusedClassrooms),
            'subjectStats'         => $subjectStats,
            'subjectsNotPlaced'    => array_values($subjectsNotPlaced),
            'dayDistribution'      => $dayDistribution,
            'sessionDistribution'  => $sessionDistribution,
            'sectionsByDept'       => $sectionsByDept,
            'conflictSlots'        => $conflictSlots,
            'unplacedList'         => $unplacedList,
        ]);
    }
}

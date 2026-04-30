<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/Department.php';
require_once APP_ROOT . '/app/Models/Member.php';
require_once APP_ROOT . '/app/Models/Subject.php';
require_once APP_ROOT . '/app/Models/Section.php';
require_once APP_ROOT . '/app/Models/Classroom.php';
require_once APP_ROOT . '/app/Models/Timetable.php';
require_once APP_ROOT . '/app/Models/AuditLog.php';
require_once APP_ROOT . '/app/Models/Notification.php';
require_once APP_ROOT . '/app/Models/Level.php';

use App\Models\{Department, Member, Subject, Section, Classroom, Timetable, AuditLog, Notification, Level};

class DashboardController extends \Controller
{
    public function index(): void
    {
        $db = \Database::getInstance();
        $departments = Department::active();
        $levels = Level::active();
        $academicYears = $db->fetchAll(
            "SELECT id, year_name
             FROM academic_years
             WHERE is_active = 1
             ORDER BY start_date DESC"
        );
        $semesters = $db->fetchAll(
            "SELECT s.id, s.semester_name, s.academic_year_id, ay.year_name
             FROM semesters s
             JOIN academic_years ay ON s.academic_year_id = ay.id
             WHERE s.is_active = 1
             ORDER BY ay.start_date DESC, s.start_date ASC"
        );

        $filters = [
            'department_id'    => $this->request->input('department_id'),
            'level_id'         => $this->request->input('level_id'),
            'academic_year_id' => $this->request->input('academic_year_id'),
            'semester_id'      => $this->request->input('semester_id'),
        ];
        $hasFilter = !empty(array_filter($filters));

        $timetableFilterSql = '';
        $timetableFilterParams = [];
        if (!empty($filters['department_id'])) {
            $timetableFilterSql .= " AND s.department_id = ?";
            $timetableFilterParams[] = (int)$filters['department_id'];
        }
        if (!empty($filters['level_id'])) {
            $timetableFilterSql .= " AND s.level_id = ?";
            $timetableFilterParams[] = (int)$filters['level_id'];
        }
        if (!empty($filters['academic_year_id'])) {
            $timetableFilterSql .= " AND t.academic_year_id = ?";
            $timetableFilterParams[] = (int)$filters['academic_year_id'];
        }
        if (!empty($filters['semester_id'])) {
            $timetableFilterSql .= " AND t.semester_id = ?";
            $timetableFilterParams[] = (int)$filters['semester_id'];
        }

        $stats = [
            'departments' => Department::count(),
            'members'     => Member::count(),
            'subjects'    => Subject::count(),
            'sections'    => Section::count(),
            'classrooms'  => Classroom::count(),
            'timetable'   => (int)($db->fetch(
                "SELECT COUNT(*) AS cnt
                 FROM timetable t
                 JOIN member_courses mc ON t.member_course_id = mc.member_course_id
                 JOIN subjects s ON mc.subject_id = s.subject_id
                 WHERE 1 = 1 {$timetableFilterSql}",
                $timetableFilterParams
            )['cnt'] ?? 0),
        ];

        $activeSessionsRow = $db->fetch("SELECT COUNT(*) AS cnt FROM sessions WHERE is_active = 1");
        $activeSessions = (int)($activeSessionsRow['cnt'] ?? 0);
        $workingDays = 5;

        // Chart data: entries per day
        $dayData = $db->fetchAll(
            "SELECT sess.day, COUNT(*) as cnt
             FROM timetable t
             JOIN member_courses mc ON t.member_course_id = mc.member_course_id
             JOIN subjects s ON mc.subject_id = s.subject_id
             JOIN sessions sess ON t.session_id = sess.session_id
             WHERE 1 = 1 {$timetableFilterSql}
             GROUP BY sess.day
             ORDER BY FIELD(sess.day,'الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس')",
             $timetableFilterParams
        );

        // Classroom occupancy: sessions used vs total capacity
        $classroomOccupancy = $db->fetchAll(
            "SELECT c.classroom_name as name, COUNT(t.timetable_id) as used_slots
             FROM classrooms c
             LEFT JOIN timetable t ON c.classroom_id = t.classroom_id
             LEFT JOIN member_courses mc ON t.member_course_id = mc.member_course_id
             LEFT JOIN subjects s ON mc.subject_id = s.subject_id
             WHERE c.is_active = 1
             {$timetableFilterSql}
             GROUP BY c.classroom_id, c.classroom_name
             ORDER BY used_slots DESC
             LIMIT 10",
             $timetableFilterParams
        );

        // Total available slots per classroom (5 days * sessions per day)
        $totalSlots = $activeSessions * $workingDays;

        // Faculty workload: sessions per member (top 10)
        $facultyWorkload = $db->fetchAll(
            "SELECT m.member_name as name, COUNT(t.timetable_id) as session_count
             FROM faculty_members m
             LEFT JOIN member_courses mc ON m.member_id = mc.member_id
             LEFT JOIN timetable t ON mc.member_course_id = t.member_course_id
             LEFT JOIN subjects s ON mc.subject_id = s.subject_id
             WHERE m.is_active = 1
             {$timetableFilterSql}
             GROUP BY m.member_id, m.member_name
             ORDER BY session_count DESC
             LIMIT 10",
            $timetableFilterParams
        );

        $departmentLoad = $db->fetchAll(
            "SELECT d.department_name AS department, COUNT(t.timetable_id) AS lecture_count
             FROM departments d
             LEFT JOIN subjects s ON d.department_id = s.department_id
             LEFT JOIN member_courses mc ON s.subject_id = mc.subject_id
             LEFT JOIN timetable t ON mc.member_course_id = t.member_course_id
             WHERE d.is_active = 1
             {$timetableFilterSql}
             GROUP BY d.department_id, d.department_name
             ORDER BY lecture_count DESC
             LIMIT 8",
             $timetableFilterParams
        );

        $assignmentFilterSql = '';
        $assignmentFilterParams = [];
        if (!empty($filters['department_id'])) {
            $assignmentFilterSql .= " AND s.department_id = ?";
            $assignmentFilterParams[] = (int)$filters['department_id'];
        }
        if (!empty($filters['level_id'])) {
            $assignmentFilterSql .= " AND s.level_id = ?";
            $assignmentFilterParams[] = (int)$filters['level_id'];
        }
        if (!empty($filters['academic_year_id'])) {
            $assignmentFilterSql .= " AND mc.academic_year_id = ?";
            $assignmentFilterParams[] = (int)$filters['academic_year_id'];
        }
        if (!empty($filters['semester_id'])) {
            $assignmentFilterSql .= " AND mc.semester_id = ?";
            $assignmentFilterParams[] = (int)$filters['semester_id'];
        }

        $coverageRow = $db->fetch(
            "SELECT
                COUNT(*) AS total_assignments,
                SUM(CASE WHEN t.timetable_id IS NULL THEN 1 ELSE 0 END) AS unscheduled_assignments
             FROM member_courses mc
             JOIN subjects s ON mc.subject_id = s.subject_id
             LEFT JOIN timetable t ON mc.member_course_id = t.member_course_id
             WHERE 1 = 1 {$assignmentFilterSql}",
            $assignmentFilterParams
        );
        $totalAssignments = (int)($coverageRow['total_assignments'] ?? 0);
        $unscheduledAssignments = (int)($coverageRow['unscheduled_assignments'] ?? 0);
        $scheduledAssignments = max(0, $totalAssignments - $unscheduledAssignments);
        $scheduleCoverage = $totalAssignments > 0
            ? round(($scheduledAssignments / $totalAssignments) * 100, 1)
            : 0.0;

        $utilizationByDay = [];
        foreach ($dayData as $row) {
            $count = (int)($row['cnt'] ?? 0);
            $dailyCapacity = $activeSessions * max(1, $stats['classrooms']);
            $utilizationByDay[] = [
                'day' => $row['day'] ?? '',
                'lecture_count' => $count,
                'utilization_percent' => $dailyCapacity > 0 ? round(($count / $dailyCapacity) * 100, 1) : 0.0,
            ];
        }

        $busiestDay = null;
        if (!empty($dayData)) {
            $busiestDay = $dayData[0];
            foreach ($dayData as $row) {
                if ((int)($row['cnt'] ?? 0) > (int)($busiestDay['cnt'] ?? 0)) {
                    $busiestDay = $row;
                }
            }
        }

        // Recent audit logs
        $recentLogs = AuditLog::allWithUser(5);

        // User notifications
        $notifications = [];
        if ($this->session->isLoggedIn()) {
            $notifications = Notification::unreadForUser($this->session->userId(), 5);
        }

        $this->render('dashboard.index', [
            'departments'         => $departments,
            'levels'              => $levels,
            'academicYears'       => $academicYears,
            'semesters'           => $semesters,
            'filters'             => $filters,
            'hasFilter'           => $hasFilter,
            'stats'               => $stats,
            'dayData'             => $dayData,
            'classroomOccupancy'  => $classroomOccupancy,
            'totalSlots'          => $totalSlots,
            'facultyWorkload'     => $facultyWorkload,
            'departmentLoad'      => $departmentLoad,
            'utilizationByDay'    => $utilizationByDay,
            'totalAssignments'    => $totalAssignments,
            'scheduledAssignments'=> $scheduledAssignments,
            'unscheduledAssignments' => $unscheduledAssignments,
            'scheduleCoverage'    => $scheduleCoverage,
            'busiestDay'          => $busiestDay,
            'recentLogs'          => $recentLogs,
            'notifications'       => $notifications,
        ]);
    }
}

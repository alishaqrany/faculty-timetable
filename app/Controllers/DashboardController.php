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

use App\Models\{Department, Member, Subject, Section, Classroom, Timetable, AuditLog, Notification};

class DashboardController extends \Controller
{
    public function index(): void
    {
        $db = \Database::getInstance();

        $stats = [
            'departments' => Department::count(),
            'members'     => Member::count(),
            'subjects'    => Subject::count(),
            'sections'    => Section::count(),
            'classrooms'  => Classroom::count(),
            'timetable'   => Timetable::count(),
        ];

        // Chart data: entries per day
        $dayData = $db->fetchAll(
            "SELECT sess.day, COUNT(*) as cnt
             FROM timetable t
             JOIN sessions sess ON t.session_id = sess.session_id
             GROUP BY sess.day
             ORDER BY FIELD(sess.day,'الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس')"
        );

        // Classroom occupancy: sessions used vs total capacity
        $classroomOccupancy = $db->fetchAll(
            "SELECT c.classroom_name as name, COUNT(t.timetable_id) as used_slots
             FROM classrooms c
             LEFT JOIN timetable t ON c.classroom_id = t.classroom_id
             WHERE c.is_active = 1
             GROUP BY c.classroom_id, c.classroom_name
             ORDER BY used_slots DESC
             LIMIT 10"
        );

        // Total available slots per classroom (5 days * sessions per day)
        $totalSessions = (int)($db->fetch(
            "SELECT COUNT(*) as cnt FROM sessions WHERE is_active = 1"
        )['cnt'] ?? 0);
        $totalSlots = $totalSessions * 5; // 5 working days

        // Faculty workload: sessions per member (top 10)
        $facultyWorkload = $db->fetchAll(
            "SELECT m.member_name as name, COUNT(t.timetable_id) as session_count
             FROM faculty_members m
             LEFT JOIN member_courses mc ON m.member_id = mc.member_id
             LEFT JOIN timetable t ON mc.member_course_id = t.member_course_id
             WHERE m.is_active = 1
             GROUP BY m.member_id, m.member_name
             ORDER BY session_count DESC
             LIMIT 10"
        );

        // Recent audit logs
        $recentLogs = AuditLog::allWithUser(5);

        // User notifications
        $notifications = [];
        if ($this->session->isLoggedIn()) {
            $notifications = Notification::unreadForUser($this->session->userId(), 5);
        }

        $this->render('dashboard.index', [
            'stats'               => $stats,
            'dayData'             => $dayData,
            'classroomOccupancy'  => $classroomOccupancy,
            'totalSlots'          => $totalSlots,
            'facultyWorkload'     => $facultyWorkload,
            'recentLogs'          => $recentLogs,
            'notifications'       => $notifications,
        ]);
    }
}

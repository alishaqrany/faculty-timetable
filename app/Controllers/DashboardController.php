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
        $stats = [
            'departments' => Department::count(),
            'members'     => Member::count(),
            'subjects'    => Subject::count(),
            'sections'    => Section::count(),
            'classrooms'  => Classroom::count(),
            'timetable'   => Timetable::count(),
        ];

        // Chart data: entries per day
        $dayData = \Database::getInstance()->fetchAll(
            "SELECT sess.day, COUNT(*) as cnt
             FROM timetable t
             JOIN sessions sess ON t.session_id = sess.session_id
             GROUP BY sess.day
             ORDER BY FIELD(sess.day,'الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس')"
        );

        // Recent audit logs
        $recentLogs = AuditLog::allWithUser(5);

        // User notifications
        $notifications = [];
        if ($this->session->isLoggedIn()) {
            $notifications = Notification::unreadForUser($this->session->userId(), 5);
        }

        $this->render('dashboard.index', [
            'stats'         => $stats,
            'dayData'       => $dayData,
            'recentLogs'    => $recentLogs,
            'notifications' => $notifications,
        ]);
    }
}

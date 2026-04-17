<?php
namespace App\Controllers\Api;

require_once APP_ROOT . '/app/Models/Timetable.php';
require_once APP_ROOT . '/app/Models/Department.php';
require_once APP_ROOT . '/app/Models/Member.php';
require_once APP_ROOT . '/app/Models/Classroom.php';
require_once APP_ROOT . '/app/Models/Subject.php';
require_once APP_ROOT . '/app/Models/Section.php';
require_once APP_ROOT . '/app/Models/Session.php';

use App\Models\{Timetable, Department, Member, Classroom, Subject, Section, Session};

class TimetableController extends \Controller
{
    public function index(): void
    {
        $filters = [
            'department_id' => $this->request->input('department_id'),
            'level_id'      => $this->request->input('level_id'),
            'member_id'     => $this->request->input('member_id'),
            'classroom_id'  => $this->request->input('classroom_id'),
        ];

        $entries = Timetable::allWithDetails(array_filter($filters));
        $this->json(['data' => $entries, 'count' => count($entries)]);
    }

    public function departments(): void
    {
        $departments = Department::active();
        $this->json(['data' => $departments]);
    }

    public function members(): void
    {
        $db = \Database::getInstance();
        $members = $db->fetchAll(
            "SELECT m.id, m.name, m.academic_degree, d.name as department_name
             FROM members m
             LEFT JOIN departments d ON m.department_id = d.id
             WHERE m.is_active = 1
             ORDER BY m.name"
        );
        $this->json(['data' => $members, 'count' => count($members)]);
    }

    public function classrooms(): void
    {
        $classrooms = Classroom::active();
        $this->json(['data' => $classrooms, 'count' => count($classrooms)]);
    }

    public function subjects(): void
    {
        $db = \Database::getInstance();
        $subjects = $db->fetchAll(
            "SELECT s.id, s.name, s.code, d.name as department_name
             FROM subjects s
             LEFT JOIN departments d ON s.department_id = d.id
             WHERE s.is_active = 1
             ORDER BY s.name"
        );
        $this->json(['data' => $subjects, 'count' => count($subjects)]);
    }

    public function sections(): void
    {
        $db = \Database::getInstance();
        $sections = $db->fetchAll(
            "SELECT s.id, s.name, d.name as department_name, l.name as level_name
             FROM sections s
             LEFT JOIN departments d ON s.department_id = d.id
             LEFT JOIN levels l ON s.level_id = l.id
             WHERE s.is_active = 1
             ORDER BY s.name"
        );
        $this->json(['data' => $sections, 'count' => count($sections)]);
    }

    public function sessions(): void
    {
        $sessions = Session::active();
        $this->json(['data' => $sessions, 'count' => count($sessions)]);
    }
}

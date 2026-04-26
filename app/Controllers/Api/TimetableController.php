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
            "SELECT fm.member_id AS id, fm.member_name AS name, ad.degree_name AS academic_degree,
                    d.department_name
             FROM faculty_members fm
             LEFT JOIN academic_degrees ad ON fm.degree_id = ad.id
             LEFT JOIN departments d ON fm.department_id = d.department_id
             WHERE fm.is_active = 1
             ORDER BY fm.member_name"
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
            "SELECT s.subject_id AS id, s.subject_name AS name, s.subject_code AS code,
                    d.department_name
             FROM subjects s
             LEFT JOIN departments d ON s.department_id = d.department_id
             WHERE s.is_active = 1
             ORDER BY s.subject_name"
        );
        $this->json(['data' => $subjects, 'count' => count($subjects)]);
    }

    public function sections(): void
    {
        $db = \Database::getInstance();
        $sections = $db->fetchAll(
            "SELECT s.section_id AS id, s.section_name AS name,
                    d.department_name, l.level_name
             FROM sections s
             LEFT JOIN departments d ON s.department_id = d.department_id
             LEFT JOIN levels l ON s.level_id = l.level_id
             WHERE s.is_active = 1
             ORDER BY s.section_name"
        );
        $this->json(['data' => $sections, 'count' => count($sections)]);
    }

    public function sessions(): void
    {
        $sessions = Session::active();
        $this->json(['data' => $sessions, 'count' => count($sessions)]);
    }
}

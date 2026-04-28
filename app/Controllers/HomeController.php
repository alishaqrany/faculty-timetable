<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/Department.php';
require_once APP_ROOT . '/app/Models/Level.php';
require_once APP_ROOT . '/app/Models/Subject.php';
require_once APP_ROOT . '/app/Models/Member.php';
require_once APP_ROOT . '/app/Models/Classroom.php';
require_once APP_ROOT . '/app/Models/Timetable.php';

use App\Models\{Department, Level, Subject, Member, Classroom, Timetable};

class HomeController extends \Controller
{
    public function index(): void
    {
        $filters = [
            'department_id' => $this->normalizeId($this->request->input('department_id')),
            'level_id'      => $this->normalizeId($this->request->input('level_id')),
            'member_id'     => $this->normalizeId($this->request->input('member_id')),
            'classroom_id'  => $this->normalizeId($this->request->input('classroom_id')),
        ];

        $data = [
            'institutionName' => config('app.name', 'نظام إدارة الجداول الدراسية'),
            'stats' => [
                'departments' => 0,
                'levels'      => 0,
                'subjects'    => 0,
                'members'     => 0,
                'classrooms'  => 0,
                'timetable'   => 0,
            ],
            'departments'   => [],
            'levels'        => [],
            'members'       => [],
            'classrooms'    => [],
            'filters'       => $filters,
            'hasFilter'     => !empty(array_filter($filters)),
            'entries'       => [],
            'recentEntries' => [],
            'pivotData'     => null,
            'insights'      => [
                'topDepartment' => null,
                'topMember'     => null,
                'topClassroom'  => null,
            ],
            'setupRequired' => false,
        ];

        try {
            $db = \Database::getInstance();
            $institutionName = $db->fetchColumn(
                "SELECT setting_value FROM settings WHERE setting_key = 'institution_name'"
            );

            if (is_string($institutionName) && $institutionName !== '') {
                $data['institutionName'] = $institutionName;
            }

            $data['stats'] = [
                'departments' => Department::count(['is_active' => 1]),
                'levels'      => Level::count(['is_active' => 1]),
                'subjects'    => Subject::count(['is_active' => 1]),
                'members'     => Member::count(['is_active' => 1]),
                'classrooms'  => Classroom::count(['is_active' => 1]),
                'timetable'   => Timetable::count(),
            ];

            $data['departments'] = Department::active();
            $data['levels'] = Level::active();
            $data['members'] = Member::active();
            $data['classrooms'] = Classroom::active();

            $filters['department_id'] = $this->resolveActiveId($data['departments'], 'department_id', $filters['department_id']);
            $filters['level_id'] = $this->resolveActiveId($data['levels'], 'level_id', $filters['level_id']);
            $filters['member_id'] = $this->resolveActiveId($data['members'], 'member_id', $filters['member_id']);
            $filters['classroom_id'] = $this->resolveActiveId($data['classrooms'], 'classroom_id', $filters['classroom_id']);
            $data['filters'] = $filters;
            $data['hasFilter'] = !empty(array_filter($filters));

            $data['recentEntries'] = $db->fetchAll(
                "SELECT s.subject_name, fm.member_name, c.classroom_name,
                        sess.day, sess.session_name, sess.start_time, sess.end_time,
                        d.department_name, l.level_name
                 FROM timetable t
                 JOIN member_courses mc ON t.member_course_id = mc.member_course_id
                 JOIN faculty_members fm ON mc.member_id = fm.member_id
                 JOIN subjects s ON mc.subject_id = s.subject_id
                 JOIN classrooms c ON t.classroom_id = c.classroom_id
                 JOIN sessions sess ON t.session_id = sess.session_id
                 JOIN departments d ON s.department_id = d.department_id
                 JOIN levels l ON s.level_id = l.level_id
                 ORDER BY FIELD(sess.day,'الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس'), sess.start_time
                 LIMIT 10"
            );

              $data['insights']['topDepartment'] = $db->fetch(
                 "SELECT d.department_name AS name, COUNT(t.timetable_id) AS total
                  FROM timetable t
                  JOIN member_courses mc ON t.member_course_id = mc.member_course_id
                  JOIN subjects s ON mc.subject_id = s.subject_id
                  JOIN departments d ON s.department_id = d.department_id
                  GROUP BY d.department_id, d.department_name
                  ORDER BY total DESC, d.department_name ASC
                  LIMIT 1"
              );

              $data['insights']['topMember'] = $db->fetch(
                 "SELECT fm.member_name AS name, COUNT(t.timetable_id) AS total
                  FROM timetable t
                  JOIN member_courses mc ON t.member_course_id = mc.member_course_id
                  JOIN faculty_members fm ON mc.member_id = fm.member_id
                  GROUP BY fm.member_id, fm.member_name
                  ORDER BY total DESC, fm.member_name ASC
                  LIMIT 1"
              );

              $data['insights']['topClassroom'] = $db->fetch(
                 "SELECT c.classroom_name AS name, COUNT(t.timetable_id) AS total
                  FROM timetable t
                  JOIN classrooms c ON t.classroom_id = c.classroom_id
                  GROUP BY c.classroom_id, c.classroom_name
                  ORDER BY total DESC, c.classroom_name ASC
                  LIMIT 1"
              );

            if ($data['hasFilter']) {
                $activeFilters = array_filter($filters, static fn($value) => $value !== null);
                $data['entries'] = Timetable::allWithDetails($activeFilters);

                if (!empty($filters['department_id']) && !empty($filters['level_id'])) {
                    $data['pivotData'] = Timetable::pivotTable(
                        (int) $filters['department_id'],
                        (int) $filters['level_id']
                    );
                }
            }
        } catch (\Throwable $exception) {
            $data['setupRequired'] = true;
        }

        $this->render('home.index', $data);
    }

    private function normalizeId($value): ?int
    {
        $filtered = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $filtered === false ? null : (int) $filtered;
    }

    private function resolveActiveId(array $rows, string $idKey, ?int $value): ?int
    {
        if ($value === null) {
            return null;
        }

        foreach ($rows as $row) {
            if ((int) ($row[$idKey] ?? 0) === $value) {
                return $value;
            }
        }

        return null;
    }
}
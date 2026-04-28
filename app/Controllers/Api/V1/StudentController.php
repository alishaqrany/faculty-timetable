<?php
namespace App\Controllers\Api\V1;

require_once APP_ROOT . '/app/Models/Timetable.php';

use App\Models\Timetable;

class StudentController extends BaseApiController
{
    public function timetable(): void
    {
        $departmentId = (int)$this->request->input('department_id', 0);
        $levelId = (int)$this->request->input('level_id', 0);

        if ($departmentId <= 0 || $levelId <= 0) {
            $this->fail('department_id و level_id مطلوبان', 422);
        }

        $entries = Timetable::allWithDetails([
            'department_id' => $departmentId,
            'level_id' => $levelId,
        ]);

        $this->ok($entries, 'Timetable loaded', ['count' => count($entries)]);
    }

    public function sectionTimetable(): void
    {
        $sectionId = (int)$this->request->input('section_id', 0);
        if ($sectionId <= 0) {
            $this->fail('section_id مطلوب', 422);
        }

        $db = \Database::getInstance();
        $entries = $db->fetchAll(
            "SELECT t.*, mc.member_id, mc.subject_id, mc.section_id, fm.member_name, s.subject_name,
                    c.classroom_name, sess.day, sess.session_name, sess.start_time, sess.end_time
             FROM timetable t
             JOIN member_courses mc ON t.member_course_id = mc.member_course_id
             JOIN faculty_members fm ON mc.member_id = fm.member_id
             JOIN subjects s ON mc.subject_id = s.subject_id
             JOIN classrooms c ON t.classroom_id = c.classroom_id
             JOIN sessions sess ON t.session_id = sess.session_id
             WHERE mc.section_id = ?
             ORDER BY FIELD(sess.day,'الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس'), sess.start_time",
            [$sectionId]
        );

        $this->ok($entries, 'Section timetable loaded', ['count' => count($entries)]);
    }

    public function todayLectures(): void
    {
        $date = (string)$this->request->input('date', date('Y-m-d'));
        $timestamp = strtotime($date) ?: time();
        $dayMap = [
            'Sunday' => 'الأحد',
            'Monday' => 'الاثنين',
            'Tuesday' => 'الثلاثاء',
            'Wednesday' => 'الأربعاء',
            'Thursday' => 'الخميس',
            'Friday' => 'الجمعة',
            'Saturday' => 'السبت',
        ];
        $dayEn = date('l', $timestamp);
        $dayAr = $dayMap[$dayEn] ?? 'الأحد';

        $sectionId = (int)$this->request->input('section_id', 0);
        $departmentId = (int)$this->request->input('department_id', 0);
        $levelId = (int)$this->request->input('level_id', 0);

        $db = \Database::getInstance();
        if ($sectionId > 0) {
            $rows = $db->fetchAll(
                "SELECT t.*, s.subject_name, fm.member_name, c.classroom_name, sess.day, sess.session_name,
                        sess.start_time, sess.end_time
                 FROM timetable t
                 JOIN member_courses mc ON t.member_course_id = mc.member_course_id
                 JOIN subjects s ON mc.subject_id = s.subject_id
                 JOIN faculty_members fm ON mc.member_id = fm.member_id
                 JOIN classrooms c ON t.classroom_id = c.classroom_id
                 JOIN sessions sess ON t.session_id = sess.session_id
                 WHERE mc.section_id = ? AND sess.day = ?
                 ORDER BY sess.start_time",
                [$sectionId, $dayAr]
            );
        } elseif ($departmentId > 0 && $levelId > 0) {
            $rows = $db->fetchAll(
                "SELECT t.*, s.subject_name, fm.member_name, c.classroom_name, sess.day, sess.session_name,
                        sess.start_time, sess.end_time, d.department_name, l.level_name
                 FROM timetable t
                 JOIN member_courses mc ON t.member_course_id = mc.member_course_id
                 JOIN subjects s ON mc.subject_id = s.subject_id
                 JOIN faculty_members fm ON mc.member_id = fm.member_id
                 JOIN classrooms c ON t.classroom_id = c.classroom_id
                 JOIN sessions sess ON t.session_id = sess.session_id
                 JOIN departments d ON s.department_id = d.department_id
                 JOIN levels l ON s.level_id = l.level_id
                 WHERE d.department_id = ? AND l.level_id = ? AND sess.day = ?
                 ORDER BY sess.start_time",
                [$departmentId, $levelId, $dayAr]
            );
        } else {
            $this->fail('حدد section_id أو (department_id + level_id)', 422);
        }

        $this->ok([
            'date' => date('Y-m-d', $timestamp),
            'day' => $dayAr,
            'lectures' => $rows,
        ], 'Today lectures loaded', ['count' => count($rows)]);
    }
}

<?php
namespace App\Services;

class SchedulingService
{
    /**
     * Check for scheduling conflicts.
     * Returns array of conflict descriptions, empty if no conflicts.
     */
    public static function checkConflicts(
        int $classroomId,
        int $sessionId,
        int $memberCourseId,
        ?int $excludeTimetableId = null,
        ?int $semesterId = null,
        ?int $academicYearId = null
    ): array {
        $db = \Database::getInstance();
        $conflicts = [];

        // Get member_course details (use LEFT JOIN since section might be null for theory courses)
        $mc = $db->fetch(
            "SELECT mc.*, s.department_id, s.level_id
             FROM member_courses mc
             JOIN subjects s ON mc.subject_id = s.subject_id
             WHERE mc.member_course_id = ?",
            [$memberCourseId]
        );
        if (!$mc) return ['تكليف التدريس غير موجود'];

        $excludeClause = '';
        $baseParams = [];
        if ($excludeTimetableId) {
            $excludeClause = ' AND t.timetable_id != ?';
            $baseParams[] = $excludeTimetableId;
        }

        // Semester/year awareness: only conflict with same semester/year
        $semClause = '';
        if ($semesterId) {
            $semClause .= ' AND (t.semester_id IS NULL OR t.semester_id = ?)';
            $baseParams[] = $semesterId;
        }
        if ($academicYearId) {
            $semClause .= ' AND (t.academic_year_id IS NULL OR t.academic_year_id = ?)';
            $baseParams[] = $academicYearId;
        }

        // 1. Classroom conflict: same classroom + same session
        $params = array_merge([$classroomId, $sessionId], $baseParams);
        $classroomConflict = $db->fetch(
            "SELECT t.*, c.classroom_name, sess.session_name, sess.day
             FROM timetable t
             JOIN classrooms c ON t.classroom_id = c.classroom_id
             JOIN sessions sess ON t.session_id = sess.session_id
             WHERE t.classroom_id = ? AND t.session_id = ? $excludeClause $semClause
             LIMIT 1",
            $params
        );
        if ($classroomConflict) {
            $conflicts[] = "القاعة \"{$classroomConflict['classroom_name']}\" محجوزة في {$classroomConflict['day']} - {$classroomConflict['session_name']}";
        }

        // 2. Division / Section overlap conflict (Student schedule conflicts)
        $myDivisions = [];
        $mySections = [];

        if ($mc['assignment_type'] === 'نظري') {
            if ($mc['is_shared']) {
                $shared = $db->fetchAll("SELECT division_id FROM member_course_shared_divisions WHERE member_course_id = ?", [$memberCourseId]);
                $myDivisions = array_column($shared, 'division_id');
            } elseif ($mc['division_id']) {
                $myDivisions[] = $mc['division_id'];
            }
        } elseif ($mc['assignment_type'] === 'عملي') {
            if ($mc['section_id']) {
                $mySections[] = $mc['section_id'];
            } elseif ($mc['division_id']) {
                // Practical at division level — treat like theory division overlap
                $myDivisions[] = $mc['division_id'];
            }
        }
        
        // Inherit sections from divisions
        if (!empty($myDivisions)) {
            $divPlaceholders = implode(',', array_fill(0, count($myDivisions), '?'));
            $divSecs = $db->fetchAll("SELECT section_id FROM sections WHERE division_id IN ($divPlaceholders)", $myDivisions);
            foreach ($divSecs as $ds) {
                if (!in_array($ds['section_id'], $mySections)) {
                    $mySections[] = $ds['section_id'];
                }
            }
        }

        // Now see if existing classes overlap
        $sessParams = array_merge([$sessionId], $baseParams);
        $overlapSQL = "SELECT t.*, sess.session_name, sess.day, mc2.subject_id, s2.subject_name, mc2.assignment_type, mc2.member_course_id AS mc2_id
             FROM timetable t
             JOIN member_courses mc2 ON t.member_course_id = mc2.member_course_id
             JOIN subjects s2 ON mc2.subject_id = s2.subject_id
             JOIN sessions sess ON t.session_id = sess.session_id
             WHERE t.session_id = ? $excludeClause $semClause";

        $overlappingClasses = $db->fetchAll($overlapSQL, $sessParams);
        
        foreach ($overlappingClasses as $ex) {
            $exDivs = [];
            $exSecs = [];
            $exM = $db->fetch("SELECT is_shared, division_id, section_id, assignment_type FROM member_courses WHERE member_course_id = ?", [$ex['mc2_id']]);
            
            if ($exM['assignment_type'] === 'نظري') {
                if ($exM['is_shared']) {
                    $sh = $db->fetchAll("SELECT division_id FROM member_course_shared_divisions WHERE member_course_id = ?", [$ex['mc2_id']]);
                    $exDivs = array_column($sh, 'division_id');
                } elseif ($exM['division_id']) {
                    $exDivs[] = $exM['division_id'];
                }
            } elseif ($exM['assignment_type'] === 'عملي') {
                if ($exM['section_id']) {
                    $exSecs[] = $exM['section_id'];
                } elseif ($exM['division_id']) {
                    // Practical at division level
                    $exDivs[] = $exM['division_id'];
                }
            }

            // Expand divisions to their sections for overlap check
            if (!empty($exDivs)) {
                $ph = implode(',', array_fill(0, count($exDivs), '?'));
                $secRows = $db->fetchAll("SELECT section_id FROM sections WHERE division_id IN ($ph)", $exDivs);
                $exSecs = array_merge($exSecs, array_column($secRows, 'section_id'));
            }
            
            $intersectDivs = array_intersect($myDivisions, $exDivs);
            $intersectSecs = array_intersect($mySections, $exSecs);
            
            if (!empty($intersectDivs) || !empty($intersectSecs)) {
                $conflicts[] = "يوجد تعارض مع مجموعة طلابية: لديهم مادة \"{$ex['subject_name']}\" في نفس الفترة ({$ex['day']} - {$ex['session_name']})";
                break;
            }
        }

        // 3. Faculty conflict: same member at same session
        $params = array_merge([$sessionId, $mc['member_id']], $baseParams);
        $memberConflict = $db->fetch(
            "SELECT t.*, fm.member_name, sess.session_name, sess.day
             FROM timetable t
             JOIN member_courses mc2 ON t.member_course_id = mc2.member_course_id
             JOIN faculty_members fm ON mc2.member_id = fm.member_id
             JOIN sessions sess ON t.session_id = sess.session_id
             WHERE t.session_id = ? AND mc2.member_id = ? $excludeClause $semClause
             LIMIT 1",
            $params
        );
        if ($memberConflict) {
            $conflicts[] = "عضو هيئة التدريس \"{$memberConflict['member_name']}\" لديه محاضرة في نفس الفترة ({$memberConflict['day']} - {$memberConflict['session_name']})";
        }

        // 4. Duplicate entry: same member_course + same session
        $dupParams = array_merge([$memberCourseId, $sessionId], $baseParams);
        $dupConflict = $db->fetch(
            "SELECT t.timetable_id FROM timetable t
             WHERE t.member_course_id = ? AND t.session_id = ? $excludeClause $semClause
             LIMIT 1",
            $dupParams
        );
        if ($dupConflict) {
            $conflicts[] = 'هذا المقرر مسكّن بالفعل في نفس الفترة';
        }

        return $conflicts;
    }

    /**
     * Atomically check conflicts then insert — prevents race conditions.
     * Returns ['success' => bool, 'timetable_id' => int|null, 'conflicts' => array]
     */
    public static function storeEntry(array $data, ?int $semesterId = null, ?int $academicYearId = null): array
    {
        $db = \Database::getInstance();

        try {
            $db->beginTransaction();

            // Lock the relevant rows to prevent concurrent inserts
            $db->fetch(
                "SELECT 1 FROM timetable WHERE session_id = ? FOR UPDATE",
                [(int)$data['session_id']]
            );

            $conflicts = self::checkConflicts(
                (int)$data['classroom_id'],
                (int)$data['session_id'],
                (int)$data['member_course_id'],
                null,
                $semesterId,
                $academicYearId
            );

            if (!empty($conflicts)) {
                $db->rollback();
                return ['success' => false, 'timetable_id' => null, 'conflicts' => $conflicts];
            }

            // Add semester/year if available
            if ($semesterId)     $data['semester_id'] = $semesterId;
            if ($academicYearId) $data['academic_year_id'] = $academicYearId;

            $id = \App\Models\Timetable::create($data);

            $db->commit();
            return ['success' => true, 'timetable_id' => $id, 'conflicts' => []];

        } catch (\Throwable $e) {
            $db->rollback();
            error_log('SchedulingService::storeEntry error: ' . $e->getMessage());
            return ['success' => false, 'timetable_id' => null, 'conflicts' => ['خطأ داخلي أثناء التسكين']];
        }
    }

    /**
     * Atomically check conflicts then update — prevents race conditions.
     */
    public static function updateEntry(int $timetableId, array $data, ?int $semesterId = null, ?int $academicYearId = null): array
    {
        $db = \Database::getInstance();
        try {
            $db->beginTransaction();
            $db->fetch("SELECT 1 FROM timetable WHERE session_id = ? FOR UPDATE", [(int)$data['session_id']]);

            $conflicts = self::checkConflicts(
                (int)$data['classroom_id'], (int)$data['session_id'],
                (int)$data['member_course_id'], $timetableId, $semesterId, $academicYearId
            );

            if (!empty($conflicts)) {
                $db->rollback();
                return ['success' => false, 'conflicts' => $conflicts];
            }

            \App\Models\Timetable::updateById($timetableId, $data);
            $db->commit();
            return ['success' => true, 'conflicts' => []];
        } catch (\Throwable $e) {
            $db->rollback();
            error_log('SchedulingService::updateEntry error: ' . $e->getMessage());
            return ['success' => false, 'conflicts' => ['خطأ داخلي أثناء التحديث']];
        }
    }

    /**
     * Get current semester_id and academic_year_id from DB.
     */
    public static function getCurrentContext(): array
    {
        $db = \Database::getInstance();
        $result = ['semester_id' => null, 'academic_year_id' => null];
        try {
            $year = $db->fetch("SELECT id FROM academic_years WHERE is_current = 1 LIMIT 1");
            if ($year) $result['academic_year_id'] = (int)$year['id'];
            $sem = $db->fetch("SELECT id FROM semesters WHERE is_current = 1 LIMIT 1");
            if ($sem) $result['semester_id'] = (int)$sem['id'];
        } catch (\Throwable $e) {}
        return $result;
    }

    /**
     * Pass scheduling role to next faculty member.
     */
    public static function passRole(int $currentMemberId): bool
    {
        $db = \Database::getInstance();

        try {
            $db->beginTransaction();

            // Get current user
            $current = $db->fetch(
                "SELECT fm.*, u.id as user_id, u.registration_status
                 FROM faculty_members fm
                 JOIN users u ON u.member_id = fm.member_id
                 WHERE fm.member_id = ?",
                [$currentMemberId]
            );
            if (!$current) {
                $db->rollback();
                return false;
            }

            // Set current user's status to 0
            $db->execute(
                "UPDATE users SET registration_status = 0 WHERE member_id = ?",
                [$currentMemberId]
            );

            // Find next member by ranking
            $next = $db->fetch(
                "SELECT fm.member_id, u.id as user_id
                 FROM faculty_members fm
                 JOIN users u ON u.member_id = fm.member_id
                 WHERE fm.ranking > ? AND fm.is_active = 1
                 ORDER BY fm.ranking ASC LIMIT 1",
                [$current['ranking']]
            );

            // If no next, wrap around to first
            if (!$next) {
                $next = $db->fetch(
                    "SELECT fm.member_id, u.id as user_id
                     FROM faculty_members fm
                     JOIN users u ON u.member_id = fm.member_id
                     WHERE fm.is_active = 1
                     ORDER BY fm.ranking ASC LIMIT 1"
                );
            }

            if ($next) {
                $db->execute(
                    "UPDATE users SET registration_status = 1 WHERE member_id = ?",
                    [$next['member_id']]
                );

                // Send notification
                NotificationService::send(
                    $next['user_id'],
                    'دورك في التسكين',
                    'تم تمرير دور التسكين إليك. يمكنك الآن تسكين محاضراتك في الجدول.',
                    'info',
                    '/scheduling'
                );
            }

            $db->commit();
            return true;
        } catch (\Throwable $e) {
            $db->rollback();
            error_log("SchedulingService passRole error: " . $e->getMessage());
            return false;
        }
    }
}

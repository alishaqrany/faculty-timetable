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
        ?int $excludeTimetableId = null
    ): array {
        $db = \Database::getInstance();
        $conflicts = [];

        // Get member_course details
        $mc = $db->fetch(
            "SELECT mc.*, s.department_id, s.level_id, sec.section_id, mc.member_id
             FROM member_courses mc
             JOIN subjects s ON mc.subject_id = s.subject_id
             JOIN sections sec ON mc.section_id = sec.section_id
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

        // 1. Classroom conflict: same classroom + same session
        $params = array_merge([$classroomId, $sessionId], $baseParams);
        $classroomConflict = $db->fetch(
            "SELECT t.*, c.classroom_name, sess.session_name, sess.day
             FROM timetable t
             JOIN classrooms c ON t.classroom_id = c.classroom_id
             JOIN sessions sess ON t.session_id = sess.session_id
             WHERE t.classroom_id = ? AND t.session_id = ? $excludeClause
             LIMIT 1",
            $params
        );
        if ($classroomConflict) {
            $conflicts[] = "القاعة \"{$classroomConflict['classroom_name']}\" محجوزة في {$classroomConflict['day']} - {$classroomConflict['session_name']}";
        }

        // 2. Section/Level conflict: same department+level+session (different section can't be at the same time for same level)
        $params = array_merge([$sessionId, $mc['department_id'], $mc['level_id']], $baseParams);
        $sectionConflict = $db->fetch(
            "SELECT t.*, sec.section_name, sess.session_name, sess.day
             FROM timetable t
             JOIN member_courses mc2 ON t.member_course_id = mc2.member_course_id
             JOIN subjects sub ON mc2.subject_id = sub.subject_id
             JOIN sections sec ON mc2.section_id = sec.section_id
             JOIN sessions sess ON t.session_id = sess.session_id
             WHERE t.session_id = ? AND sub.department_id = ? AND sub.level_id = ?
             AND mc2.section_id = ? $excludeClause
             LIMIT 1",
            array_merge($params, [$mc['section_id']])
        );
        if ($sectionConflict) {
            $conflicts[] = "الشعبة \"{$sectionConflict['section_name']}\" لديها حصة في نفس الفترة ({$sectionConflict['day']} - {$sectionConflict['session_name']})";
        }

        // 3. Faculty conflict: same member at same session
        $params = array_merge([$sessionId, $mc['member_id']], $baseParams);
        $memberConflict = $db->fetch(
            "SELECT t.*, fm.member_name, sess.session_name, sess.day
             FROM timetable t
             JOIN member_courses mc2 ON t.member_course_id = mc2.member_course_id
             JOIN faculty_members fm ON mc2.member_id = fm.member_id
             JOIN sessions sess ON t.session_id = sess.session_id
             WHERE t.session_id = ? AND mc2.member_id = ? $excludeClause
             LIMIT 1",
            $params
        );
        if ($memberConflict) {
            $conflicts[] = "عضو هيئة التدريس \"{$memberConflict['member_name']}\" لديه حصة في نفس الفترة ({$memberConflict['day']} - {$memberConflict['session_name']})";
        }

        return $conflicts;
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
                    'دورك في الجدولة',
                    'تم تمرير دور الجدولة إليك. يمكنك الآن إضافة حصصك في الجدول.',
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

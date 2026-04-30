<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/Session.php';
require_once APP_ROOT . '/app/Services/AuditService.php';

use App\Models\Session;
use App\Services\AuditService;

class SessionController extends \Controller
{
    public function index(): void
    {
        $this->authorize('sessions.view');
        $sessions = Session::all("FIELD(day,'الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس'), start_time ASC");
        $this->render('sessions.index', ['sessions' => $sessions]);
    }

    public function create(): void
    {
        $this->authorize('sessions.create');
        $this->render('sessions.create');
    }

    public function store(): void
    {
        $this->authorize('sessions.create');
        $this->validateCsrf();

        $data = $this->request->validate([
            'day'          => 'required|in:الأحد,الاثنين,الثلاثاء,الأربعاء,الخميس',
            'session_name' => 'required|max:100',
            'start_time'   => 'required',
            'end_time'     => 'required',
            'duration'     => 'integer',
        ]);
        if ($data === false) $this->redirect('/sessions/create', 'يرجى تصحيح الأخطاء', 'error');

        $data['is_active'] = 1;
        $id = Session::create($data);
        AuditService::log('CREATE', 'sessions', $id, null, $data);

        $this->redirect('/sessions', 'تم إنشاء الفترة الزمنية بنجاح ✓');
    }

    public function edit(string $id): void
    {
        $this->authorize('sessions.edit');
        $session = Session::find((int)$id);
        if (!$session) $this->redirect('/sessions', 'الفترة غير موجودة', 'error');
        $this->render('sessions.edit', ['session_item' => $session]);
    }

    public function update(string $id): void
    {
        $this->authorize('sessions.edit');
        $this->validateCsrf();

        $session = Session::find((int)$id);
        if (!$session) $this->redirect('/sessions', 'الفترة غير موجودة', 'error');

        $data = $this->request->validate([
            'day'          => 'required|in:الأحد,الاثنين,الثلاثاء,الأربعاء,الخميس',
            'session_name' => 'required|max:100',
            'start_time'   => 'required',
            'end_time'     => 'required',
            'duration'     => 'integer',
        ]);
        if ($data === false) $this->redirect("/sessions/{$id}/edit", 'يرجى تصحيح الأخطاء', 'error');

        $data['is_active'] = $this->request->input('is_active', 1);
        Session::updateById((int)$id, $data);
        AuditService::log('UPDATE', 'sessions', (int)$id, $session, $data);

        $this->redirect('/sessions', 'تم تحديث الفترة الزمنية بنجاح ✓');
    }

    public function destroy(string $id): void
    {
        $this->authorize('sessions.delete');
        $this->validateCsrf();

        $session = Session::find((int)$id);
        if (!$session) $this->redirect('/sessions', 'الفترة غير موجودة', 'error');

        Session::destroy((int)$id);
        AuditService::log('DELETE', 'sessions', (int)$id, $session);

        $this->redirect('/sessions', 'تم حذف الفترة الزمنية بنجاح ✓');
    }

    /**
     * Generate default sessions based on user-selected count and duration.
     */
    public function generate(): void
    {
        $this->authorize('sessions.create');
        $this->validateCsrf();

        $sessionsPerDay = (int)$this->request->input('sessions_per_day', 5);
        $sessionsPerDay = max(1, min(8, $sessionsPerDay));

        $durationHours = (int)$this->request->input('duration_hours', 2);
        if (!in_array($durationHours, [1, 2, 3], true)) {
            $durationHours = 2;
        }

        $days = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];
        $names = [
            'الفترة الأولى', 'الفترة الثانية', 'الفترة الثالثة', 'الفترة الرابعة',
            'الفترة الخامسة', 'الفترة السادسة', 'الفترة السابعة', 'الفترة الثامنة',
        ];

        $slots = [];
        $startMin = 8 * 60; // 08:00
        for ($i = 0; $i < $sessionsPerDay; $i++) {
            $endMin = $startMin + ($durationHours * 60);
            $slots[] = [
                'name'  => $names[$i],
                'start' => sprintf('%02d:%02d:00', intdiv($startMin, 60), $startMin % 60),
                'end'   => sprintf('%02d:%02d:00', intdiv($endMin, 60),   $endMin   % 60),
            ];
            $startMin = $endMin; // consecutive sessions, no break
        }

        // Remove existing sessions (and dependent timetable rows) before regenerating
        $db = \Database::getInstance();
        $db->raw("DELETE FROM `timetable` WHERE `session_id` IN (SELECT `session_id` FROM `sessions`)");
        $db->raw("DELETE FROM `sessions`");

        $count = 0;
        foreach ($days as $day) {
            foreach ($slots as $slot) {
                Session::create([
                    'day'          => $day,
                    'session_name' => $slot['name'],
                    'start_time'   => $slot['start'],
                    'end_time'     => $slot['end'],
                    'duration'     => $durationHours,
                    'is_active'    => 1,
                ]);
                $count++;
            }
        }

        AuditService::log('GENERATE', 'sessions', null, null, [
            'count'            => $count,
            'sessions_per_day' => $sessionsPerDay,
            'duration_hours'   => $durationHours,
        ]);
        $this->redirect('/sessions', "تم إنشاء $count فترة زمنية بنجاح ✓");
    }

    /**
     * Delete all sessions (and dependent timetable rows to respect FK constraints).
     */
    public function destroyAll(): void
    {
        $this->authorize('sessions.delete');
        $this->validateCsrf();

        $db = \Database::getInstance();
        $count = (int)($db->fetch("SELECT COUNT(*) AS cnt FROM `sessions`")['cnt'] ?? 0);

        // Must remove dependent timetable rows first (FK: timetable.session_id → sessions.session_id)
        $db->raw("DELETE FROM `timetable` WHERE `session_id` IN (SELECT `session_id` FROM `sessions`)");
        $db->raw("DELETE FROM `sessions`");

        AuditService::log('DELETE_ALL', 'sessions', null, null, ['deleted_count' => $count]);
        $this->redirect('/sessions', "تم حذف $count فترة زمنية بنجاح ✓");
    }
}

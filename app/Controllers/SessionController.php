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
     * Generate default sessions (5 days × 5 time slots).
     */
    public function generate(): void
    {
        $this->authorize('sessions.create');
        $this->validateCsrf();

        $days = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];
        $slots = [
            ['name' => 'الفترة الأولى',  'start' => '08:00:00', 'end' => '10:00:00'],
            ['name' => 'الفترة الثانية', 'start' => '10:00:00', 'end' => '12:00:00'],
            ['name' => 'الفترة الثالثة', 'start' => '12:00:00', 'end' => '14:00:00'],
            ['name' => 'الفترة الرابعة', 'start' => '14:00:00', 'end' => '16:00:00'],
            ['name' => 'الفترة الخامسة', 'start' => '16:00:00', 'end' => '18:00:00'],
        ];

        $count = 0;
        foreach ($days as $day) {
            foreach ($slots as $slot) {
                $exists = Session::findWhere(['day' => $day, 'start_time' => $slot['start']]);
                if (!$exists) {
                    Session::create([
                        'day'          => $day,
                        'session_name' => $slot['name'],
                        'start_time'   => $slot['start'],
                        'end_time'     => $slot['end'],
                        'duration'     => 2,
                        'is_active'    => 1,
                    ]);
                    $count++;
                }
            }
        }

        AuditService::log('GENERATE', 'sessions', null, null, ['count' => $count]);
        $this->redirect('/sessions', "تم إنشاء $count فترة زمنية بنجاح ✓");
    }
}

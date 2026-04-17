<?php
namespace App\Controllers\Api;

require_once APP_ROOT . '/app/Models/Timetable.php';
require_once APP_ROOT . '/app/Models/Department.php';

use App\Models\{Timetable, Department};

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
}

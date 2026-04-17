<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/Timetable.php';
require_once APP_ROOT . '/app/Models/Department.php';
require_once APP_ROOT . '/app/Models/Level.php';
require_once APP_ROOT . '/app/Models/Member.php';
require_once APP_ROOT . '/app/Models/Classroom.php';
require_once APP_ROOT . '/app/Services/ExportService.php';

use App\Models\{Timetable, Department, Level, Member, Classroom};
use App\Services\ExportService;

class TimetableController extends \Controller
{
    public function index(): void
    {
        $this->authorize('timetable.view');

        $departments = Department::active();
        $levels = Level::active();
        $members = Member::active();
        $classrooms = Classroom::active();

        $filters = [
            'department_id' => $this->request->input('department_id'),
            'level_id'      => $this->request->input('level_id'),
            'member_id'     => $this->request->input('member_id'),
            'classroom_id'  => $this->request->input('classroom_id'),
        ];

        $entries = [];
        $pivotData = null;
        $hasFilter = !empty(array_filter($filters));

        if ($hasFilter) {
            $entries = Timetable::allWithDetails($filters);

            if (!empty($filters['department_id']) && !empty($filters['level_id'])) {
                $pivotData = Timetable::pivotTable(
                    (int)$filters['department_id'],
                    (int)$filters['level_id']
                );
            }
        }

        $this->render('timetable.index', [
            'departments' => $departments,
            'levels'      => $levels,
            'members'     => $members,
            'classrooms'  => $classrooms,
            'filters'     => $filters,
            'entries'     => $entries,
            'pivotData'   => $pivotData,
            'hasFilter'   => $hasFilter,
        ]);
    }

    public function export(): void
    {
        $this->authorize('timetable.export');

        $format = $this->request->input('format', 'csv');
        $filters = [
            'department_id' => $this->request->input('department_id'),
            'level_id'      => $this->request->input('level_id'),
            'member_id'     => $this->request->input('member_id'),
            'classroom_id'  => $this->request->input('classroom_id'),
        ];

        $entries = Timetable::allWithDetails($filters);

        if ($format === 'html') {
            $deptName = '';
            $levelName = '';
            if (!empty($filters['department_id'])) {
                $dept = Department::find((int)$filters['department_id']);
                $deptName = $dept['department_name'] ?? '';
            }
            if (!empty($filters['level_id'])) {
                $level = Level::find((int)$filters['level_id']);
                $levelName = $level['level_name'] ?? '';
            }
            $pivotData = Timetable::pivotTable(
                (int)$filters['department_id'],
                (int)$filters['level_id']
            );
            ExportService::timetableHtml($pivotData, $deptName, $levelName);
        } else {
            ExportService::timetableCsv($entries);
        }
    }
}

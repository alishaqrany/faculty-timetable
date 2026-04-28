<?php
namespace App\Controllers\Api\V1;

require_once APP_ROOT . '/app/Models/Department.php';
require_once APP_ROOT . '/app/Models/Level.php';
require_once APP_ROOT . '/app/Models/Section.php';
require_once APP_ROOT . '/app/Models/Subject.php';
require_once APP_ROOT . '/app/Models/Session.php';
require_once APP_ROOT . '/app/Models/Classroom.php';

use App\Models\{Department, Level, Section, Subject, Session, Classroom};

class LookupController extends BaseApiController
{
    public function departments(): void
    {
        $rows = Department::active();
        $this->ok($rows, 'Departments loaded', ['count' => count($rows)]);
    }

    public function levels(): void
    {
        $rows = Level::active();
        $this->ok($rows, 'Levels loaded', ['count' => count($rows)]);
    }

    public function sections(): void
    {
        $rows = Section::active();
        $this->ok($rows, 'Sections loaded', ['count' => count($rows)]);
    }

    public function subjects(): void
    {
        $rows = Subject::allWithDetails();
        $this->ok($rows, 'Subjects loaded', ['count' => count($rows)]);
    }

    public function sessions(): void
    {
        $rows = Session::active();
        $this->ok($rows, 'Sessions loaded', ['count' => count($rows)]);
    }

    public function classrooms(): void
    {
        $rows = Classroom::active();
        $this->ok($rows, 'Classrooms loaded', ['count' => count($rows)]);
    }
}

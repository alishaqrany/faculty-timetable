<?php
namespace App\Controllers\Api\V1;

require_once APP_ROOT . '/app/Models/Department.php';
require_once APP_ROOT . '/app/Models/Subject.php';
require_once APP_ROOT . '/app/Models/Section.php';
require_once APP_ROOT . '/app/Models/Session.php';

use App\Models\{Department, Subject, Section, Session};

class AdminController extends BaseApiController
{
    public function departments(): void
    {
        $this->listResource('departments');
    }

    public function storeDepartment(): void
    {
        $name = trim((string)$this->request->input('department_name', ''));
        if ($name === '') {
            $this->fail('department_name مطلوب', 422);
        }
        $id = Department::create([
            'department_name' => $name,
            'department_code' => trim((string)$this->request->input('department_code', '')),
            'description' => trim((string)$this->request->input('description', '')),
            'is_active' => (int)$this->request->input('is_active', 1),
        ]);
        $this->created(['id' => $id], 'Department created');
    }

    public function updateDepartment(string $id): void
    {
        Department::updateById((int)$id, [
            'department_name' => trim((string)$this->request->input('department_name', '')),
            'department_code' => trim((string)$this->request->input('department_code', '')),
            'description' => trim((string)$this->request->input('description', '')),
            'is_active' => (int)$this->request->input('is_active', 1),
        ]);
        $this->ok(['id' => (int)$id], 'Department updated');
    }

    public function deleteDepartment(string $id): void
    {
        Department::destroy((int)$id);
        $this->ok(['id' => (int)$id], 'Department deleted');
    }

    public function subjects(): void
    {
        $this->listResource('subjects');
    }

    public function storeSubject(): void
    {
        $name = trim((string)$this->request->input('subject_name', ''));
        if ($name === '') {
            $this->fail('subject_name مطلوب', 422);
        }
        $id = Subject::create([
            'subject_name' => $name,
            'subject_code' => trim((string)$this->request->input('subject_code', '')),
            'department_id' => (int)$this->request->input('department_id', 0),
            'level_id' => (int)$this->request->input('level_id', 0),
            'hours' => (int)$this->request->input('hours', 0),
            'credit_hours' => (int)$this->request->input('credit_hours', 0),
            'subject_type' => trim((string)$this->request->input('subject_type', 'نظري')),
            'is_active' => (int)$this->request->input('is_active', 1),
        ]);
        $this->created(['id' => $id], 'Subject created');
    }

    public function updateSubject(string $id): void
    {
        Subject::updateById((int)$id, [
            'subject_name' => trim((string)$this->request->input('subject_name', '')),
            'subject_code' => trim((string)$this->request->input('subject_code', '')),
            'department_id' => (int)$this->request->input('department_id', 0),
            'level_id' => (int)$this->request->input('level_id', 0),
            'hours' => (int)$this->request->input('hours', 0),
            'credit_hours' => (int)$this->request->input('credit_hours', 0),
            'subject_type' => trim((string)$this->request->input('subject_type', 'نظري')),
            'is_active' => (int)$this->request->input('is_active', 1),
        ]);
        $this->ok(['id' => (int)$id], 'Subject updated');
    }

    public function deleteSubject(string $id): void
    {
        Subject::destroy((int)$id);
        $this->ok(['id' => (int)$id], 'Subject deleted');
    }

    public function sections(): void
    {
        $this->listResource('sections');
    }

    public function storeSection(): void
    {
        $id = Section::create([
            'section_name' => trim((string)$this->request->input('section_name', '')),
            'division_id' => (int)$this->request->input('division_id', 0),
            'department_id' => (int)$this->request->input('department_id', 0),
            'level_id' => (int)$this->request->input('level_id', 0),
            'capacity' => (int)$this->request->input('capacity', 0),
            'is_active' => (int)$this->request->input('is_active', 1),
        ]);
        $this->created(['id' => $id], 'Section created');
    }

    public function updateSection(string $id): void
    {
        Section::updateById((int)$id, [
            'section_name' => trim((string)$this->request->input('section_name', '')),
            'division_id' => (int)$this->request->input('division_id', 0),
            'department_id' => (int)$this->request->input('department_id', 0),
            'level_id' => (int)$this->request->input('level_id', 0),
            'capacity' => (int)$this->request->input('capacity', 0),
            'is_active' => (int)$this->request->input('is_active', 1),
        ]);
        $this->ok(['id' => (int)$id], 'Section updated');
    }

    public function deleteSection(string $id): void
    {
        Section::destroy((int)$id);
        $this->ok(['id' => (int)$id], 'Section deleted');
    }

    public function sessions(): void
    {
        $this->listResource('sessions');
    }

    public function storeSession(): void
    {
        $id = Session::create([
            'day' => trim((string)$this->request->input('day', '')),
            'session_name' => trim((string)$this->request->input('session_name', '')),
            'start_time' => trim((string)$this->request->input('start_time', '')),
            'end_time' => trim((string)$this->request->input('end_time', '')),
            'duration' => (int)$this->request->input('duration', 0),
            'is_active' => (int)$this->request->input('is_active', 1),
        ]);
        $this->created(['id' => $id], 'Session created');
    }

    public function updateSession(string $id): void
    {
        Session::updateById((int)$id, [
            'day' => trim((string)$this->request->input('day', '')),
            'session_name' => trim((string)$this->request->input('session_name', '')),
            'start_time' => trim((string)$this->request->input('start_time', '')),
            'end_time' => trim((string)$this->request->input('end_time', '')),
            'duration' => (int)$this->request->input('duration', 0),
            'is_active' => (int)$this->request->input('is_active', 1),
        ]);
        $this->ok(['id' => (int)$id], 'Session updated');
    }

    public function deleteSession(string $id): void
    {
        Session::destroy((int)$id);
        $this->ok(['id' => (int)$id], 'Session deleted');
    }

    private function listResource(string $type): void
    {
        $page = max(1, (int)$this->request->input('page', 1));
        $perPage = min(100, max(1, (int)$this->request->input('per_page', 20)));
        $q = trim((string)$this->request->input('q', ''));

        $all = match ($type) {
            'departments' => Department::all('department_name ASC'),
            'subjects' => Subject::allWithDetails('s.subject_name ASC'),
            'sections' => Section::allWithDetails(),
            default => Session::active(),
        };

        if ($q !== '') {
            $all = array_values(array_filter($all, function ($row) use ($q) {
                $hay = mb_strtolower(json_encode($row, JSON_UNESCAPED_UNICODE) ?: '');
                return str_contains($hay, mb_strtolower($q));
            }));
        }

        $total = count($all);
        $offset = ($page - 1) * $perPage;
        $slice = array_slice($all, $offset, $perPage);
        $this->ok($slice, 'OK', $this->paginationMeta($total, $page, $perPage));
    }
}

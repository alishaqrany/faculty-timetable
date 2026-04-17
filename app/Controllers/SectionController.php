<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/Section.php';
require_once APP_ROOT . '/app/Models/Division.php';
require_once APP_ROOT . '/app/Models/Department.php';
require_once APP_ROOT . '/app/Models/Level.php';
require_once APP_ROOT . '/app/Services/AuditService.php';

use App\Models\{Section, Division, Department, Level};
use App\Services\AuditService;

class SectionController extends \Controller
{
    public function index(): void
    {
        $this->authorize('sections.view');
        // Group sections by division for better display
        $sections = Section::allGroupedByDivision();
        $divisions = Division::active();
        $departments = Department::active();
        $levels = Level::active();
        $this->render('sections.index', [
            'groupedSections' => $sections,
            'divisions' => $divisions,
            'departments' => $departments,
            'levels' => $levels,
        ]);
    }

    public function create(): void
    {
        $this->authorize('sections.create');
        $divisions = Division::active();
        $this->render('sections.create', [
            'divisions' => $divisions
        ]);
    }

    public function store(): void
    {
        $this->authorize('sections.create');
        $this->validateCsrf();

        $data = $this->request->validate([
            'section_name'  => 'required|max:100',
            'division_id'   => 'required|integer',
            'capacity'      => 'integer',
        ]);
        if ($data === false) $this->redirect('/sections/create', 'يرجى تصحيح الأخطاء', 'error');

        // Get department_id and level_id from division (for backwards compatibility)
        $division = Division::find((int)$data['division_id']);
        if ($division) {
            $data['department_id'] = $division['department_id'];
            $data['level_id'] = $division['level_id'];
        }

        $data['is_active'] = 1;
        $id = Section::create($data);
        AuditService::log('CREATE', 'sections', $id, null, $data);

        $this->redirect('/sections', 'تم إنشاء السكشن بنجاح ✓');
    }

    public function edit(string $id): void
    {
        $this->authorize('sections.edit');
        $section = Section::findWithDetails((int)$id);
        if (!$section) $this->redirect('/sections', 'السكشن غير موجود', 'error');

        $divisions = Division::active();
        
        $this->render('sections.edit', [
            'section' => $section, 
            'divisions' => $divisions
        ]);
    }

    public function update(string $id): void
    {
        $this->authorize('sections.edit');
        $this->validateCsrf();

        $section = Section::find((int)$id);
        if (!$section) $this->redirect('/sections', 'السكشن غير موجود', 'error');

        $data = $this->request->validate([
            'section_name'  => 'required|max:100',
            'division_id'   => 'required|integer',
            'capacity'      => 'integer',
        ]);
        if ($data === false) $this->redirect("/sections/{$id}/edit", 'يرجى تصحيح الأخطاء', 'error');

        // Get department_id and level_id from division (for backwards compatibility)
        $division = Division::find((int)$data['division_id']);
        if ($division) {
            $data['department_id'] = $division['department_id'];
            $data['level_id'] = $division['level_id'];
        }

        $data['is_active'] = $this->request->input('is_active', 1);
        Section::updateById((int)$id, $data);
        AuditService::log('UPDATE', 'sections', (int)$id, $section, $data);

        $this->redirect('/sections', 'تم تحديث السكشن بنجاح ✓');
    }

    public function destroy(string $id): void
    {
        $this->authorize('sections.delete');
        $this->validateCsrf();

        $section = Section::find((int)$id);
        if (!$section) $this->redirect('/sections', 'السكشن غير موجود', 'error');

        Section::destroy((int)$id);
        AuditService::log('DELETE', 'sections', (int)$id, $section);

        $this->redirect('/sections', 'تم حذف السكشن بنجاح ✓');
    }

    public function generateAuto(): void
    {
        $this->authorize('sections.create');
        $this->validateCsrf();

        $targetType = (string)$this->request->input('target_type', 'division');
        $sectionsCount = (int)$this->request->input('sections_count', 2);
        $capacity = (int)$this->request->input('capacity', 25);
        $startNumber = (int)$this->request->input('start_number', 1);

        if (!in_array($targetType, ['division', 'level'], true)) {
            $this->redirect('/sections', 'نوع التوليد غير صالح.', 'error');
        }
        if ($sectionsCount < 1 || $sectionsCount > 30) {
            $this->redirect('/sections', 'عدد السكاشن يجب أن يكون بين 1 و30.', 'error');
        }
        if ($capacity < 1) {
            $this->redirect('/sections', 'سعة السكشن يجب أن تكون أكبر من صفر.', 'error');
        }
        if ($startNumber < 1) {
            $this->redirect('/sections', 'رقم بداية الترقيم يجب أن يكون 1 أو أكثر.', 'error');
        }

        $divisionIds = [];
        $createdGeneralDivision = false;

        if ($targetType === 'division') {
            $divisionId = (int)$this->request->input('division_id');
            if ($divisionId <= 0) {
                $this->redirect('/sections', 'يرجى اختيار الشعبة.', 'error');
            }

            $division = Division::find($divisionId);
            if (!$division) {
                $this->redirect('/sections', 'الشعبة المختارة غير موجودة.', 'error');
            }

            $divisionIds = [$divisionId];
        } else {
            $departmentId = (int)$this->request->input('department_id');
            $levelId = (int)$this->request->input('level_id');

            if ($departmentId <= 0 || $levelId <= 0) {
                $this->redirect('/sections', 'يرجى اختيار القسم والفرقة.', 'error');
            }

            $levelDivisions = Division::where([
                'department_id' => $departmentId,
                'level_id' => $levelId,
            ], 'division_name ASC');

            if (empty($levelDivisions)) {
                $generalDivision = Division::findWhere([
                    'division_name' => 'عامة',
                    'department_id' => $departmentId,
                    'level_id' => $levelId,
                ]);

                if (!$generalDivision) {
                    $generalData = [
                        'division_name' => 'عامة',
                        'department_id' => $departmentId,
                        'level_id' => $levelId,
                        'capacity' => 0,
                        'is_active' => 1,
                    ];
                    $generalDivisionId = Division::create($generalData);
                    AuditService::log('CREATE', 'divisions', $generalDivisionId, null, $generalData);
                    $createdGeneralDivision = true;
                } else {
                    $generalDivisionId = (int)$generalDivision['division_id'];
                }

                $levelDivisions = [Division::find((int)$generalDivisionId)];
            }

            foreach ($levelDivisions as $div) {
                if (!empty($div['division_id'])) {
                    $divisionIds[] = (int)$div['division_id'];
                }
            }
        }

        if (empty($divisionIds)) {
            $this->redirect('/sections', 'لا توجد شُعب متاحة لإنشاء السكاشن.', 'error');
        }

        $inserted = 0;
        $stats = [];

        foreach ($divisionIds as $divisionId) {
            $division = Division::find($divisionId);
            if (!$division) {
                continue;
            }

            $divisionName = (string)($division['division_name'] ?? ('#' . $divisionId));
            if (!isset($stats[$divisionName])) {
                $stats[$divisionName] = 0;
            }

            $nextNumber = max($startNumber, $this->nextSectionNumberForDivision($divisionId));

            for ($i = 0; $i < $sectionsCount; $i++) {
                while ($this->sectionNameExistsForDivision($divisionId, 'سكشن ' . $nextNumber)) {
                    $nextNumber++;
                }

                $row = [
                    'section_name' => 'سكشن ' . $nextNumber,
                    'division_id' => $divisionId,
                    'department_id' => (int)$division['department_id'],
                    'level_id' => (int)$division['level_id'],
                    'capacity' => $capacity,
                    'is_active' => 1,
                ];

                Section::create($row);
                $inserted++;
                $stats[$divisionName]++;
                $nextNumber++;
            }
        }

        AuditService::log('CREATE', 'sections_auto_generate', null, null, [
            'target_type' => $targetType,
            'sections_count' => $sectionsCount,
            'capacity' => $capacity,
            'start_number' => $startNumber,
            'division_ids' => $divisionIds,
            'inserted' => $inserted,
            'stats' => $stats,
        ]);

        if ($inserted === 0) {
            $this->redirect('/sections', 'لم يتم إنشاء سكاشن جديدة.', 'error');
        }

        $message = "تم إنشاء {$inserted} سكشن تلقائياً بنجاح ✓";
        if ($createdGeneralDivision) {
            $message .= ' (تم إنشاء شعبة عامة تلقائياً للفرقة المختارة)';
        }

        $this->redirect('/sections', $message);
    }

    /**
     * API: Get sections by division (for AJAX)
     */
    public function byDivision(): void
    {
        $divisionId = (int)$this->request->input('division_id');
        $sections = Section::byDivision($divisionId);
        
        header('Content-Type: application/json');
        echo json_encode($sections);
        exit;
    }

    private function sectionNameExistsForDivision(int $divisionId, string $sectionName): bool
    {
        $count = (int)
            \Database::getInstance()->fetchColumn(
                "SELECT COUNT(*) FROM sections WHERE division_id = ? AND section_name = ?",
                [$divisionId, $sectionName]
            );
        return $count > 0;
    }

    private function nextSectionNumberForDivision(int $divisionId): int
    {
        $rows = \Database::getInstance()->fetchAll(
            "SELECT section_name FROM sections WHERE division_id = ?",
            [$divisionId]
        );

        $max = 0;
        foreach ($rows as $row) {
            $name = (string)($row['section_name'] ?? '');
            if (preg_match('/^سكشن\s+(\d+)$/u', $name, $m)) {
                $num = (int)$m[1];
                if ($num > $max) {
                    $max = $num;
                }
            }
        }

        return $max + 1;
    }
}

<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/Section.php';
require_once APP_ROOT . '/app/Models/Department.php';
require_once APP_ROOT . '/app/Models/Level.php';
require_once APP_ROOT . '/app/Services/AuditService.php';

use App\Models\{Section, Department, Level};
use App\Services\AuditService;

class SectionController extends \Controller
{
    public function index(): void
    {
        $this->authorize('sections.view');
        $sections = Section::allWithDetails();
        $this->render('sections.index', ['sections' => $sections]);
    }

    public function create(): void
    {
        $this->authorize('sections.create');
        $departments = Department::active();
        $levels = Level::active();
        $parentSections = Section::parentOptions();
        $this->render('sections.create', [
            'departments'    => $departments,
            'levels'         => $levels,
            'parentSections' => $parentSections,
        ]);
    }

    public function store(): void
    {
        $this->authorize('sections.create');
        $this->validateCsrf();

        $data = $this->request->validate([
            'section_name'  => 'required|max:100',
            'section_type'  => 'required|in:شعبة,سكشن',
            'department_id' => 'required|integer',
            'level_id'      => 'required|integer',
            'parent_section_id' => 'integer',
            'capacity'      => 'integer',
        ]);
        if ($data === false) $this->redirect('/sections/create', 'يرجى تصحيح الأخطاء', 'error');

        if ($data['section_type'] === 'سكشن') {
            if (empty($data['parent_section_id'])) {
                $this->redirect('/sections/create', 'يجب اختيار الشعبة الأب عند إنشاء سكشن', 'error');
            }

            $parent = Section::find((int)$data['parent_section_id']);
            if (!$parent || ($parent['section_type'] ?? '') !== 'شعبة') {
                $this->redirect('/sections/create', 'الشعبة الأب غير صالحة', 'error');
            }

            $data['department_id'] = (int)$parent['department_id'];
            $data['level_id'] = (int)$parent['level_id'];
        } else {
            $data['parent_section_id'] = null;
        }

        $data['is_active'] = 1;
        $id = Section::create($data);
        AuditService::log('CREATE', 'sections', $id, null, $data);

        $this->redirect('/sections', 'تم إنشاء الشعبة بنجاح ✓');
    }

    public function edit(string $id): void
    {
        $this->authorize('sections.edit');
        $section = Section::findWithDetails((int)$id);
        if (!$section) $this->redirect('/sections', 'الشعبة غير موجودة', 'error');

        $departments = Department::active();
        $levels = Level::active();
        $parentSections = Section::parentOptions();
        $this->render('sections.edit', [
            'section'        => $section,
            'departments'    => $departments,
            'levels'         => $levels,
            'parentSections' => $parentSections,
        ]);
    }

    public function update(string $id): void
    {
        $this->authorize('sections.edit');
        $this->validateCsrf();

        $section = Section::find((int)$id);
        if (!$section) $this->redirect('/sections', 'الشعبة غير موجودة', 'error');

        $data = $this->request->validate([
            'section_name'  => 'required|max:100',
            'section_type'  => 'required|in:شعبة,سكشن',
            'department_id' => 'required|integer',
            'level_id'      => 'required|integer',
            'parent_section_id' => 'integer',
            'capacity'      => 'integer',
        ]);
        if ($data === false) $this->redirect("/sections/{$id}/edit", 'يرجى تصحيح الأخطاء', 'error');

        if ($data['section_type'] === 'سكشن') {
            if (empty($data['parent_section_id'])) {
                $this->redirect("/sections/{$id}/edit", 'يجب اختيار الشعبة الأب عند إنشاء سكشن', 'error');
            }

            if ((int)$data['parent_section_id'] === (int)$id) {
                $this->redirect("/sections/{$id}/edit", 'لا يمكن ربط السكشن بنفسه كشعبة أب', 'error');
            }

            $parent = Section::find((int)$data['parent_section_id']);
            if (!$parent || ($parent['section_type'] ?? '') !== 'شعبة') {
                $this->redirect("/sections/{$id}/edit", 'الشعبة الأب غير صالحة', 'error');
            }

            $data['department_id'] = (int)$parent['department_id'];
            $data['level_id'] = (int)$parent['level_id'];
        } else {
            $data['parent_section_id'] = null;
        }

        $data['is_active'] = $this->request->input('is_active', 1);
        Section::updateById((int)$id, $data);
        AuditService::log('UPDATE', 'sections', (int)$id, $section, $data);

        $this->redirect('/sections', 'تم تحديث الشعبة بنجاح ✓');
    }

    public function destroy(string $id): void
    {
        $this->authorize('sections.delete');
        $this->validateCsrf();

        $section = Section::find((int)$id);
        if (!$section) $this->redirect('/sections', 'الشعبة غير موجودة', 'error');

        Section::destroy((int)$id);
        AuditService::log('DELETE', 'sections', (int)$id, $section);

        $this->redirect('/sections', 'تم حذف الشعبة بنجاح ✓');
    }
}

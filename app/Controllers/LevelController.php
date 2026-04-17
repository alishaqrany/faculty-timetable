<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/Level.php';
require_once APP_ROOT . '/app/Services/AuditService.php';

use App\Models\Level;
use App\Services\AuditService;

class LevelController extends \Controller
{
    private function levelNameExists(string $levelName, ?int $exceptId = null): bool
    {
        $db = \Database::getInstance();
        if ($exceptId === null) {
            $count = (int)$db->fetchColumn("SELECT COUNT(*) FROM levels WHERE level_name = ?", [$levelName]);
            return $count > 0;
        }

        $count = (int)$db->fetchColumn(
            "SELECT COUNT(*) FROM levels WHERE level_name = ? AND level_id <> ?",
            [$levelName, $exceptId]
        );
        return $count > 0;
    }

    public function index(): void
    {
        $this->authorize('levels.view');
        $levels = Level::all('sort_order ASC, level_name ASC');
        $this->render('levels.index', ['levels' => $levels]);
    }

    public function create(): void
    {
        $this->authorize('levels.create');
        $this->render('levels.create');
    }

    public function store(): void
    {
        $this->authorize('levels.create');
        $this->validateCsrf();

        $data = $this->request->validate([
            'level_name' => 'required|max:200',
            'level_code' => 'max:20',
            'sort_order' => 'integer',
        ]);
        if ($data === false) $this->redirect('/levels/create', 'يرجى تصحيح الأخطاء', 'error');

        if ($this->levelNameExists($data['level_name'])) {
            $this->redirect('/levels/create', 'اسم الفرقة موجود بالفعل ولا يمكن تكراره.', 'error');
        }

        $data['is_active'] = 1;
        $id = Level::create($data);
        AuditService::log('CREATE', 'levels', $id, null, $data);

        $this->redirect('/levels', 'تم إنشاء الفرقة بنجاح ✓');
    }

    public function edit(string $id): void
    {
        $this->authorize('levels.edit');
        $level = Level::find((int)$id);
        if (!$level) $this->redirect('/levels', 'الفرقة غير موجودة', 'error');
        $this->render('levels.edit', ['level' => $level]);
    }

    public function update(string $id): void
    {
        $this->authorize('levels.edit');
        $this->validateCsrf();

        $level = Level::find((int)$id);
        if (!$level) $this->redirect('/levels', 'الفرقة غير موجودة', 'error');

        $data = $this->request->validate([
            'level_name' => 'required|max:200',
            'level_code' => 'max:20',
            'sort_order' => 'integer',
        ]);
        if ($data === false) $this->redirect("/levels/{$id}/edit", 'يرجى تصحيح الأخطاء', 'error');

        if ($this->levelNameExists($data['level_name'], (int)$id)) {
            $this->redirect("/levels/{$id}/edit", 'اسم الفرقة موجود بالفعل ولا يمكن تكراره.', 'error');
        }

        $data['is_active'] = $this->request->input('is_active', 1);
        Level::updateById((int)$id, $data);
        AuditService::log('UPDATE', 'levels', (int)$id, $level, $data);

        $this->redirect('/levels', 'تم تحديث الفرقة بنجاح ✓');
    }

    public function destroy(string $id): void
    {
        $this->authorize('levels.delete');
        $this->validateCsrf();

        $level = Level::find((int)$id);
        if (!$level) $this->redirect('/levels', 'الفرقة غير موجودة', 'error');

        Level::destroy((int)$id);
        AuditService::log('DELETE', 'levels', (int)$id, $level);

        $this->redirect('/levels', 'تم حذف الفرقة بنجاح ✓');
    }

    public function seedDefaults(): void
    {
        $this->authorize('levels.create');
        $this->validateCsrf();

        $defaults = [
            ['level_name' => 'الفرقة الأولى', 'level_code' => 'L1', 'sort_order' => 1],
            ['level_name' => 'الفرقة الثانية', 'level_code' => 'L2', 'sort_order' => 2],
            ['level_name' => 'الفرقة الثالثة', 'level_code' => 'L3', 'sort_order' => 3],
            ['level_name' => 'الفرقة الرابعة', 'level_code' => 'L4', 'sort_order' => 4],
        ];

        $inserted = 0;
        foreach ($defaults as $row) {
            if ($this->levelNameExists($row['level_name'])) {
                continue;
            }

            $row['is_active'] = 1;
            Level::create($row);
            $inserted++;
        }

        AuditService::log('CREATE', 'levels_defaults', null, null, ['inserted' => $inserted]);

        if ($inserted === 0) {
            $this->redirect('/levels', 'كل الفرق الافتراضية موجودة بالفعل.');
        }

        $this->redirect('/levels', "تمت إضافة {$inserted} فرقة افتراضية بنجاح ✓");
    }
}

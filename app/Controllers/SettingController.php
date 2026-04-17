<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/Setting.php';
require_once APP_ROOT . '/app/Services/AuditService.php';

use App\Models\Setting;
use App\Services\AuditService;

class SettingController extends \Controller
{
    public function index(): void
    {
        $this->authorize('settings.view');
        $general = Setting::byGroup('general');
        $scheduling = Setting::byGroup('scheduling');
        $this->render('settings.index', ['general' => $general, 'scheduling' => $scheduling]);
    }

    public function update(): void
    {
        $this->authorize('settings.manage');
        $this->validateCsrf();

        $settings = $this->request->input('settings', []);
        if (is_array($settings)) {
            foreach ($settings as $key => $value) {
                Setting::setValue($key, $value);
            }
        }

        AuditService::log('UPDATE', 'settings');
        $this->redirect('/settings', 'تم حفظ الإعدادات بنجاح ✓');
    }
}

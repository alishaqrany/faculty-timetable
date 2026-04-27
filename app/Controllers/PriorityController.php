<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/PriorityCategory.php';
require_once APP_ROOT . '/app/Models/PriorityGroup.php';
require_once APP_ROOT . '/app/Models/PriorityGroupMember.php';
require_once APP_ROOT . '/app/Models/PriorityException.php';
require_once APP_ROOT . '/app/Models/DepartmentPriorityOrder.php';
require_once APP_ROOT . '/app/Models/Member.php';
require_once APP_ROOT . '/app/Models/Department.php';
require_once APP_ROOT . '/app/Services/PriorityService.php';
require_once APP_ROOT . '/app/Services/AuditService.php';
require_once APP_ROOT . '/app/Services/NotificationService.php';

use App\Models\{PriorityCategory, PriorityGroup, PriorityGroupMember, PriorityException, DepartmentPriorityOrder, Member, Department};
use App\Services\{PriorityService, AuditService, NotificationService};

class PriorityController extends \Controller
{
    /**
     * Main priority management page.
     */
    public function index(): void
    {
        $this->authorize('priority.view');

        $state = PriorityService::getCurrentState();
        $modes = PriorityService::getAvailableModes();
        $categories = PriorityCategory::allWithGroupCount();
        $exceptions = PriorityException::allActive();

        $deptOrder = [];
        if ($state['mode'] === PriorityService::MODE_SEQUENTIAL_DEPT) {
            DepartmentPriorityOrder::syncWithDepartments();
            $deptOrder = DepartmentPriorityOrder::allWithDetails();
        }

        $this->render('priority.index', [
            'state'      => $state,
            'modes'      => $modes,
            'categories' => $categories,
            'exceptions' => $exceptions,
            'deptOrder'  => $deptOrder,
            'allMembers' => $this->getMembersWithRegStatus(),
        ]);
    }

    /**
     * Get all members with their registration status.
     */
    private function getMembersWithRegStatus(): array
    {
        $db = \Database::getInstance();
        return $db->fetchAll(
            "SELECT fm.member_id, fm.member_name, d.department_name, ad.degree_name,
                    u.id as user_id, u.registration_status
             FROM faculty_members fm
             LEFT JOIN departments d ON fm.department_id = d.department_id
             LEFT JOIN academic_degrees ad ON fm.degree_id = ad.id
             LEFT JOIN users u ON u.member_id = fm.member_id
             WHERE fm.is_active = 1
             ORDER BY fm.member_name ASC"
        );
    }

    /**
     * Update priority mode.
     */
    public function updateMode(): void
    {
        $this->authorize('priority.manage');
        $this->validateCsrf();

        $mode = $this->request->input('priority_mode', 'disabled');
        try {
            PriorityService::setMode($mode);
            
            // If switching to sequential_dept, sync departments
            if ($mode === PriorityService::MODE_SEQUENTIAL_DEPT) {
                DepartmentPriorityOrder::syncWithDepartments();
                $firstDept = DepartmentPriorityOrder::currentDepartment();
                if ($firstDept) {
                    \App\Models\Setting::setValue('priority_current_dept_id', (string) $firstDept['department_id']);
                }
            }

            AuditService::log('UPDATE', 'priority_mode', null, null, ['mode' => $mode]);
            $this->redirect('/priority', 'تم تحديث وضع الأولوية بنجاح ✓');
        } catch (\Exception $e) {
            $this->redirect('/priority', 'خطأ: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Set active category.
     */
    public function setActiveCategory(): void
    {
        $this->authorize('priority.manage');
        $this->validateCsrf();

        $categoryId = (int) $this->request->input('category_id', 0);
        if ($categoryId) {
            PriorityService::setActiveCategory($categoryId);
            AuditService::log('UPDATE', 'priority_active_category', $categoryId);
            $this->redirect('/priority', 'تم تحديد التصنيف النشط بنجاح ✓');
        } else {
            $this->redirect('/priority', 'يرجى اختيار تصنيف', 'error');
        }
    }

    // ── Categories ──────────────────────────────────────────────

    public function categories(): void
    {
        $this->authorize('priority.manage');
        $categories = PriorityCategory::allWithGroupCount();
        $this->render('priority.categories', ['categories' => $categories]);
    }

    public function storeCategory(): void
    {
        $this->authorize('priority.manage');
        $this->validateCsrf();

        $data = $this->request->validate([
            'category_name' => 'required',
        ]);
        if ($data === false) $this->redirect('/priority/categories', 'يرجى تصحيح الأخطاء', 'error');

        $data['sort_order'] = (int) $this->request->input('sort_order', 0);
        $data['description'] = $this->request->input('description', '');
        $id = PriorityCategory::create($data);

        AuditService::log('CREATE', 'priority_category', $id, null, $data);
        $this->redirect('/priority/categories', 'تم إنشاء التصنيف بنجاح ✓');
    }

    public function editCategory(string $id): void
    {
        $this->authorize('priority.manage');
        $category = PriorityCategory::find((int)$id);
        if (!$category) $this->redirect('/priority/categories', 'التصنيف غير موجود', 'error');

        $this->render('priority.edit-category', ['category' => $category]);
    }

    public function updateCategory(string $id): void
    {
        $this->authorize('priority.manage');
        $this->validateCsrf();

        $data = $this->request->validate(['category_name' => 'required']);
        if ($data === false) $this->redirect('/priority/categories', 'يرجى تصحيح الأخطاء', 'error');

        $data['sort_order'] = (int) $this->request->input('sort_order', 0);
        $data['description'] = $this->request->input('description', '');
        $data['is_active'] = (int) $this->request->input('is_active', 1);

        PriorityCategory::updateById((int)$id, $data);
        AuditService::log('UPDATE', 'priority_category', (int)$id, null, $data);
        $this->redirect('/priority/categories', 'تم تحديث التصنيف بنجاح ✓');
    }

    public function deleteCategory(string $id): void
    {
        $this->authorize('priority.manage');
        $this->validateCsrf();

        PriorityCategory::destroy((int)$id);
        AuditService::log('DELETE', 'priority_category', (int)$id);
        $this->redirect('/priority/categories', 'تم حذف التصنيف بنجاح ✓');
    }

    // ── Groups ───────────────────────────────────────────────────

    public function groups(string $categoryId): void
    {
        $this->authorize('priority.manage');
        $category = PriorityCategory::find((int)$categoryId);
        if (!$category) $this->redirect('/priority/categories', 'التصنيف غير موجود', 'error');

        $groups = PriorityGroup::forCategory((int)$categoryId);
        $this->render('priority.groups', ['category' => $category, 'groups' => $groups]);
    }

    public function storeGroup(): void
    {
        $this->authorize('priority.manage');
        $this->validateCsrf();

        $data = $this->request->validate([
            'group_name'  => 'required',
            'category_id' => 'required|integer',
        ]);
        if ($data === false) $this->redirect('/priority/categories', 'يرجى تصحيح الأخطاء', 'error');

        $data['sort_order'] = (int) $this->request->input('sort_order', 0);
        $id = PriorityGroup::create($data);

        AuditService::log('CREATE', 'priority_group', $id, null, $data);
        $this->redirect('/priority/categories/' . $data['category_id'] . '/groups', 'تم إنشاء المجموعة بنجاح ✓');
    }

    public function editGroup(string $id): void
    {
        $this->authorize('priority.manage');
        $group = PriorityGroup::find((int)$id);
        if (!$group) $this->redirect('/priority/categories', 'المجموعة غير موجودة', 'error');

        $category = PriorityCategory::find((int)$group['category_id']);
        $this->render('priority.edit-group', ['group' => $group, 'category' => $category]);
    }

    public function updateGroup(string $id): void
    {
        $this->authorize('priority.manage');
        $this->validateCsrf();

        $group = PriorityGroup::find((int)$id);
        if (!$group) $this->redirect('/priority/categories', 'المجموعة غير موجودة', 'error');

        $data = $this->request->validate(['group_name' => 'required']);
        if ($data === false) $this->redirect('/priority/groups/' . $id . '/edit', 'يرجى تصحيح الأخطاء', 'error');

        $data['sort_order'] = (int) $this->request->input('sort_order', 0);
        $data['is_active'] = (int) $this->request->input('is_active', 1);

        PriorityGroup::updateById((int)$id, $data);
        AuditService::log('UPDATE', 'priority_group', (int)$id, null, $data);
        $this->redirect('/priority/categories/' . $group['category_id'] . '/groups', 'تم تحديث المجموعة بنجاح ✓');
    }

    public function deleteGroup(string $id): void
    {
        $this->authorize('priority.manage');
        $this->validateCsrf();

        $group = PriorityGroup::find((int)$id);
        if (!$group) $this->redirect('/priority/categories', 'المجموعة غير موجودة', 'error');

        PriorityGroup::destroy((int)$id);
        AuditService::log('DELETE', 'priority_group', (int)$id);
        $this->redirect('/priority/categories/' . $group['category_id'] . '/groups', 'تم حذف المجموعة بنجاح ✓');
    }

    // ── Group Members ────────────────────────────────────────────

    public function groupMembers(string $groupId): void
    {
        $this->authorize('priority.manage');
        $group = PriorityGroup::find((int)$groupId);
        if (!$group) $this->redirect('/priority/categories', 'المجموعة غير موجودة', 'error');

        $category = PriorityCategory::find((int)$group['category_id']);
        $members = PriorityGroup::withMembers((int)$groupId);
        $allMembers = Member::allWithDetails('fm.member_name ASC');

        $this->render('priority.group-members', [
            'group'      => $group,
            'category'   => $category,
            'members'    => $members,
            'allMembers' => $allMembers,
        ]);
    }

    public function addGroupMember(string $groupId): void
    {
        $this->authorize('priority.manage');
        $this->validateCsrf();

        $memberId = (int) $this->request->input('member_id', 0);
        if (!$memberId) $this->redirect('/priority/groups/' . $groupId . '/members', 'يرجى اختيار عضو', 'error');

        if (PriorityGroup::isMemberInGroup((int)$groupId, $memberId)) {
            $this->redirect('/priority/groups/' . $groupId . '/members', 'العضو موجود بالفعل في هذه المجموعة', 'warning');
        }

        PriorityGroup::addMember((int)$groupId, $memberId);
        AuditService::log('CREATE', 'priority_group_member', null, null, ['group_id' => $groupId, 'member_id' => $memberId]);
        $this->redirect('/priority/groups/' . $groupId . '/members', 'تم إضافة العضو بنجاح ✓');
    }

    public function removeGroupMember(string $pivotId): void
    {
        $this->authorize('priority.manage');
        $this->validateCsrf();

        $pivot = PriorityGroupMember::find((int)$pivotId);
        if (!$pivot) $this->redirect('/priority/categories', 'العضوية غير موجودة', 'error');

        PriorityGroup::removeMember((int)$pivotId);
        AuditService::log('DELETE', 'priority_group_member', (int)$pivotId);
        $this->redirect('/priority/groups/' . $pivot['group_id'] . '/members', 'تم إزالة العضو بنجاح ✓');
    }

    // ── Exceptions ───────────────────────────────────────────────

    public function exceptions(): void
    {
        $this->authorize('priority.grant_exception');

        $exceptions = PriorityException::allWithDetails();
        $allMembers = Member::allWithDetails('fm.member_name ASC');
        $allGroups = PriorityGroup::query(
            "SELECT pg.*, pc.category_name FROM priority_groups pg
             JOIN priority_categories pc ON pg.category_id = pc.id
             WHERE pg.is_active = 1
             ORDER BY pc.sort_order ASC, pg.sort_order ASC"
        );

        $this->render('priority.exceptions', [
            'exceptions' => $exceptions,
            'allMembers' => $allMembers,
            'allGroups'  => $allGroups,
        ]);
    }

    public function grantException(): void
    {
        $this->authorize('priority.grant_exception');
        $this->validateCsrf();

        $type = $this->request->input('exception_type', 'member');
        $reason = $this->request->input('reason', '');
        $expiresAt = $this->request->input('expires_at') ?: null;

        if ($type === 'member') {
            $memberId = (int) $this->request->input('member_id', 0);
            if (!$memberId) $this->redirect('/priority/exceptions', 'يرجى اختيار عضو', 'error');
            PriorityService::grantMemberException($memberId, $this->session->userId(), $reason, $expiresAt);
        } else {
            $groupId = (int) $this->request->input('group_id', 0);
            if (!$groupId) $this->redirect('/priority/exceptions', 'يرجى اختيار مجموعة', 'error');
            PriorityService::grantGroupException($groupId, $this->session->userId(), $reason, $expiresAt);
        }

        AuditService::log('CREATE', 'priority_exception', null, null, ['type' => $type]);
        $this->redirect('/priority/exceptions', 'تم منح الاستثناء بنجاح ✓');
    }

    public function revokeException(string $id): void
    {
        $this->authorize('priority.grant_exception');
        $this->validateCsrf();

        PriorityService::revokeException((int)$id);
        AuditService::log('UPDATE', 'priority_exception', (int)$id, null, ['action' => 'revoke']);
        $this->redirect('/priority/exceptions', 'تم إلغاء الاستثناء بنجاح ✓');
    }

    // ── Department Order ─────────────────────────────────────────

    public function deptOrder(): void
    {
        $this->authorize('priority.manage');

        DepartmentPriorityOrder::syncWithDepartments();
        $deptOrder = DepartmentPriorityOrder::allWithDetails();
        $currentDept = DepartmentPriorityOrder::currentDepartment();

        $this->render('priority.dept-order', [
            'deptOrder'   => $deptOrder,
            'currentDept' => $currentDept,
        ]);
    }

    public function saveDeptOrder(): void
    {
        $this->authorize('priority.manage');
        $this->validateCsrf();

        $order = $this->request->input('order', []);
        if (is_array($order)) {
            foreach ($order as $index => $deptId) {
                $row = DepartmentPriorityOrder::findWhere(['department_id' => (int)$deptId]);
                if ($row) {
                    DepartmentPriorityOrder::updateById($row['id'], ['sort_order' => (int)$index]);
                }
            }
        }

        AuditService::log('UPDATE', 'department_priority_order');
        $this->redirect('/priority/dept-order', 'تم حفظ ترتيب الأقسام بنجاح ✓');
    }

    // ── Advance ──────────────────────────────────────────────────

    public function advanceGroup(): void
    {
        $this->authorize('priority.manage');
        $this->validateCsrf();

        $success = PriorityService::advanceToNextGroup();
        if ($success) {
            AuditService::log('UPDATE', 'priority_advance_group');
            $this->redirect('/priority', 'تم الانتقال للمجموعة التالية ✓');
        } else {
            $this->redirect('/priority', 'لا توجد مجموعة تالية — تم الانتهاء من جميع المجموعات', 'warning');
        }
    }

    public function advanceDept(): void
    {
        $this->authorize('priority.manage');
        $this->validateCsrf();

        $success = PriorityService::advanceToNextDepartment();
        if ($success) {
            AuditService::log('UPDATE', 'priority_advance_dept');
            $this->redirect('/priority', 'تم الانتقال للقسم التالي ✓');
        } else {
            $this->redirect('/priority', 'لا يوجد قسم تالي — تم الانتهاء من جميع الأقسام', 'warning');
        }
    }

    public function resetPriority(): void
    {
        $this->authorize('priority.manage');
        $this->validateCsrf();

        PriorityService::resetPriority();
        AuditService::log('UPDATE', 'priority_reset');
        $this->redirect('/priority', 'تم إعادة تعيين نظام الأولوية ✓');
    }

    // ── Grant Registration ───────────────────────────────────────

    public function grantMemberRegister(string $memberId): void
    {
        $this->authorize('priority.grant_register');
        $this->validateCsrf();

        $success = PriorityService::grantDirectRegistration((int)$memberId);
        if ($success) {
            // Send notification
            $db = \Database::getInstance();
            $user = $db->fetch("SELECT id FROM users WHERE member_id = ?", [(int)$memberId]);
            if ($user) {
                NotificationService::send(
                    $user['id'],
                    'تم فتح التسجيل لك',
                    'قام مدير النظام بمنحك صلاحية التسجيل في الجدول.',
                    'success',
                    '/scheduling'
                );
            }
            AuditService::log('UPDATE', 'grant_register', (int)$memberId);
            $this->redirect('/priority', 'تم منح صلاحية التسجيل للعضو بنجاح ✓');
        } else {
            $this->redirect('/priority', 'لم يتم العثور على حساب للعضو', 'error');
        }
    }

    public function revokeMemberRegister(string $memberId): void
    {
        $this->authorize('priority.grant_register');
        $this->validateCsrf();

        PriorityService::revokeDirectRegistration((int)$memberId);
        AuditService::log('UPDATE', 'revoke_register', (int)$memberId);
        $this->redirect('/priority', 'تم سحب صلاحية التسجيل من العضو ✓');
    }
}

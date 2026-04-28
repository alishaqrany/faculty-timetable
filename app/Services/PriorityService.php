<?php
namespace App\Services;

require_once APP_ROOT . '/app/Models/Setting.php';
require_once APP_ROOT . '/app/Models/PriorityCategory.php';
require_once APP_ROOT . '/app/Models/PriorityGroup.php';
require_once APP_ROOT . '/app/Models/PriorityException.php';
require_once APP_ROOT . '/app/Models/DepartmentPriorityOrder.php';
require_once APP_ROOT . '/app/Models/Member.php';

use App\Models\{Setting, PriorityCategory, PriorityGroup, PriorityException, DepartmentPriorityOrder, Member};

class PriorityService
{
    // Priority modes
    const MODE_DISABLED = 'disabled';
    const MODE_GLOBAL = 'global';
    const MODE_PARALLEL_DEPT = 'parallel_dept';
    const MODE_SEQUENTIAL_DEPT = 'sequential_dept';

    /**
     * Get the current priority mode.
     */
    public static function getMode(): string
    {
        return Setting::getValue('priority_mode', self::MODE_DISABLED);
    }

    /**
     * Set the priority mode.
     */
    public static function setMode(string $mode): void
    {
        $validModes = [self::MODE_DISABLED, self::MODE_GLOBAL, self::MODE_PARALLEL_DEPT, self::MODE_SEQUENTIAL_DEPT];
        if (!in_array($mode, $validModes)) {
            throw new \InvalidArgumentException("Invalid priority mode: $mode");
        }
        Setting::setValue('priority_mode', $mode);
    }

    /**
     * Get the active category ID.
     */
    public static function getActiveCategoryId(): ?int
    {
        $val = Setting::getValue('priority_active_category_id', '');
        return $val ? (int) $val : null;
    }

    /**
     * Get the first active group ID for a category.
     */
    private static function firstGroupIdForCategory(?int $categoryId): ?int
    {
        if (!$categoryId) return null;

        $first = PriorityGroup::firstInCategory($categoryId);
        return $first ? (int) $first['id'] : null;
    }

    /**
     * Sync and initialize per-department group state for parallel mode.
     *
     * When $resetAll is true, all departments are reseeded to the first group
     * of the active category. Otherwise, only missing departments / empty
     * pointers are initialized, preserving departments that already progressed.
     */
    public static function initializeParallelDeptState(?int $categoryId = null, bool $resetAll = true): void
    {
        $categoryId = $categoryId ?? self::getActiveCategoryId();
        $firstGroupId = self::firstGroupIdForCategory($categoryId);

        if ($resetAll) {
            Setting::setValue('priority_current_group_id', $firstGroupId ? (string) $firstGroupId : '');

            DepartmentPriorityOrder::syncWithDepartments();
            DepartmentPriorityOrder::resetAll();

            if ($firstGroupId) {
                DepartmentPriorityOrder::initAllDeptGroups($firstGroupId);
            }

            return;
        }

        DepartmentPriorityOrder::syncWithDepartments($firstGroupId);
    }

    /**
     * Set the active category.
     */
    public static function setActiveCategory(int $categoryId): void
    {
        Setting::setValue('priority_active_category_id', (string) $categoryId);

        if (self::getMode() === self::MODE_PARALLEL_DEPT) {
            self::initializeParallelDeptState($categoryId, true);
            return;
        }

        // Set current group to first in category (or clear if empty)
        $firstGroupId = self::firstGroupIdForCategory($categoryId);
        Setting::setValue('priority_current_group_id', $firstGroupId ? (string) $firstGroupId : '');
    }

    /**
     * Get the current group ID.
     */
    public static function getCurrentGroupId(): ?int
    {
        $val = Setting::getValue('priority_current_group_id', '');
        return $val ? (int) $val : null;
    }

    /**
     * Get the current department ID (for sequential mode).
     */
    public static function getCurrentDeptId(): ?int
    {
        $val = Setting::getValue('priority_current_dept_id', '');
        return $val ? (int) $val : null;
    }

    /**
     * Main function: can this member register now?
     */
    public static function canMemberRegister(int $memberId): bool
    {
        $mode = self::getMode();

        // 1. Priority disabled → everyone can register
        if ($mode === self::MODE_DISABLED) {
            return true;
        }

        // 2. Check if member has an active exception
        if (self::hasException($memberId)) {
            return true;
        }

        // 3. Check the old registration_status (admin override / manual grant)
        $db = \Database::getInstance();
        $user = $db->fetch(
            "SELECT registration_status FROM users WHERE member_id = ?",
            [$memberId]
        );
        if ($user && (int) $user['registration_status'] === 1) {
            return true;
        }

        // 4. Based on mode
        switch ($mode) {
            case self::MODE_GLOBAL:
                return self::canRegisterGlobal($memberId);

            case self::MODE_PARALLEL_DEPT:
                return self::canRegisterParallelDept($memberId);

            case self::MODE_SEQUENTIAL_DEPT:
                return self::canRegisterSequentialDept($memberId);

            default:
                return false;
        }
    }

    /**
     * Global mode: check if member is in the current group or a completed group.
     */
    private static function canRegisterGlobal(int $memberId): bool
    {
        $categoryId = self::getActiveCategoryId();
        $currentGroupId = self::getCurrentGroupId();
        if (!$categoryId || !$currentGroupId) return false;

        $currentGroup = PriorityGroup::find($currentGroupId);
        if (!$currentGroup) return false;

        // Member is in the current group
        if (PriorityGroup::isMemberInGroup($currentGroupId, $memberId)) {
            return true;
        }

        // Member is in a group with lower sort_order (already passed)
        $memberGroups = PriorityGroup::findGroupsForMember($memberId);
        foreach ($memberGroups as $mg) {
            if ((int) $mg['category_id'] === $categoryId && (int) $mg['sort_order'] < (int) $currentGroup['sort_order']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parallel dept mode: each dept applies priority internally.
     * All departments run in parallel — each department tracks its own
     * current group independently via department_priority_order.current_group_id.
     */
    private static function canRegisterParallelDept(int $memberId): bool
    {
        $categoryId = self::getActiveCategoryId();
        if (!$categoryId) return false;

        // Verify the member belongs to an active department
        $member = Member::find($memberId);
        if (!$member || !$member['department_id']) return false;

        $deptId = (int) $member['department_id'];

        // Get this department's own current group (independent progression)
        $deptGroupId = DepartmentPriorityOrder::getDeptCurrentGroupId($deptId);

        // Fallback to global group if dept doesn't have its own yet
        if (!$deptGroupId) {
            $deptGroupId = self::getCurrentGroupId();
        }
        if (!$deptGroupId) return false;

        $currentGroup = PriorityGroup::find($deptGroupId);
        if (!$currentGroup) return false;

        // Check if member is in current group
        if (PriorityGroup::isMemberInGroup($deptGroupId, $memberId)) {
            return true;
        }

        // Check if member is in an already-passed group (lower sort_order)
        $memberGroups = PriorityGroup::findGroupsForMember($memberId);
        foreach ($memberGroups as $mg) {
            if ((int) $mg['category_id'] === $categoryId && (int) $mg['sort_order'] < (int) $currentGroup['sort_order']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sequential dept mode: only the current department can register.
     * Within the department, priority groups still apply.
     */
    private static function canRegisterSequentialDept(int $memberId): bool
    {
        // Check member's department
        $member = Member::find($memberId);
        if (!$member || !$member['department_id']) return false;

        // Get current department in queue
        $currentDept = DepartmentPriorityOrder::currentDepartment();
        if (!$currentDept) return false;

        // Member must be in the current department
        if ((int) $member['department_id'] !== (int) $currentDept['department_id']) {
            return false;
        }

        // Now apply group-based priority within the department
        $categoryId = self::getActiveCategoryId();
        $currentGroupId = self::getCurrentGroupId();
        if (!$categoryId || !$currentGroupId) {
            // No group priority, just dept gate
            return true;
        }

        $currentGroup = PriorityGroup::find($currentGroupId);
        if (!$currentGroup) return true;

        if (PriorityGroup::isMemberInGroup($currentGroupId, $memberId)) {
            return true;
        }

        $memberGroups = PriorityGroup::findGroupsForMember($memberId);
        foreach ($memberGroups as $mg) {
            if ((int) $mg['category_id'] === $categoryId && (int) $mg['sort_order'] < (int) $currentGroup['sort_order']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if member has an active exception.
     */
    public static function hasException(int $memberId): bool
    {
        return PriorityException::activeForMember($memberId) !== null;
    }

    /**
     * Grant individual member exception.
     */
    public static function grantMemberException(int $memberId, int $grantedBy, ?string $reason = null, ?string $expiresAt = null): int
    {
        return PriorityException::create([
            'exception_type' => 'member',
            'member_id' => $memberId,
            'granted_by' => $grantedBy,
            'reason' => $reason,
            'is_active' => 1,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Grant exception to a whole group.
     */
    public static function grantGroupException(int $groupId, int $grantedBy, ?string $reason = null, ?string $expiresAt = null): int
    {
        return PriorityException::create([
            'exception_type' => 'group',
            'group_id' => $groupId,
            'granted_by' => $grantedBy,
            'reason' => $reason,
            'is_active' => 1,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Revoke an exception.
     */
    public static function revokeException(int $exceptionId): int
    {
        return PriorityException::revoke($exceptionId);
    }

    /**
     * Advance to next group in active category.
     */
    public static function advanceToNextGroup(): bool
    {
        $categoryId = self::getActiveCategoryId();
        $currentGroupId = self::getCurrentGroupId();
        if (!$categoryId || !$currentGroupId) return false;

        $currentGroup = PriorityGroup::find($currentGroupId);
        if (!$currentGroup) return false;

        $next = PriorityGroup::nextInCategory($categoryId, (int) $currentGroup['sort_order']);
        if ($next) {
            Setting::setValue('priority_current_group_id', (string) $next['id']);
            return true;
        }

        // No next group — all groups done
        return false;
    }

    /**
     * Advance a specific department to its next group (parallel mode).
     */
    public static function advanceDeptGroup(int $departmentId): bool
    {
        $categoryId = self::getActiveCategoryId();
        if (!$categoryId) return false;

        $currentGroupId = DepartmentPriorityOrder::getDeptCurrentGroupId($departmentId);
        if (!$currentGroupId) $currentGroupId = self::getCurrentGroupId();
        if (!$currentGroupId) return false;

        $currentGroup = PriorityGroup::find($currentGroupId);
        if (!$currentGroup) return false;

        $next = PriorityGroup::nextInCategory($categoryId, (int) $currentGroup['sort_order']);
        if ($next) {
            DepartmentPriorityOrder::setDeptCurrentGroupId($departmentId, (int) $next['id']);
            return true;
        }

        // All groups done for this department
        return false;
    }

    /**
     * Advance to next department (sequential mode).
     */
    public static function advanceToNextDepartment(): bool
    {
        $currentDept = DepartmentPriorityOrder::currentDepartment();
        if (!$currentDept) return false;

        // Mark current as completed
        DepartmentPriorityOrder::markCompleted((int) $currentDept['department_id']);

        // Reset group to first for next department
        $categoryId = self::getActiveCategoryId();
        if ($categoryId) {
            $first = PriorityGroup::firstInCategory($categoryId);
            if ($first) {
                Setting::setValue('priority_current_group_id', (string) $first['id']);
            }
        }

        // Get next
        $next = DepartmentPriorityOrder::nextDepartment((int) $currentDept['sort_order']);
        if ($next) {
            Setting::setValue('priority_current_dept_id', (string) $next['department_id']);
            return true;
        }

        // All departments done
        Setting::setValue('priority_current_dept_id', '');
        return false;
    }

    /**
     * Grant direct registration to a member (sets registration_status = 1).
     */
    public static function grantDirectRegistration(int $memberId): bool
    {
        $db = \Database::getInstance();
        $user = $db->fetch("SELECT id FROM users WHERE member_id = ?", [$memberId]);
        if (!$user) return false;

        $db->execute("UPDATE users SET registration_status = 1 WHERE member_id = ?", [$memberId]);
        return true;
    }

    /**
     * Revoke direct registration from a member (sets registration_status = 0).
     */
    public static function revokeDirectRegistration(int $memberId): bool
    {
        $db = \Database::getInstance();
        $db->execute("UPDATE users SET registration_status = 0 WHERE member_id = ?", [$memberId]);
        return true;
    }

    /**
     * Get complete current priority state.
     */
    public static function getCurrentState(): array
    {
        $mode = self::getMode();
        $state = [
            'mode' => $mode,
            'mode_label' => self::getModeLabel($mode),
            'active_category' => null,
            'current_group' => null,
            'current_department' => null,
        ];

        $categoryId = self::getActiveCategoryId();
        if ($categoryId) {
            $state['active_category'] = PriorityCategory::find($categoryId);
        }

        $groupId = self::getCurrentGroupId();
        if ($groupId) {
            $state['current_group'] = PriorityGroup::find($groupId);
        }

        if ($mode === self::MODE_SEQUENTIAL_DEPT) {
            $state['current_department'] = DepartmentPriorityOrder::currentDepartment();
        }

        return $state;
    }

    /**
     * Reset the priority system.
     */
    public static function resetPriority(): void
    {
        if (self::getMode() === self::MODE_PARALLEL_DEPT) {
            self::initializeParallelDeptState(null, true);

            $firstDept = DepartmentPriorityOrder::currentDepartment();
            if ($firstDept) {
                Setting::setValue('priority_current_dept_id', (string) $firstDept['department_id']);
            }
            return;
        }

        $categoryId = self::getActiveCategoryId();
        $firstGroupId = self::firstGroupIdForCategory($categoryId);
        Setting::setValue('priority_current_group_id', $firstGroupId ? (string) $firstGroupId : '');

        DepartmentPriorityOrder::resetAll();

        // Reset current dept to first
        $firstDept = DepartmentPriorityOrder::currentDepartment();
        if ($firstDept) {
            Setting::setValue('priority_current_dept_id', (string) $firstDept['department_id']);
        }
    }

    /**
     * Get human-readable mode label.
     */
    public static function getModeLabel(string $mode): string
    {
        $labels = [
            self::MODE_DISABLED => 'معطل',
            self::MODE_GLOBAL => 'أولوية عامة (كل الأقسام)',
            self::MODE_PARALLEL_DEPT => 'أولوية بالقسم (متوازي)',
            self::MODE_SEQUENTIAL_DEPT => 'أولوية بالقسم (تسلسلي)',
        ];
        return $labels[$mode] ?? $mode;
    }

    /**
     * Get available modes.
     */
    public static function getAvailableModes(): array
    {
        return [
            self::MODE_DISABLED => 'معطل — الكل يسجل بحرية',
            self::MODE_GLOBAL => 'أولوية عامة — تطبيق على كل الأقسام في وقت واحد',
            self::MODE_PARALLEL_DEPT => 'أولوية بالقسم (متوازي) — كل قسم يطبق الأولوية داخلياً بشكل متوازي',
            self::MODE_SEQUENTIAL_DEPT => 'أولوية بالقسم (تسلسلي) — قسم بعد قسم بالترتيب',
        ];
    }
}

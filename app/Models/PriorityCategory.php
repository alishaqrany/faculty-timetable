<?php
namespace App\Models;
require_once APP_ROOT . '/core/Model.php';

class PriorityCategory extends \Model
{
    protected static string $table = 'priority_categories';
    protected static string $primaryKey = 'id';
    protected static array $fillable = ['category_name', 'description', 'is_active', 'sort_order'];

    public static function active(): array
    {
        return static::where(['is_active' => 1], 'sort_order ASC');
    }

    public static function withGroups(int $categoryId): array
    {
        return static::query(
            "SELECT pg.*, 
                    (SELECT COUNT(*) FROM priority_group_members pgm WHERE pgm.group_id = pg.id) as member_count
             FROM priority_groups pg
             WHERE pg.category_id = ? AND pg.is_active = 1
             ORDER BY pg.sort_order ASC",
            [$categoryId]
        );
    }

    public static function findWithGroups(int $id): ?array
    {
        $category = static::find($id);
        if (!$category) return null;
        $category['groups'] = static::withGroups($id);
        return $category;
    }

    public static function allWithGroupCount(): array
    {
        return static::query(
            "SELECT pc.*, 
                    (SELECT COUNT(*) FROM priority_groups pg WHERE pg.category_id = pc.id) as group_count
             FROM priority_categories pc
             ORDER BY pc.sort_order ASC"
        );
    }
}

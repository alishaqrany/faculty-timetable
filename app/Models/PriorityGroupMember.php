<?php
namespace App\Models;
require_once APP_ROOT . '/core/Model.php';

class PriorityGroupMember extends \Model
{
    protected static string $table = 'priority_group_members';
    protected static string $primaryKey = 'id';
    protected static array $fillable = ['group_id', 'member_id'];
}

<?php

class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';
    protected static array $fillable = [];

    /**
     * Find a record by primary key.
     */
    public static function find(int $id): ?array
    {
        $table = static::$table;
        $pk = static::$primaryKey;
        return Database::getInstance()->fetch(
            "SELECT * FROM `$table` WHERE `$pk` = ? LIMIT 1",
            [$id]
        );
    }

    /**
     * Get all records.
     */
    public static function all(string $orderBy = ''): array
    {
        $table = static::$table;
        $sql = "SELECT * FROM `$table`";
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        return Database::getInstance()->fetchAll($sql);
    }

    /**
     * Get records matching conditions.
     */
    public static function where(array $conditions, string $orderBy = ''): array
    {
        $table = static::$table;
        $parts = [];
        $params = [];
        foreach ($conditions as $col => $val) {
            $parts[] = "`$col` = ?";
            $params[] = $val;
        }
        $sql = "SELECT * FROM `$table` WHERE " . implode(' AND ', $parts);
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        return Database::getInstance()->fetchAll($sql, $params);
    }

    /**
     * Find first record matching conditions.
     */
    public static function findWhere(array $conditions): ?array
    {
        $table = static::$table;
        $parts = [];
        $params = [];
        foreach ($conditions as $col => $val) {
            $parts[] = "`$col` = ?";
            $params[] = $val;
        }
        $sql = "SELECT * FROM `$table` WHERE " . implode(' AND ', $parts) . " LIMIT 1";
        return Database::getInstance()->fetch($sql, $params);
    }

    /**
     * Create a new record.
     */
    public static function create(array $data): int
    {
        $filtered = static::filterFillable($data);
        return Database::getInstance()->insert(static::$table, $filtered);
    }

    /**
     * Update a record by primary key.
     */
    public static function updateById(int $id, array $data): int
    {
        $filtered = static::filterFillable($data);
        return Database::getInstance()->update(
            static::$table,
            $filtered,
            [static::$primaryKey => $id]
        );
    }

    /**
     * Delete a record by primary key.
     */
    public static function destroy(int $id): int
    {
        return Database::getInstance()->delete(
            static::$table,
            [static::$primaryKey => $id]
        );
    }

    /**
     * Count all records, optionally filtered.
     */
    public static function count(array $conditions = []): int
    {
        $table = static::$table;
        $sql = "SELECT COUNT(*) as cnt FROM `$table`";
        $params = [];
        if ($conditions) {
            $parts = [];
            foreach ($conditions as $col => $val) {
                $parts[] = "`$col` = ?";
                $params[] = $val;
            }
            $sql .= " WHERE " . implode(' AND ', $parts);
        }
        $row = Database::getInstance()->fetch($sql, $params);
        return (int)($row['cnt'] ?? 0);
    }

    /**
     * Paginate results.
     */
    public static function paginate(int $page = 1, int $perPage = 15, string $orderBy = '', array $conditions = []): array
    {
        $table = static::$table;
        $offset = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(*) as cnt FROM `$table`";
        $dataSql = "SELECT * FROM `$table`";
        $params = [];

        if ($conditions) {
            $parts = [];
            foreach ($conditions as $col => $val) {
                $parts[] = "`$col` = ?";
                $params[] = $val;
            }
            $whereClause = " WHERE " . implode(' AND ', $parts);
            $countSql .= $whereClause;
            $dataSql .= $whereClause;
        }

        $total = (int) Database::getInstance()->fetchColumn($countSql, $params);

        if ($orderBy) {
            $dataSql .= " ORDER BY $orderBy";
        }
        $dataSql .= " LIMIT ? OFFSET ?";
        $dataParams = array_merge($params, [$perPage, $offset]);
        $data = Database::getInstance()->fetchAll($dataSql, $dataParams);

        return [
            'data'         => $data,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Execute a custom query.
     */
    public static function query(string $sql, array $params = []): array
    {
        return Database::getInstance()->fetchAll($sql, $params);
    }

    /**
     * Execute a custom query and return first row.
     */
    public static function queryOne(string $sql, array $params = []): ?array
    {
        return Database::getInstance()->fetch($sql, $params);
    }

    /**
     * Filter data to only include fillable fields.
     */
    protected static function filterFillable(array $data): array
    {
        if (empty(static::$fillable)) {
            return $data;
        }
        return array_intersect_key($data, array_flip(static::$fillable));
    }
}

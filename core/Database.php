<?php

class Database
{
    private static ?Database $instance = null;
    private mysqli $conn;

    private function __construct(string $host, string $user, string $pass, string $dbname)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->conn = new mysqli($host, $user, $pass, $dbname);
        $this->conn->set_charset('utf8mb4');
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            $cfg = require APP_ROOT . '/config/database.php';
            self::$instance = new self($cfg['host'], $cfg['username'], $cfg['password'], $cfg['database']);
        }
        return self::$instance;
    }

    public static function setInstance(self $db): void
    {
        self::$instance = $db;
    }

    public static function connectWith(string $host, string $user, string $pass, string $dbname): self
    {
        self::$instance = new self($host, $user, $pass, $dbname);
        return self::$instance;
    }

    public function getConnection(): mysqli
    {
        return $this->conn;
    }

    /**
     * Execute a query with bound parameters.
     * @param string $sql   SQL with ? placeholders
     * @param array  $params Values to bind
     * @return mysqli_stmt
     */
    public function execute(string $sql, array $params = []): mysqli_stmt
    {
        $stmt = $this->conn->prepare($sql);
        if ($params) {
            $types = $this->detectTypes($params);
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt;
    }

    /**
     * Fetch a single row as associative array.
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->execute($sql, $params);
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    /**
     * Fetch all rows as array of associative arrays.
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->execute($sql, $params);
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /**
     * Fetch a single scalar value.
     */
    public function fetchColumn(string $sql, array $params = [])
    {
        $row = $this->fetch($sql, $params);
        return $row ? reset($row) : null;
    }

    /**
     * Insert a row and return lastInsertId.
     */
    public function insert(string $table, array $data): int
    {
        $cols = implode(', ', array_map(fn($c) => "`$c`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `$table` ($cols) VALUES ($placeholders)";
        $this->execute($sql, array_values($data));
        return (int) $this->conn->insert_id;
    }

    /**
     * Update rows matching conditions.
     */
    public function update(string $table, array $data, array $where): int
    {
        $setParts = [];
        $params = [];
        foreach ($data as $col => $val) {
            $setParts[] = "`$col` = ?";
            $params[] = $val;
        }
        $whereParts = [];
        foreach ($where as $col => $val) {
            $whereParts[] = "`$col` = ?";
            $params[] = $val;
        }
        $sql = "UPDATE `$table` SET " . implode(', ', $setParts)
             . " WHERE " . implode(' AND ', $whereParts);
        $stmt = $this->execute($sql, $params);
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }

    /**
     * Delete rows matching conditions.
     */
    public function delete(string $table, array $where): int
    {
        $parts = [];
        $params = [];
        foreach ($where as $col => $val) {
            $parts[] = "`$col` = ?";
            $params[] = $val;
        }
        $sql = "DELETE FROM `$table` WHERE " . implode(' AND ', $parts);
        $stmt = $this->execute($sql, $params);
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }

    public function beginTransaction(): void
    {
        $this->conn->begin_transaction();
    }

    public function commit(): void
    {
        $this->conn->commit();
    }

    public function rollback(): void
    {
        $this->conn->rollback();
    }

    public function lastInsertId(): int
    {
        return (int) $this->conn->insert_id;
    }

    /**
     * Raw query (for DDL, etc.)
     */
    public function raw(string $sql): bool
    {
        return $this->conn->query($sql) !== false;
    }

    public function multiQuery(string $sql): bool
    {
        $result = $this->conn->multi_query($sql);
        while ($this->conn->next_result()) {
            $this->conn->store_result();
        }
        return $result;
    }

    public function escape(string $value): string
    {
        return $this->conn->real_escape_string($value);
    }

    private function detectTypes(array $params): string
    {
        $types = '';
        foreach ($params as $p) {
            if (is_int($p)) {
                $types .= 'i';
            } elseif (is_float($p)) {
                $types .= 'd';
            } elseif (is_null($p)) {
                $types .= 's';
            } else {
                $types .= 's';
            }
        }
        return $types;
    }
}

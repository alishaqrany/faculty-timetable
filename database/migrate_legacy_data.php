<?php
/**
 * Legacy Data Migration Script
 * ─────────────────────────────
 * Migrates data from the old timetable DB (legacy install.php schema)
 * into the new timetable_v2 schema.
 *
 * Usage:
 *   php database/migrate_legacy_data.php <old_db_name>
 *   php database/migrate_legacy_data.php timetable
 *
 * Prerequisites:
 *   1. Run `php database/migrate.php` first to create the new schema.
 *   2. Both old and new databases must be on the same MySQL server.
 */

// ── Bootstrap ────────────────────────────────────────────────────
define('APP_ROOT', dirname(__DIR__));
$newConfig = require APP_ROOT . '/config/database.php';

// Parse CLI argument
$oldDb = $argv[1] ?? null;
if (!$oldDb) {
    echo "\033[31mUsage: php database/migrate_legacy_data.php <old_db_name>\033[0m\n";
    echo "Example: php database/migrate_legacy_data.php timetable\n";
    exit(1);
}

$newDb = $newConfig['database'];

echo "╔══════════════════════════════════════════╗\n";
echo "║   ترحيل البيانات من القاعدة القديمة       ║\n";
echo "╚══════════════════════════════════════════╝\n\n";
echo "  Old DB: {$oldDb}\n";
echo "  New DB: {$newDb}\n\n";

// ── Connect ──────────────────────────────────────────────────────
$conn = new mysqli(
    $newConfig['host'],
    $newConfig['username'],
    $newConfig['password']
);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}

$conn->set_charset($newConfig['charset'] ?? 'utf8mb4');

// Verify both databases exist
$result = $conn->query("SHOW DATABASES LIKE '{$conn->real_escape_string($oldDb)}'");
if ($result->num_rows === 0) {
    die("\033[31mOld database '{$oldDb}' not found.\033[0m\n");
}

$result = $conn->query("SHOW DATABASES LIKE '{$conn->real_escape_string($newDb)}'");
if ($result->num_rows === 0) {
    die("\033[31mNew database '{$newDb}' not found. Run migrations first: php database/migrate.php\033[0m\n");
}

// ── Helper Functions ─────────────────────────────────────────────
$counters = ['inserted' => 0, 'skipped' => 0, 'errors' => 0];

function migrate_table(mysqli $conn, string $oldDb, string $newDb, string $table, string $selectSql, string $insertSql, callable $rowMapper): void
{
    global $counters;
    echo "  [{$table}] ";

    $rows = $conn->query("SELECT {$selectSql} FROM `{$oldDb}`.`{$table}`");
    if (!$rows) {
        echo "\033[33mSKIPPED (table not found or query error)\033[0m\n";
        return;
    }

    $count = 0;
    $stmt = $conn->prepare("INSERT IGNORE INTO `{$newDb}`.`{$table}` {$insertSql}");
    if (!$stmt) {
        echo "\033[31mERROR preparing: {$conn->error}\033[0m\n";
        $counters['errors']++;
        return;
    }

    while ($row = $rows->fetch_assoc()) {
        $mapped = $rowMapper($row);
        if ($mapped === null) {
            $counters['skipped']++;
            continue;
        }
        // Dynamic bind
        $types = $mapped['types'];
        $values = $mapped['values'];
        $stmt->bind_param($types, ...$values);
        if ($stmt->execute()) {
            $count++;
            $counters['inserted']++;
        } else {
            $counters['skipped']++;
        }
    }

    $stmt->close();
    $rows->free();
    echo "\033[32m{$count} rows migrated\033[0m\n";
}

// ── Disable FK checks ───────────────────────────────────────────
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

echo "Migrating tables...\n\n";

// ── 1. Departments ──────────────────────────────────────────────
migrate_table($conn, $oldDb, $newDb, 'departments',
    'department_id, department_name',
    '(department_id, department_name, is_active, created_at) VALUES (?, ?, 1, NOW())',
    function ($row) {
        return [
            'types'  => 'is',
            'values' => [(int)$row['department_id'], $row['department_name']],
        ];
    }
);

// ── 2. Levels ───────────────────────────────────────────────────
migrate_table($conn, $oldDb, $newDb, 'levels',
    'level_id, level_name',
    '(level_id, level_name, is_active, created_at) VALUES (?, ?, 1, NOW())',
    function ($row) {
        return [
            'types'  => 'is',
            'values' => [(int)$row['level_id'], $row['level_name']],
        ];
    }
);

// ── 3. Classrooms ───────────────────────────────────────────────
migrate_table($conn, $oldDb, $newDb, 'classrooms',
    'classroom_id, classroom_name',
    '(classroom_id, classroom_name, is_active, created_at) VALUES (?, ?, 1, NOW())',
    function ($row) {
        return [
            'types'  => 'is',
            'values' => [(int)$row['classroom_id'], $row['classroom_name']],
        ];
    }
);

// ── 4. Faculty Members ──────────────────────────────────────────
migrate_table($conn, $oldDb, $newDb, 'faculty_members',
    'member_id, member_name, academic_degree, join_date, ranking, role',
    '(member_id, member_name, degree, join_date, ranking, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())',
    function ($row) {
        return [
            'types'  => 'isssss',
            'values' => [
                (int)$row['member_id'],
                $row['member_name'],
                $row['academic_degree'] ?? '',
                $row['join_date'] ?? date('Y-m-d'),
                $row['ranking'] ?? 0,
                $row['role'] ?? '',
            ],
        ];
    }
);

// ── 5. Subjects ─────────────────────────────────────────────────
migrate_table($conn, $oldDb, $newDb, 'subjects',
    'subject_id, subject_name, department_id, level_id, hours',
    '(subject_id, subject_name, department_id, level_id, hours, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())',
    function ($row) {
        return [
            'types'  => 'isiii',
            'values' => [
                (int)$row['subject_id'],
                $row['subject_name'],
                (int)$row['department_id'],
                (int)$row['level_id'],
                (int)($row['hours'] ?? 0),
            ],
        ];
    }
);

// ── 6. Sections ─────────────────────────────────────────────────
migrate_table($conn, $oldDb, $newDb, 'sections',
    'section_id, section_name, department_id, level_id',
    '(section_id, section_name, department_id, level_id, is_active, created_at) VALUES (?, ?, ?, ?, 1, NOW())',
    function ($row) {
        return [
            'types'  => 'isii',
            'values' => [
                (int)$row['section_id'],
                $row['section_name'],
                (int)$row['department_id'],
                (int)$row['level_id'],
            ],
        ];
    }
);

// ── 7. Sessions ─────────────────────────────────────────────────
migrate_table($conn, $oldDb, $newDb, 'sessions',
    'session_id, day, session_name, start_time, end_time, duration',
    '(session_id, day, session_name, start_time, end_time, duration, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())',
    function ($row) {
        return [
            'types'  => 'issssi',
            'values' => [
                (int)$row['session_id'],
                $row['day'],
                $row['session_name'],
                $row['start_time'],
                $row['end_time'],
                (int)($row['duration'] ?? 0),
            ],
        ];
    }
);

// ── 8. Member Courses ───────────────────────────────────────────
migrate_table($conn, $oldDb, $newDb, 'member_courses',
    'member_course_id, member_id, subject_id, section_id',
    '(member_course_id, member_id, subject_id, section_id, created_at) VALUES (?, ?, ?, ?, NOW())',
    function ($row) {
        return [
            'types'  => 'iiii',
            'values' => [
                (int)$row['member_course_id'],
                (int)$row['member_id'],
                (int)$row['subject_id'],
                (int)$row['section_id'],
            ],
        ];
    }
);

// ── 9. Timetable ────────────────────────────────────────────────
migrate_table($conn, $oldDb, $newDb, 'timetable',
    'timetable_id, member_course_id, classroom_id, session_id',
    '(timetable_id, member_course_id, classroom_id, session_id, status, created_at) VALUES (?, ?, ?, ?, \'منشور\', NOW())',
    function ($row) {
        return [
            'types'  => 'iiii',
            'values' => [
                (int)$row['timetable_id'],
                (int)$row['member_course_id'],
                (int)$row['classroom_id'],
                (int)$row['session_id'],
            ],
        ];
    }
);

// ── 10. Users ───────────────────────────────────────────────────
// Map old users: registration_status=1 → admin role (role_id=1)
// Need to get admin role_id
$adminRole = $conn->query("SELECT id FROM `{$newDb}`.`roles` WHERE slug = 'admin' LIMIT 1");
$adminRoleId = $adminRole ? ($adminRole->fetch_assoc()['id'] ?? 1) : 1;

$facultyRole = $conn->query("SELECT id FROM `{$newDb}`.`roles` WHERE slug = 'faculty' LIMIT 1");
$facultyRoleId = $facultyRole ? ($facultyRole->fetch_assoc()['id'] ?? 3) : 3;

migrate_table($conn, $oldDb, $newDb, 'users',
    'id, username, password, member_id, registration_status',
    '(id, username, password, member_id, role_id, registration_status, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())',
    function ($row) use ($adminRoleId, $facultyRoleId) {
        $isAdmin = ((int)$row['registration_status'] === 1);
        $roleId = $isAdmin ? $adminRoleId : $facultyRoleId;
        return [
            'types'  => 'issiiil',  // l for long (tinyint flags)
            'values' => [
                (int)$row['id'],
                $row['username'],
                $row['password'],
                (int)($row['member_id'] ?? 0),
                $roleId,
                (int)$row['registration_status'],
                $isAdmin ? 1 : 0,
            ],
        ];
    }
);

// ── Re-enable FK checks ─────────────────────────────────────────
$conn->query("SET FOREIGN_KEY_CHECKS = 1");
$conn->close();

echo "\n════════════════════════════════════════════\n";
echo "  Migration complete!\n";
echo "  Inserted: {$counters['inserted']}\n";
echo "  Skipped:  {$counters['skipped']}\n";
echo "  Errors:   {$counters['errors']}\n";
echo "════════════════════════════════════════════\n";

if ($counters['errors'] > 0) {
    exit(1);
}

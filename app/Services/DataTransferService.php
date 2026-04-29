<?php
namespace App\Services;

class DataTransferService
{
    private const TABLES = [
        'roles',
        'permissions',
        'role_permissions',
        'academic_degrees',
        'departments',
        'levels',
        'academic_years',
        'semesters',
        'priority_categories',
        'priority_groups',
        'faculty_members',
        'users',
        'priority_group_members',
        'priority_exceptions',
        'subjects',
        'divisions',
        'sections',
        'classrooms',
        'sessions',
        'member_courses',
        'member_course_shared_divisions',
        'timetable',
        'department_priority_order',
        'settings',
        'notifications',
        'api_tokens',
        'audit_logs',
        'migrations',
    ];

    private const RESET_TABLES = [
        'timetable',
        'member_course_shared_divisions',
        'member_courses',
        'sections',
        'divisions',
        'subjects',
        'priority_exceptions',
        'priority_group_members',
        'priority_groups',
        'priority_categories',
        'department_priority_order',
        'classrooms',
        'sessions',
        'notifications',
        'api_tokens',
        'audit_logs',
        'semesters',
        'academic_years',
        'levels',
        'departments',
    ];

    private const SAMPLE_SQL_FILENAME = 'sample_faculty_demo_data.sql';
    private const SAMPLE_YEAR_NAME = '2026/2027 (بيانات تجريبية)';
    private const SAMPLE_SEMESTER_ONE = 'الفصل الدراسي الأول - تجريبي';
    private const SAMPLE_SEMESTER_TWO = 'الفصل الدراسي الثاني - تجريبي';
    private const DEMO_PASSWORD_HASH = '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZag0J7sXh8M6gZfQnUnxE27XGr0aG';

    public static function tables(): array
    {
        return self::TABLES;
    }

    public static function sampleSqlFilename(): string
    {
        return self::SAMPLE_SQL_FILENAME;
    }

    public static function generateSampleSqlContent(): string
    {
        $levels = self::defaultLevelsDefinition();
        $departments = self::sampleDepartments();
        $classrooms = self::sampleClassrooms();
        $sessions = self::sampleSessions();
        $lectureRooms = self::sampleLectureRoomNames();

        $levelRows = [];
        foreach ($levels as $level) {
            $levelRows[] = [
                self::sqlString($level['level_name']),
                self::sqlString($level['level_code']),
                (string) $level['sort_order'],
                '1',
            ];
        }

        $departmentRows = [];
        $divisionRows = [];
        $sectionRows = [];
        $facultyRows = [];
        $userRows = [];
        $subjectRows = [];
        $memberCourseRows = [];
        $timetableRows = [];

        $facultyFirstNames = ['أحمد', 'محمد', 'محمود', 'خالد', 'مصطفى', 'منى', 'سارة', 'نجلاء', 'هبة', 'دعاء'];
        $assistantFirstNames = ['إيمان', 'نهى', 'آلاء', 'مروة', 'آية', 'ولاء'];
        $facultyDegrees = ['أستاذ', 'أستاذ مشارك', 'أستاذ مساعد', 'أستاذ مساعد', 'محاضر', 'محاضر', 'دكتوراه', 'دكتوراه', 'ماجستير', 'ماجستير'];
        $phoneCounter = 1000001;
        $scheduledTheoryRows = [];

        foreach ($departments as $department) {
            $departmentRows[] = [
                self::sqlString($department['name']),
                self::sqlString($department['code']),
                self::sqlString($department['description']),
                '1',
            ];

            foreach ($levels as $level) {
                $divisionNames = in_array($level['level_code'], ['L1', 'L2'], true)
                    ? ['عامة']
                    : ($department['branches'][$level['level_code']] ?? ['عامة']);

                foreach ($divisionNames as $divisionName) {
                    $divisionRows[] = [
                        self::sqlString($divisionName),
                        self::departmentIdExpr($department['code']),
                        self::levelIdExpr($level['level_code']),
                        $divisionName === 'عامة' ? '320' : '160',
                        '1',
                    ];

                    $sectionsCount = $divisionName === 'عامة' ? 12 : 6;
                    for ($index = 1; $index <= $sectionsCount; $index++) {
                        $sectionRows[] = [
                            self::divisionIdExpr($department['code'], $level['level_code'], $divisionName),
                            self::sqlString('سكشن ' . $index),
                            self::departmentIdExpr($department['code']),
                            self::levelIdExpr($level['level_code']),
                            $divisionName === 'عامة' ? '32' : '28',
                            '1',
                        ];
                    }
                }
            }

            for ($index = 0; $index < 10; $index++) {
                $email = self::memberEmail($department['slug'], 'faculty', $index + 1);
                $facultyRows[] = [
                    self::sqlString($facultyFirstNames[$index] . ' ' . $department['member_suffix']),
                    self::sqlString($email),
                    self::sqlString('010' . str_pad((string) $phoneCounter++, 7, '0', STR_PAD_LEFT)),
                    self::departmentIdExpr($department['code']),
                    'NULL',
                    self::sqlString($facultyDegrees[$index]),
                    self::sqlString(sprintf('%04d-09-01', 2014 + $index)),
                    (string) ($index + 1),
                    self::sqlString($index === 0 ? 'رئيس قسم' : 'عضو هيئة تدريس'),
                    '1',
                ];
            }

            for ($index = 0; $index < 6; $index++) {
                $email = self::memberEmail($department['slug'], 'assistant', $index + 1);
                $facultyRows[] = [
                    self::sqlString($assistantFirstNames[$index] . ' ' . $department['member_suffix']),
                    self::sqlString($email),
                    self::sqlString('010' . str_pad((string) $phoneCounter++, 7, '0', STR_PAD_LEFT)),
                    self::departmentIdExpr($department['code']),
                    'NULL',
                    self::sqlString('معيد'),
                    self::sqlString(sprintf('%04d-10-01', 2021 + $index)),
                    (string) (101 + $index),
                    self::sqlString('هيئة معاونة'),
                    '1',
                ];
            }

            $userRows[] = [
                self::sqlString('head_' . $department['slug']),
                self::sqlString(self::memberEmail($department['slug'], 'faculty', 1)),
                self::sqlString(self::DEMO_PASSWORD_HASH),
                self::memberIdExpr(self::memberEmail($department['slug'], 'faculty', 1)),
                self::roleIdExpr('dept_head'),
                '1',
                '1',
            ];

            $userRows[] = [
                self::sqlString('faculty_' . $department['slug']),
                self::sqlString(self::memberEmail($department['slug'], 'faculty', 2)),
                self::sqlString(self::DEMO_PASSWORD_HASH),
                self::memberIdExpr(self::memberEmail($department['slug'], 'faculty', 2)),
                self::roleIdExpr('faculty'),
                '1',
                '1',
            ];

            foreach ($department['subjects'] as $levelCode => $subjects) {
                foreach ($subjects as $subject) {
                    $subjectRows[] = [
                        self::sqlString($subject['name']),
                        self::sqlString($subject['code']),
                        self::departmentIdExpr($department['code']),
                        self::levelIdExpr($levelCode),
                        $subject['type'] === 'نظري' ? '2' : '2',
                        $subject['type'] === 'نظري' ? '2' : '1',
                        self::sqlString($subject['type']),
                        '1',
                    ];

                    $memberEmail = self::memberEmail($department['slug'], $subject['member_group'], $subject['member_index']);
                    $memberCourseRows[] = [
                        self::memberIdExpr($memberEmail),
                        self::subjectIdExpr($subject['code']),
                        $subject['type'] === 'نظري'
                            ? self::divisionIdExpr($department['code'], $levelCode, $subject['division'])
                            : 'NULL',
                        $subject['type'] === 'عملي'
                            ? self::sectionIdExpr($department['code'], $levelCode, $subject['division'], $subject['section'] ?? 'سكشن 1')
                            : 'NULL',
                        self::sqlString($subject['type']),
                        '0',
                        self::semesterIdExpr(self::SAMPLE_SEMESTER_ONE),
                        self::academicYearIdExpr(self::SAMPLE_YEAR_NAME),
                    ];

                    if (!empty($subject['scheduled'])) {
                        $scheduledTheoryRows[] = [
                            'subject_code' => $subject['code'],
                            'member_email' => $memberEmail,
                        ];
                    }
                }
            }
        }

        foreach ($scheduledTheoryRows as $index => $scheduled) {
            $session = $sessions[$index % count($sessions)];
            $roomName = $lectureRooms[$index % count($lectureRooms)];

            $timetableRows[] = [
                self::memberCourseIdExpr($scheduled['subject_code'], $scheduled['member_email'], 'نظري'),
                self::classroomIdExpr($roomName),
                self::sessionIdExpr($session['day'], $session['session_name']),
                self::semesterIdExpr(self::SAMPLE_SEMESTER_ONE),
                self::academicYearIdExpr(self::SAMPLE_YEAR_NAME),
                self::sqlString('منشور'),
                self::adminUserIdExpr(),
            ];
        }

        $lines = [
            '-- Rich sample data for Timetable Management System',
            '-- 4 levels, 4 departments, 24 divisions, 192 sections, 64 faculty/assistants, 64 subjects, 64 teaching assignments, 24 timetable rows.',
            '-- Demo password for all generated accounts: password',
            '-- Generated at: ' . date('Y-m-d H:i:s'),
            '',
            'SET NAMES utf8mb4;',
            'SET FOREIGN_KEY_CHECKS = 0;',
            '',
            'TRUNCATE TABLE `timetable`;',
            'TRUNCATE TABLE `member_course_shared_divisions`;',
            'TRUNCATE TABLE `member_courses`;',
            'TRUNCATE TABLE `sections`;',
            'TRUNCATE TABLE `divisions`;',
            'TRUNCATE TABLE `subjects`;',
            'TRUNCATE TABLE `department_priority_order`;',
            'TRUNCATE TABLE `priority_exceptions`;',
            'TRUNCATE TABLE `priority_group_members`;',
            'TRUNCATE TABLE `priority_groups`;',
            'TRUNCATE TABLE `priority_categories`;',
            'TRUNCATE TABLE `classrooms`;',
            'TRUNCATE TABLE `sessions`;',
            'TRUNCATE TABLE `notifications`;',
            'TRUNCATE TABLE `api_tokens`;',
            'TRUNCATE TABLE `audit_logs`;',
            'TRUNCATE TABLE `semesters`;',
            'TRUNCATE TABLE `academic_years`;',
            'TRUNCATE TABLE `levels`;',
            'TRUNCATE TABLE `departments`;',
            "DELETE FROM `users` WHERE `role_id` IS NULL OR `role_id` <> (SELECT `id` FROM `roles` WHERE `role_slug` = 'admin' LIMIT 1);",
            "DELETE fm FROM `faculty_members` fm LEFT JOIN `users` u ON u.`member_id` = fm.`member_id` AND u.`role_id` = (SELECT `id` FROM `roles` WHERE `role_slug` = 'admin' LIMIT 1) WHERE u.`id` IS NULL;",
            'SET FOREIGN_KEY_CHECKS = 1;',
            '',
        ];

        self::appendInsertBlock($lines, 'levels', ['level_name', 'level_code', 'sort_order', 'is_active'], $levelRows);
        self::appendInsertBlock($lines, 'departments', ['department_name', 'department_code', 'description', 'is_active'], $departmentRows);

        self::appendInsertBlock($lines, 'academic_years', ['year_name', 'start_date', 'end_date', 'is_current', 'is_active'], [[
            self::sqlString(self::SAMPLE_YEAR_NAME),
            self::sqlString('2026-09-01'),
            self::sqlString('2027-06-30'),
            '1',
            '1',
        ]]);

        self::appendInsertBlock($lines, 'semesters', ['semester_name', 'academic_year_id', 'start_date', 'end_date', 'is_current', 'is_active'], [
            [
                self::sqlString(self::SAMPLE_SEMESTER_ONE),
                self::academicYearIdExpr(self::SAMPLE_YEAR_NAME),
                self::sqlString('2026-09-01'),
                self::sqlString('2027-01-31'),
                '1',
                '1',
            ],
            [
                self::sqlString(self::SAMPLE_SEMESTER_TWO),
                self::academicYearIdExpr(self::SAMPLE_YEAR_NAME),
                self::sqlString('2027-02-10'),
                self::sqlString('2027-06-30'),
                '0',
                '1',
            ],
        ]);

        $classroomRows = [];
        foreach ($classrooms as $room) {
            $classroomRows[] = [
                self::sqlString($room['classroom_name']),
                (string) $room['capacity'],
                self::sqlString($room['classroom_type']),
                self::sqlString($room['building']),
                self::sqlString($room['floor']),
                '1',
            ];
        }
        self::appendInsertBlock($lines, 'classrooms', ['classroom_name', 'capacity', 'classroom_type', 'building', 'floor', 'is_active'], $classroomRows);

        $sessionRows = [];
        foreach ($sessions as $session) {
            $sessionRows[] = [
                self::sqlString($session['day']),
                self::sqlString($session['session_name']),
                self::sqlString($session['start_time']),
                self::sqlString($session['end_time']),
                (string) $session['duration'],
                '1',
            ];
        }
        self::appendInsertBlock($lines, 'sessions', ['day', 'session_name', 'start_time', 'end_time', 'duration', 'is_active'], $sessionRows);
        self::appendInsertBlock($lines, 'divisions', ['division_name', 'department_id', 'level_id', 'capacity', 'is_active'], $divisionRows);
        self::appendInsertBlock($lines, 'sections', ['division_id', 'section_name', 'department_id', 'level_id', 'capacity', 'is_active'], $sectionRows);
        self::appendInsertBlock($lines, 'faculty_members', ['member_name', 'email', 'phone', 'department_id', 'degree_id', 'degree', 'join_date', 'ranking', 'role', 'is_active'], $facultyRows);
        self::appendInsertBlock($lines, 'users', ['username', 'email', 'password', 'member_id', 'role_id', 'registration_status', 'is_active'], $userRows);
        self::appendInsertBlock($lines, 'subjects', ['subject_name', 'subject_code', 'department_id', 'level_id', 'hours', 'credit_hours', 'subject_type', 'is_active'], $subjectRows);
        self::appendInsertBlock($lines, 'member_courses', ['member_id', 'subject_id', 'division_id', 'section_id', 'assignment_type', 'is_shared', 'semester_id', 'academic_year_id'], $memberCourseRows);
        self::appendInsertBlock($lines, 'timetable', ['member_course_id', 'classroom_id', 'session_id', 'semester_id', 'academic_year_id', 'status', 'created_by'], $timetableRows);

        $lines[] = '-- Demo accounts';
        foreach ($departments as $department) {
            $lines[] = '-- head_' . $department['slug'] . ' / password';
            $lines[] = '-- faculty_' . $department['slug'] . ' / password';
        }

        return implode("\n", $lines) . "\n";
    }

    public static function resetSystem(): void
    {
        $db = \Database::getInstance();

        $db->beginTransaction();

        try {
            $db->raw('SET FOREIGN_KEY_CHECKS = 0');

            foreach (self::RESET_TABLES as $table) {
                if (!self::tableExists($db, $table)) {
                    continue;
                }

                $db->raw("DELETE FROM `{$table}`");
            }

            self::deleteNonAdminUsers($db);
            self::deleteNonAdminFacultyMembers($db);

            $db->raw('SET FOREIGN_KEY_CHECKS = 1');

            self::runDefaultSeeds($db);
            self::seedDefaultLevels($db);

            $db->commit();
        } catch (\Throwable $exception) {
            $db->rollback();

            try {
                $db->raw('SET FOREIGN_KEY_CHECKS = 1');
            } catch (\Throwable $innerException) {
                // Ignore cleanup failures and bubble up the original reset exception.
            }

            throw $exception;
        }
    }

    public static function exportSql(string $filename): void
    {
        $db = \Database::getInstance();

        header('Content-Type: application/sql; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo "-- Timetable full data export\n";
        echo "-- Generated at: " . date('Y-m-d H:i:s') . "\n\n";
        echo "SET NAMES utf8mb4;\n";
        echo "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach (self::TABLES as $table) {
            $columnsRows = $db->fetchAll("SHOW COLUMNS FROM `{$table}`");
            if (empty($columnsRows)) {
                continue;
            }

            $columns = array_map(static fn(array $row) => $row['Field'], $columnsRows);
            $quotedColumns = implode(', ', array_map(static fn(string $col) => "`{$col}`", $columns));

            echo "-- Table: {$table}\n";
            echo "TRUNCATE TABLE `{$table}`;\n";

            $rows = $db->fetchAll("SELECT * FROM `{$table}`");
            foreach ($rows as $row) {
                $values = [];
                foreach ($columns as $col) {
                    $value = $row[$col] ?? null;
                    if ($value === null) {
                        $values[] = 'NULL';
                    } else {
                        $values[] = "'" . $db->escape((string)$value) . "'";
                    }
                }

                echo "INSERT INTO `{$table}` ({$quotedColumns}) VALUES (" . implode(', ', $values) . ");\n";
            }

            echo "\n";
        }

        echo "SET FOREIGN_KEY_CHECKS = 1;\n";
        exit;
    }

    public static function exportExcel(string $filename): void
    {
        $db = \Database::getInstance();

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        echo "<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\">\n";

        foreach (self::TABLES as $table) {
            $sheetName = mb_substr($table, 0, 31);
            $columnsRows = $db->fetchAll("SHOW COLUMNS FROM `{$table}`");
            if (empty($columnsRows)) {
                continue;
            }

            $columns = array_map(static fn(array $row) => $row['Field'], $columnsRows);

            echo '<Worksheet ss:Name="' . self::xmlEscape($sheetName) . '"><Table>';
            echo '<Row>';
            foreach ($columns as $col) {
                echo '<Cell><Data ss:Type="String">' . self::xmlEscape($col) . '</Data></Cell>';
            }
            echo '</Row>';

            $rows = $db->fetchAll("SELECT * FROM `{$table}`");
            foreach ($rows as $row) {
                echo '<Row>';
                foreach ($columns as $col) {
                    $value = $row[$col] ?? '';
                    echo '<Cell><Data ss:Type="String">' . self::xmlEscape((string)$value) . '</Data></Cell>';
                }
                echo '</Row>';
            }

            echo '</Table></Worksheet>';
        }

        echo '</Workbook>';
        exit;
    }

    public static function importSql(string $sql): void
    {
        $sql = self::stripUtf8Bom($sql);
        if (trim($sql) === '') {
            throw new \RuntimeException('ملف SQL فارغ.');
        }

        $db = \Database::getInstance()->getConnection();

        if (!$db->multi_query($sql)) {
            throw new \RuntimeException('تعذر تنفيذ ملف SQL.');
        }

        while ($db->more_results()) {
            $db->next_result();
            $result = $db->store_result();
            if ($result instanceof \mysqli_result) {
                $result->free();
            }
        }
    }

    public static function importExcelXml(string $xml): array
    {
        $xml = self::stripUtf8Bom($xml);
        if (trim($xml) === '') {
            throw new \RuntimeException('ملف Excel/XML فارغ.');
        }

        $dom = new \DOMDocument();
        $loaded = @$dom->loadXML($xml, LIBXML_NONET | LIBXML_NOBLANKS);
        if (!$loaded) {
            throw new \RuntimeException('صيغة ملف Excel غير مدعومة. يرجى استخدام ملف Excel المصدر من النظام.');
        }

        $xpath = new \DOMXPath($dom);
        $worksheetNodes = $xpath->query('//*[local-name()="Worksheet"]');

        if (!$worksheetNodes || $worksheetNodes->length === 0) {
            throw new \RuntimeException('لا توجد أوراق بيانات داخل الملف.');
        }

        $db = \Database::getInstance();

        $tableMap = [];
        foreach (self::TABLES as $table) {
            $tableMap[$table] = $table;
        }

        $importedTables = [];
        $insertCount = 0;

        $db->raw('SET FOREIGN_KEY_CHECKS = 0');
        foreach (array_reverse(self::TABLES) as $table) {
            if (!self::tableExists($db, $table)) {
                continue;
            }
            $db->raw("TRUNCATE TABLE `{$table}`");
        }
        $db->raw('SET FOREIGN_KEY_CHECKS = 1');

        foreach ($worksheetNodes as $worksheetNode) {
            $rawName = $worksheetNode->attributes?->getNamedItem('ss:Name')?->nodeValue
                ?? $worksheetNode->attributes?->getNamedItem('Name')?->nodeValue
                ?? '';

            $sheetName = trim((string)$rawName);
            if (!isset($tableMap[$sheetName])) {
                continue;
            }

            $table = $tableMap[$sheetName];
            $rows = $xpath->query('.//*[local-name()="Row"]', $worksheetNode);
            if (!$rows || $rows->length === 0) {
                continue;
            }

            $headers = [];
            $dataRows = [];

            foreach ($rows as $rowIndex => $rowNode) {
                $cells = $xpath->query('./*[local-name()="Cell"]', $rowNode);
                $values = [];
                foreach ($cells as $cellNode) {
                    $dataNode = $xpath->query('.//*[local-name()="Data"]', $cellNode)->item(0);
                    $values[] = $dataNode ? trim($dataNode->textContent) : '';
                }

                if ($rowIndex === 0) {
                    $headers = $values;
                    continue;
                }

                if (!empty(array_filter($values, static fn(string $v) => $v !== ''))) {
                    $dataRows[] = $values;
                }
            }

            if (empty($headers)) {
                continue;
            }

            foreach ($dataRows as $values) {
                $data = [];
                foreach ($headers as $idx => $header) {
                    if ($header === '') {
                        continue;
                    }
                    $raw = $values[$idx] ?? null;
                    $data[$header] = ($raw === '' || $raw === null) ? null : $raw;
                }

                if (!empty($data)) {
                    $db->insert($table, $data);
                    $insertCount++;
                }
            }

            $importedTables[] = $table;
        }

        return [
            'tables' => array_values(array_unique($importedTables)),
            'rows'   => $insertCount,
        ];
    }

    private static function stripUtf8Bom(string $content): string
    {
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            return substr($content, 3);
        }
        return $content;
    }

    private static function appendInsertBlock(array &$lines, string $table, array $columns, array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $quotedColumns = implode(', ', array_map(static fn(string $column) => "`{$column}`", $columns));
        $lines[] = "INSERT INTO `{$table}` ({$quotedColumns}) VALUES";

        $lastIndex = count($rows) - 1;
        foreach ($rows as $index => $row) {
            $suffix = $index === $lastIndex ? ';' : ',';
            $lines[] = '    (' . implode(', ', $row) . ')' . $suffix;
        }

        $lines[] = '';
    }

    private static function defaultLevelsDefinition(): array
    {
        return [
            ['level_name' => 'الفرقة الأولى', 'level_code' => 'L1', 'sort_order' => 1, 'is_active' => 1],
            ['level_name' => 'الفرقة الثانية', 'level_code' => 'L2', 'sort_order' => 2, 'is_active' => 1],
            ['level_name' => 'الفرقة الثالثة', 'level_code' => 'L3', 'sort_order' => 3, 'is_active' => 1],
            ['level_name' => 'الفرقة الرابعة', 'level_code' => 'L4', 'sort_order' => 4, 'is_active' => 1],
        ];
    }

    private static function sampleDepartments(): array
    {
        return [
            [
                'name' => 'قسم الإعلام التربوي',
                'code' => 'MEDIA',
                'slug' => 'media',
                'member_suffix' => 'الإعلامي',
                'description' => 'بيانات تجريبية لقسم الإعلام التربوي مع مسارات الصحافة والإذاعة.',
                'branches' => [
                    'L3' => ['شعبة صحافة', 'شعبة إذاعة'],
                    'L4' => ['شعبة صحافة', 'شعبة إذاعة'],
                ],
                'subjects' => [
                    'L1' => [
                        ['name' => 'مدخل إلى الإعلام التربوي', 'code' => 'MED101', 'type' => 'نظري', 'division' => 'عامة', 'member_group' => 'faculty', 'member_index' => 2, 'scheduled' => true],
                        ['name' => 'مهارات الكتابة الإعلامية', 'code' => 'MED102', 'type' => 'نظري', 'division' => 'عامة', 'member_group' => 'faculty', 'member_index' => 3],
                        ['name' => 'تطبيقات الحاسب في الإعلام', 'code' => 'MED103', 'type' => 'عملي', 'division' => 'عامة', 'section' => 'سكشن 1', 'member_group' => 'assistant', 'member_index' => 1],
                        ['name' => 'ورشة إنتاج وسائط تعليمية', 'code' => 'MED104', 'type' => 'عملي', 'division' => 'عامة', 'section' => 'سكشن 2', 'member_group' => 'assistant', 'member_index' => 2],
                    ],
                    'L2' => [
                        ['name' => 'التحرير الإعلامي التربوي', 'code' => 'MED201', 'type' => 'نظري', 'division' => 'عامة', 'member_group' => 'faculty', 'member_index' => 4, 'scheduled' => true],
                        ['name' => 'تصميم الرسائل الإعلامية', 'code' => 'MED202', 'type' => 'نظري', 'division' => 'عامة', 'member_group' => 'faculty', 'member_index' => 5],
                        ['name' => 'التصوير والمونتاج التعليمي', 'code' => 'MED203', 'type' => 'عملي', 'division' => 'عامة', 'section' => 'سكشن 1', 'member_group' => 'assistant', 'member_index' => 3],
                        ['name' => 'مهارات الأداء الصوتي', 'code' => 'MED204', 'type' => 'عملي', 'division' => 'عامة', 'section' => 'سكشن 2', 'member_group' => 'assistant', 'member_index' => 4],
                    ],
                    'L3' => [
                        ['name' => 'التحرير الصحفي المتخصص', 'code' => 'MED301J', 'type' => 'نظري', 'division' => 'شعبة صحافة', 'member_group' => 'faculty', 'member_index' => 6, 'scheduled' => true],
                        ['name' => 'الإخراج الصحفي', 'code' => 'MED302J', 'type' => 'عملي', 'division' => 'شعبة صحافة', 'section' => 'سكشن 1', 'member_group' => 'assistant', 'member_index' => 5],
                        ['name' => 'التقديم الإذاعي', 'code' => 'MED301R', 'type' => 'نظري', 'division' => 'شعبة إذاعة', 'member_group' => 'faculty', 'member_index' => 7, 'scheduled' => true],
                        ['name' => 'الإنتاج البرامجي الإذاعي', 'code' => 'MED302R', 'type' => 'عملي', 'division' => 'شعبة إذاعة', 'section' => 'سكشن 1', 'member_group' => 'assistant', 'member_index' => 6],
                    ],
                    'L4' => [
                        ['name' => 'صحافة رقمية متقدمة', 'code' => 'MED401J', 'type' => 'نظري', 'division' => 'شعبة صحافة', 'member_group' => 'faculty', 'member_index' => 8, 'scheduled' => true],
                        ['name' => 'مشروع الصحافة التربوية', 'code' => 'MED402J', 'type' => 'عملي', 'division' => 'شعبة صحافة', 'section' => 'سكشن 2', 'member_group' => 'assistant', 'member_index' => 1],
                        ['name' => 'إعداد البرامج التعليمية', 'code' => 'MED401R', 'type' => 'نظري', 'division' => 'شعبة إذاعة', 'member_group' => 'faculty', 'member_index' => 9, 'scheduled' => true],
                        ['name' => 'استوديو إذاعي متقدم', 'code' => 'MED402R', 'type' => 'عملي', 'division' => 'شعبة إذاعة', 'section' => 'سكشن 2', 'member_group' => 'assistant', 'member_index' => 2],
                    ],
                ],
            ],
            [
                'name' => 'قسم تكنولوجيا التعليم',
                'code' => 'EDTECH',
                'slug' => 'edtech',
                'member_suffix' => 'التقني',
                'description' => 'بيانات تجريبية لقسم تكنولوجيا التعليم مع مساري معلم الحاسب وأخصائي تكنولوجيا التعليم.',
                'branches' => [
                    'L3' => ['شعبة معلم حاسب آلي', 'شعبة أخصائي تكنولوجيا التعليم'],
                    'L4' => ['شعبة معلم حاسب آلي', 'شعبة أخصائي تكنولوجيا التعليم'],
                ],
                'subjects' => [
                    'L1' => [
                        ['name' => 'مدخل إلى تكنولوجيا التعليم', 'code' => 'TEC101', 'type' => 'نظري', 'division' => 'عامة', 'member_group' => 'faculty', 'member_index' => 2, 'scheduled' => true],
                        ['name' => 'مهارات الحاسب التعليمية', 'code' => 'TEC102', 'type' => 'نظري', 'division' => 'عامة', 'member_group' => 'faculty', 'member_index' => 3],
                        ['name' => 'تطبيقات العروض التفاعلية', 'code' => 'TEC103', 'type' => 'عملي', 'division' => 'عامة', 'section' => 'سكشن 1', 'member_group' => 'assistant', 'member_index' => 1],
                        ['name' => 'تصميم موارد رقمية', 'code' => 'TEC104', 'type' => 'عملي', 'division' => 'عامة', 'section' => 'سكشن 2', 'member_group' => 'assistant', 'member_index' => 2],
                    ],
                    'L2' => [
                        ['name' => 'تصميم الوسائط المتعددة', 'code' => 'TEC201', 'type' => 'نظري', 'division' => 'عامة', 'member_group' => 'faculty', 'member_index' => 4, 'scheduled' => true],
                        ['name' => 'برمجة تعليمية مبسطة', 'code' => 'TEC202', 'type' => 'نظري', 'division' => 'عامة', 'member_group' => 'faculty', 'member_index' => 5],
                        ['name' => 'إدارة معامل الحاسب', 'code' => 'TEC203', 'type' => 'عملي', 'division' => 'عامة', 'section' => 'سكشن 1', 'member_group' => 'assistant', 'member_index' => 3],
                        ['name' => 'إنتاج الفيديو التعليمي', 'code' => 'TEC204', 'type' => 'عملي', 'division' => 'عامة', 'section' => 'سكشن 2', 'member_group' => 'assistant', 'member_index' => 4],
                    ],
                    'L3' => [
                        ['name' => 'مناهج تدريس الحاسب', 'code' => 'TEC301T', 'type' => 'نظري', 'division' => 'شعبة معلم حاسب آلي', 'member_group' => 'faculty', 'member_index' => 6, 'scheduled' => true],
                        ['name' => 'معمل تدريس الحاسب 1', 'code' => 'TEC302T', 'type' => 'عملي', 'division' => 'شعبة معلم حاسب آلي', 'section' => 'سكشن 1', 'member_group' => 'assistant', 'member_index' => 5],
                        ['name' => 'تصميم بيئات تعلم رقمية', 'code' => 'TEC301S', 'type' => 'نظري', 'division' => 'شعبة أخصائي تكنولوجيا التعليم', 'member_group' => 'faculty', 'member_index' => 7, 'scheduled' => true],
                        ['name' => 'تطوير وسائط تعليمية 1', 'code' => 'TEC302S', 'type' => 'عملي', 'division' => 'شعبة أخصائي تكنولوجيا التعليم', 'section' => 'سكشن 1', 'member_group' => 'assistant', 'member_index' => 6],
                    ],
                    'L4' => [
                        ['name' => 'استراتيجيات تدريس الحاسب', 'code' => 'TEC401T', 'type' => 'نظري', 'division' => 'شعبة معلم حاسب آلي', 'member_group' => 'faculty', 'member_index' => 8, 'scheduled' => true],
                        ['name' => 'تدريب ميداني معلم حاسب', 'code' => 'TEC402T', 'type' => 'عملي', 'division' => 'شعبة معلم حاسب آلي', 'section' => 'سكشن 2', 'member_group' => 'assistant', 'member_index' => 1],
                        ['name' => 'إدارة مراكز مصادر التعلم', 'code' => 'TEC401S', 'type' => 'نظري', 'division' => 'شعبة أخصائي تكنولوجيا التعليم', 'member_group' => 'faculty', 'member_index' => 9, 'scheduled' => true],
                        ['name' => 'مشروع تكنولوجيا التعليم', 'code' => 'TEC402S', 'type' => 'عملي', 'division' => 'شعبة أخصائي تكنولوجيا التعليم', 'section' => 'سكشن 2', 'member_group' => 'assistant', 'member_index' => 2],
                    ],
                ],
            ],
            [
                'name' => 'قسم الاقتصاد المنزلي',
                'code' => 'HOMEEC',
                'slug' => 'homeec',
                'member_suffix' => 'المنزلي',
                'description' => 'بيانات تجريبية لقسم الاقتصاد المنزلي مع مساري الأغذية والملابس.',
                'branches' => [
                    'L3' => ['شعبة الأغذية', 'شعبة الملابس'],
                    'L4' => ['شعبة الأغذية', 'شعبة الملابس'],
                ],
                'subjects' => [
                    'L1' => [
                        ['name' => 'مدخل إلى الاقتصاد المنزلي', 'code' => 'HOM101', 'type' => 'نظري', 'division' => 'عامة', 'member_group' => 'faculty', 'member_index' => 2, 'scheduled' => true],
                        ['name' => 'أسس التغذية', 'code' => 'HOM102', 'type' => 'نظري', 'division' => 'عامة', 'member_group' => 'faculty', 'member_index' => 3],
                        ['name' => 'مهارات المطبخ التعليمي', 'code' => 'HOM103', 'type' => 'عملي', 'division' => 'عامة', 'section' => 'سكشن 1', 'member_group' => 'assistant', 'member_index' => 1],
                        ['name' => 'مبادئ الحياكة', 'code' => 'HOM104', 'type' => 'عملي', 'division' => 'عامة', 'section' => 'سكشن 2', 'member_group' => 'assistant', 'member_index' => 2],
                    ],
                    'L2' => [
                        ['name' => 'كيمياء الأغذية', 'code' => 'HOM201', 'type' => 'نظري', 'division' => 'عامة', 'member_group' => 'faculty', 'member_index' => 4, 'scheduled' => true],
                        ['name' => 'تصميم الملابس', 'code' => 'HOM202', 'type' => 'نظري', 'division' => 'عامة', 'member_group' => 'faculty', 'member_index' => 5],
                        ['name' => 'حفظ الأغذية', 'code' => 'HOM203', 'type' => 'عملي', 'division' => 'عامة', 'section' => 'سكشن 1', 'member_group' => 'assistant', 'member_index' => 3],
                        ['name' => 'تقنيات التفصيل', 'code' => 'HOM204', 'type' => 'عملي', 'division' => 'عامة', 'section' => 'سكشن 2', 'member_group' => 'assistant', 'member_index' => 4],
                    ],
                    'L3' => [
                        ['name' => 'تغذية علاجية أساسية', 'code' => 'HOM301F', 'type' => 'نظري', 'division' => 'شعبة الأغذية', 'member_group' => 'faculty', 'member_index' => 6, 'scheduled' => true],
                        ['name' => 'معمل أغذية 1', 'code' => 'HOM302F', 'type' => 'عملي', 'division' => 'شعبة الأغذية', 'section' => 'سكشن 1', 'member_group' => 'assistant', 'member_index' => 5],
                        ['name' => 'تصميم أزياء تربوي', 'code' => 'HOM301C', 'type' => 'نظري', 'division' => 'شعبة الملابس', 'member_group' => 'faculty', 'member_index' => 7, 'scheduled' => true],
                        ['name' => 'معمل ملابس 1', 'code' => 'HOM302C', 'type' => 'عملي', 'division' => 'شعبة الملابس', 'section' => 'سكشن 1', 'member_group' => 'assistant', 'member_index' => 6],
                    ],
                    'L4' => [
                        ['name' => 'تخطيط وجبات ومؤسسات', 'code' => 'HOM401F', 'type' => 'نظري', 'division' => 'شعبة الأغذية', 'member_group' => 'faculty', 'member_index' => 8, 'scheduled' => true],
                        ['name' => 'مشروع صناعات غذائية', 'code' => 'HOM402F', 'type' => 'عملي', 'division' => 'شعبة الأغذية', 'section' => 'سكشن 2', 'member_group' => 'assistant', 'member_index' => 1],
                        ['name' => 'إدارة مشاغل الملابس', 'code' => 'HOM401C', 'type' => 'نظري', 'division' => 'شعبة الملابس', 'member_group' => 'faculty', 'member_index' => 9, 'scheduled' => true],
                        ['name' => 'مشروع تصميم وتنفيذ', 'code' => 'HOM402C', 'type' => 'عملي', 'division' => 'شعبة الملابس', 'section' => 'سكشن 2', 'member_group' => 'assistant', 'member_index' => 2],
                    ],
                ],
            ],
            [
                'name' => 'قسم الموسيقى',
                'code' => 'MUSIC',
                'slug' => 'music',
                'member_suffix' => 'الموسيقي',
                'description' => 'بيانات تجريبية لقسم الموسيقى مع مساري الغناء والعزف.',
                'branches' => [
                    'L3' => ['شعبة غناء', 'شعبة عزف'],
                    'L4' => ['شعبة غناء', 'شعبة عزف'],
                ],
                'subjects' => [
                    'L1' => [
                        ['name' => 'مبادئ الموسيقى', 'code' => 'MUS101', 'type' => 'نظري', 'division' => 'عامة', 'member_group' => 'faculty', 'member_index' => 2, 'scheduled' => true],
                        ['name' => 'صولفيج 1', 'code' => 'MUS102', 'type' => 'نظري', 'division' => 'عامة', 'member_group' => 'faculty', 'member_index' => 3],
                        ['name' => 'تدريب سمعي', 'code' => 'MUS103', 'type' => 'عملي', 'division' => 'عامة', 'section' => 'سكشن 1', 'member_group' => 'assistant', 'member_index' => 1],
                        ['name' => 'بيانو أساسي', 'code' => 'MUS104', 'type' => 'عملي', 'division' => 'عامة', 'section' => 'سكشن 2', 'member_group' => 'assistant', 'member_index' => 2],
                    ],
                    'L2' => [
                        ['name' => 'تاريخ الموسيقى', 'code' => 'MUS201', 'type' => 'نظري', 'division' => 'عامة', 'member_group' => 'faculty', 'member_index' => 4, 'scheduled' => true],
                        ['name' => 'صولفيج 2', 'code' => 'MUS202', 'type' => 'نظري', 'division' => 'عامة', 'member_group' => 'faculty', 'member_index' => 5],
                        ['name' => 'كورال', 'code' => 'MUS203', 'type' => 'عملي', 'division' => 'عامة', 'section' => 'سكشن 1', 'member_group' => 'assistant', 'member_index' => 3],
                        ['name' => 'آلة تخصصية 1', 'code' => 'MUS204', 'type' => 'عملي', 'division' => 'عامة', 'section' => 'سكشن 2', 'member_group' => 'assistant', 'member_index' => 4],
                    ],
                    'L3' => [
                        ['name' => 'أداء غنائي تربوي', 'code' => 'MUS301V', 'type' => 'نظري', 'division' => 'شعبة غناء', 'member_group' => 'faculty', 'member_index' => 6, 'scheduled' => true],
                        ['name' => 'ورشة غناء 1', 'code' => 'MUS302V', 'type' => 'عملي', 'division' => 'شعبة غناء', 'section' => 'سكشن 1', 'member_group' => 'assistant', 'member_index' => 5],
                        ['name' => 'تحليل مؤلفات موسيقية', 'code' => 'MUS301I', 'type' => 'نظري', 'division' => 'شعبة عزف', 'member_group' => 'faculty', 'member_index' => 7, 'scheduled' => true],
                        ['name' => 'ورشة عزف 1', 'code' => 'MUS302I', 'type' => 'عملي', 'division' => 'شعبة عزف', 'section' => 'سكشن 1', 'member_group' => 'assistant', 'member_index' => 6],
                    ],
                    'L4' => [
                        ['name' => 'قيادة فرق غنائية', 'code' => 'MUS401V', 'type' => 'نظري', 'division' => 'شعبة غناء', 'member_group' => 'faculty', 'member_index' => 8, 'scheduled' => true],
                        ['name' => 'مشروع أداء صوتي', 'code' => 'MUS402V', 'type' => 'عملي', 'division' => 'شعبة غناء', 'section' => 'سكشن 2', 'member_group' => 'assistant', 'member_index' => 1],
                        ['name' => 'إخراج حفل موسيقي', 'code' => 'MUS401I', 'type' => 'نظري', 'division' => 'شعبة عزف', 'member_group' => 'faculty', 'member_index' => 9, 'scheduled' => true],
                        ['name' => 'مشروع عزف جماعي', 'code' => 'MUS402I', 'type' => 'عملي', 'division' => 'شعبة عزف', 'section' => 'سكشن 2', 'member_group' => 'assistant', 'member_index' => 2],
                    ],
                ],
            ],
        ];
    }

    private static function sampleClassrooms(): array
    {
        return [
            ['classroom_name' => 'مدرج التربية 1', 'capacity' => 180, 'classroom_type' => 'مدرج', 'building' => 'مبنى التربية', 'floor' => 'الأرضي'],
            ['classroom_name' => 'مدرج التربية 2', 'capacity' => 160, 'classroom_type' => 'مدرج', 'building' => 'مبنى التربية', 'floor' => 'الأرضي'],
            ['classroom_name' => 'قاعة تربوي 101', 'capacity' => 80, 'classroom_type' => 'قاعة', 'building' => 'مبنى التربية', 'floor' => '1'],
            ['classroom_name' => 'قاعة تربوي 102', 'capacity' => 80, 'classroom_type' => 'قاعة', 'building' => 'مبنى التربية', 'floor' => '1'],
            ['classroom_name' => 'قاعة تربوي 201', 'capacity' => 70, 'classroom_type' => 'قاعة', 'building' => 'مبنى التربية', 'floor' => '2'],
            ['classroom_name' => 'قاعة تربوي 202', 'capacity' => 70, 'classroom_type' => 'قاعة', 'building' => 'مبنى التربية', 'floor' => '2'],
            ['classroom_name' => 'قاعة تربوي 203', 'capacity' => 60, 'classroom_type' => 'قاعة', 'building' => 'مبنى التربية', 'floor' => '2'],
            ['classroom_name' => 'قاعة تربوي 204', 'capacity' => 60, 'classroom_type' => 'قاعة', 'building' => 'مبنى التربية', 'floor' => '2'],
            ['classroom_name' => 'معمل حاسب 1', 'capacity' => 35, 'classroom_type' => 'معمل', 'building' => 'مبنى الحاسب', 'floor' => '1'],
            ['classroom_name' => 'معمل حاسب 2', 'capacity' => 35, 'classroom_type' => 'معمل', 'building' => 'مبنى الحاسب', 'floor' => '1'],
            ['classroom_name' => 'معمل تكنولوجيا 1', 'capacity' => 30, 'classroom_type' => 'معمل', 'building' => 'مبنى الوسائط', 'floor' => '1'],
            ['classroom_name' => 'معمل أغذية 1', 'capacity' => 28, 'classroom_type' => 'معمل', 'building' => 'مبنى الاقتصاد المنزلي', 'floor' => '1'],
            ['classroom_name' => 'معمل ملابس 1', 'capacity' => 28, 'classroom_type' => 'معمل', 'building' => 'مبنى الاقتصاد المنزلي', 'floor' => '2'],
            ['classroom_name' => 'استوديو إذاعي', 'capacity' => 25, 'classroom_type' => 'قاعة', 'building' => 'مبنى الإعلام', 'floor' => '1'],
            ['classroom_name' => 'قاعة موسيقى 1', 'capacity' => 30, 'classroom_type' => 'قاعة', 'building' => 'مبنى الموسيقى', 'floor' => '1'],
            ['classroom_name' => 'قاعة عزف 1', 'capacity' => 26, 'classroom_type' => 'قاعة', 'building' => 'مبنى الموسيقى', 'floor' => '2'],
        ];
    }

    private static function sampleLectureRoomNames(): array
    {
        return [
            'مدرج التربية 1',
            'مدرج التربية 2',
            'قاعة تربوي 101',
            'قاعة تربوي 102',
            'قاعة تربوي 201',
            'قاعة تربوي 202',
            'قاعة تربوي 203',
            'قاعة تربوي 204',
        ];
    }

    private static function sampleSessions(): array
    {
        $days = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'];
        $templates = [
            ['session_name' => 'الفترة الأولى', 'start_time' => '08:00:00', 'end_time' => '10:00:00', 'duration' => 120],
            ['session_name' => 'الفترة الثانية', 'start_time' => '10:15:00', 'end_time' => '12:15:00', 'duration' => 120],
            ['session_name' => 'الفترة الثالثة', 'start_time' => '12:30:00', 'end_time' => '14:30:00', 'duration' => 120],
            ['session_name' => 'الفترة الرابعة', 'start_time' => '14:45:00', 'end_time' => '16:45:00', 'duration' => 120],
        ];

        $rows = [];
        foreach ($days as $day) {
            foreach ($templates as $template) {
                $rows[] = array_merge(['day' => $day], $template);
            }
        }

        return $rows;
    }

    private static function sqlString(string $value): string
    {
        return "'" . str_replace("'", "''", $value) . "'";
    }

    private static function departmentIdExpr(string $departmentCode): string
    {
        return '(SELECT department_id FROM departments WHERE department_code = ' . self::sqlString($departmentCode) . ' LIMIT 1)';
    }

    private static function levelIdExpr(string $levelCode): string
    {
        return '(SELECT level_id FROM levels WHERE level_code = ' . self::sqlString($levelCode) . ' LIMIT 1)';
    }

    private static function divisionIdExpr(string $departmentCode, string $levelCode, string $divisionName): string
    {
        return '(SELECT division_id FROM divisions WHERE department_id = ' . self::departmentIdExpr($departmentCode)
            . ' AND level_id = ' . self::levelIdExpr($levelCode)
            . ' AND division_name = ' . self::sqlString($divisionName)
            . ' LIMIT 1)';
    }

    private static function sectionIdExpr(string $departmentCode, string $levelCode, string $divisionName, string $sectionName): string
    {
        return '(SELECT section_id FROM sections WHERE division_id = ' . self::divisionIdExpr($departmentCode, $levelCode, $divisionName)
            . ' AND section_name = ' . self::sqlString($sectionName)
            . ' LIMIT 1)';
    }

    private static function subjectIdExpr(string $subjectCode): string
    {
        return '(SELECT subject_id FROM subjects WHERE subject_code = ' . self::sqlString($subjectCode) . ' LIMIT 1)';
    }

    private static function memberIdExpr(string $email): string
    {
        return '(SELECT member_id FROM faculty_members WHERE email = ' . self::sqlString($email) . ' LIMIT 1)';
    }

    private static function memberCourseIdExpr(string $subjectCode, string $email, string $assignmentType): string
    {
        return '(SELECT mc.member_course_id FROM member_courses mc '
            . 'JOIN subjects s ON s.subject_id = mc.subject_id '
            . 'JOIN faculty_members fm ON fm.member_id = mc.member_id '
            . 'WHERE s.subject_code = ' . self::sqlString($subjectCode)
            . ' AND fm.email = ' . self::sqlString($email)
            . ' AND mc.assignment_type = ' . self::sqlString($assignmentType)
            . ' LIMIT 1)';
    }

    private static function roleIdExpr(string $roleSlug): string
    {
        return '(SELECT id FROM roles WHERE role_slug = ' . self::sqlString($roleSlug) . ' LIMIT 1)';
    }

    private static function academicYearIdExpr(string $yearName): string
    {
        return '(SELECT id FROM academic_years WHERE year_name = ' . self::sqlString($yearName) . ' LIMIT 1)';
    }

    private static function semesterIdExpr(string $semesterName): string
    {
        return '(SELECT id FROM semesters WHERE semester_name = ' . self::sqlString($semesterName) . ' LIMIT 1)';
    }

    private static function classroomIdExpr(string $classroomName): string
    {
        return '(SELECT classroom_id FROM classrooms WHERE classroom_name = ' . self::sqlString($classroomName) . ' LIMIT 1)';
    }

    private static function sessionIdExpr(string $day, string $sessionName): string
    {
        return '(SELECT session_id FROM sessions WHERE day = ' . self::sqlString($day)
            . ' AND session_name = ' . self::sqlString($sessionName)
            . ' LIMIT 1)';
    }

    private static function adminUserIdExpr(): string
    {
        return '(SELECT id FROM users WHERE role_id = (SELECT id FROM roles WHERE role_slug = ' . self::sqlString('admin') . ' LIMIT 1) ORDER BY id LIMIT 1)';
    }

    private static function memberEmail(string $departmentSlug, string $group, int $index): string
    {
        $suffix = $group === 'assistant' ? 'asst' : 'fac';
        return sprintf('%s.%s%02d@example.test', $departmentSlug, $suffix, $index);
    }

    private static function tableExists(\Database $db, string $table): bool
    {
        return $db->fetch("SHOW TABLES LIKE ?", [$table]) !== null;
    }

    private static function runDefaultSeeds(\Database $db): void
    {
        $seedDir = APP_ROOT . '/database/seeds';
        $seedFiles = glob($seedDir . '/*.php') ?: [];
        sort($seedFiles);

        foreach ($seedFiles as $file) {
            $seed = require $file;
            if (is_callable($seed)) {
                $seed($db);
            }
        }
    }

    private static function seedDefaultLevels(\Database $db): void
    {
        $defaults = self::defaultLevelsDefinition();

        foreach ($defaults as $row) {
            $exists = $db->fetch('SELECT level_id FROM levels WHERE level_name = ? LIMIT 1', [$row['level_name']]);
            if ($exists) {
                continue;
            }

            $db->insert('levels', $row);
        }
    }

    private static function deleteNonAdminUsers(\Database $db): void
    {
        if (!self::tableExists($db, 'users') || !self::tableExists($db, 'roles')) {
            return;
        }

        $db->raw(
            "DELETE FROM `users`
             WHERE `role_id` IS NULL
                OR `role_id` <> (SELECT `id` FROM `roles` WHERE `role_slug` = 'admin' LIMIT 1)"
        );
    }

    private static function deleteNonAdminFacultyMembers(\Database $db): void
    {
        if (!self::tableExists($db, 'faculty_members') || !self::tableExists($db, 'users') || !self::tableExists($db, 'roles')) {
            return;
        }

        $db->raw(
            "DELETE fm FROM `faculty_members` fm
             LEFT JOIN `users` u
               ON u.`member_id` = fm.`member_id`
              AND u.`role_id` = (SELECT `id` FROM `roles` WHERE `role_slug` = 'admin' LIMIT 1)
             WHERE u.`id` IS NULL"
        );
    }

    private static function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}

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
        'faculty_members',
        'users',
        'subjects',
        'sections',
        'classrooms',
        'sessions',
        'member_courses',
        'timetable',
        'settings',
        'notifications',
        'api_tokens',
        'audit_logs',
        'migrations',
    ];

    public static function tables(): array
    {
        return self::TABLES;
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

    private static function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}

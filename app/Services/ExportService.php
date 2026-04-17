<?php
namespace App\Services;

class ExportService
{
    /**
     * Export timetable data as CSV.
     */
    public static function timetableCsv(array $entries, string $filename = 'timetable.csv'): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // UTF-8 BOM for Excel
        echo "\xEF\xBB\xBF";

        $out = fopen('php://output', 'w');

        // Header row
        fputcsv($out, ['اليوم', 'الفترة', 'وقت البداية', 'وقت النهاية', 'المقرر', 'الشعبة', 'القاعة', 'المدرس', 'القسم', 'المستوى']);

        foreach ($entries as $entry) {
            fputcsv($out, [
                $entry['day'],
                $entry['session_name'],
                $entry['start_time'],
                $entry['end_time'],
                $entry['subject_name'],
                $entry['section_name'],
                $entry['classroom_name'],
                $entry['member_name'],
                $entry['department_name'],
                $entry['level_name'],
            ]);
        }

        fclose($out);
        exit();
    }

    /**
     * Export timetable as Excel-compatible XML (SpreadsheetML).
     * Works in MS Excel, LibreOffice, Google Sheets without any PHP library.
     */
    public static function timetableExcel(array $entries, string $filename = 'timetable.xls'): void
    {
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
                xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";
        echo '<Styles>
            <Style ss:ID="header">
                <Font ss:Bold="1" ss:Size="12" ss:FontName="Tahoma"/>
                <Interior ss:Color="#2c3e50" ss:Pattern="Solid"/>
                <Font ss:Color="#FFFFFF" ss:Bold="1"/>
                <Alignment ss:Horizontal="Center"/>
            </Style>
            <Style ss:ID="cell">
                <Font ss:Size="11" ss:FontName="Tahoma"/>
                <Alignment ss:Horizontal="Center"/>
                <Borders>
                    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                </Borders>
            </Style>
        </Styles>' . "\n";
        echo '<Worksheet ss:Name="الجدول الدراسي"><Table>' . "\n";

        // Header row
        $headers = ['اليوم', 'الفترة', 'وقت البداية', 'وقت النهاية', 'المقرر', 'الشعبة', 'القاعة', 'المدرس', 'القسم', 'المستوى'];
        echo '<Row>';
        foreach ($headers as $h) {
            echo '<Cell ss:StyleID="header"><Data ss:Type="String">' . htmlspecialchars($h) . '</Data></Cell>';
        }
        echo '</Row>' . "\n";

        // Data rows
        foreach ($entries as $entry) {
            echo '<Row>';
            $cells = [
                $entry['day'], $entry['session_name'], $entry['start_time'], $entry['end_time'],
                $entry['subject_name'], $entry['section_name'], $entry['classroom_name'],
                $entry['member_name'], $entry['department_name'], $entry['level_name'],
            ];
            foreach ($cells as $c) {
                echo '<Cell ss:StyleID="cell"><Data ss:Type="String">' . htmlspecialchars($c ?? '') . '</Data></Cell>';
            }
            echo '</Row>' . "\n";
        }

        echo '</Table></Worksheet></Workbook>';
        exit();
    }

    /**
     * Export timetable as a PDF-ready HTML page (opened in new tab with auto-print).
     * Uses CSS @page for proper PDF output when user prints/saves as PDF.
     */
    public static function timetablePdf(array $pivotData, string $departmentName, string $levelName, string $institutionName = ''): void
    {
        $days = $pivotData['days'] ?? [];
        $sections = $pivotData['sections'] ?? [];
        $pivot = $pivotData['pivot'] ?? [];

        // Collect unique sessions
        $sessions = [];
        foreach ($pivotData['entries'] ?? [] as $e) {
            $key = $e['session_name'];
            if (!isset($sessions[$key])) {
                $sessions[$key] = ['name' => $e['session_name'], 'start' => $e['start_time'], 'end' => $e['end_time']];
            }
        }

        // Color palette for subjects
        $colors = ['#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c', '#e67e22', '#34495e', '#16a085', '#c0392b'];
        $subjectColors = [];
        $colorIdx = 0;

        header('Content-Type: text/html; charset=utf-8');
        ?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>الجدول الدراسي - <?= htmlspecialchars($departmentName) ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none !important; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; }
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, 'Tajawal', Arial, sans-serif;
            direction: rtl;
            padding: 15px;
            background: #fff;
            color: #333;
            font-size: 12px;
        }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 3px double #2c3e50; padding-bottom: 10px; }
        .header h2 { margin: 0; color: #2c3e50; font-size: 18px; }
        .header h3 { margin: 5px 0; color: #555; font-size: 14px; font-weight: normal; }
        .header .meta { font-size: 11px; color: #888; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #2c3e50; color: #fff; padding: 8px 4px; font-size: 11px; text-align: center; }
        td { border: 1px solid #ddd; padding: 5px 3px; text-align: center; vertical-align: top; font-size: 10px; }
        .day-cell { background: #f1f3f5; font-weight: bold; font-size: 12px; writing-mode: vertical-rl; text-orientation: mixed; min-width: 30px; }
        .time-cell { background: #f8f9fa; font-size: 10px; white-space: nowrap; }
        .entry { padding: 3px; border-radius: 4px; margin: 1px; line-height: 1.4; }
        .entry .subj { font-weight: bold; font-size: 11px; color: #fff; }
        .entry .info { font-size: 9px; color: rgba(255,255,255,0.85); }
        .empty-cell { background: #fafafa; }
        .footer { text-align: center; margin-top: 15px; font-size: 10px; color: #aaa; border-top: 1px solid #ddd; padding-top: 5px; }
        .toolbar { text-align: center; margin-bottom: 15px; }
        .toolbar button { padding: 8px 20px; margin: 0 5px; cursor: pointer; border: none; border-radius: 5px; font-size: 13px; font-family: inherit; }
        .btn-print { background: #2c3e50; color: #fff; }
        .btn-close { background: #e74c3c; color: #fff; }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <button class="btn-print" onclick="window.print()">🖨️ طباعة / حفظ PDF</button>
        <button class="btn-close" onclick="window.close()">✖ إغلاق</button>
    </div>

    <div class="header">
        <?php if ($institutionName): ?>
        <h2><?= htmlspecialchars($institutionName) ?></h2>
        <?php endif; ?>
        <h2>الجدول الدراسي</h2>
        <h3><?= htmlspecialchars($departmentName) ?> — <?= htmlspecialchars($levelName) ?></h3>
        <div class="meta">تاريخ الطباعة: <?= date('Y-m-d H:i') ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:30px">اليوم</th>
                <th style="width:70px">الفترة</th>
                <?php foreach ($sections as $sec): ?>
                <th><?= htmlspecialchars($sec) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($days as $day): ?>
            <?php $first = true; ?>
            <?php foreach ($sessions as $sess): ?>
                <?php $key = $day . '|' . $sess['name']; ?>
                <tr>
                    <?php if ($first): ?>
                    <td class="day-cell" rowspan="<?= count($sessions) ?>"><?= htmlspecialchars($day) ?></td>
                    <?php $first = false; endif; ?>
                    <td class="time-cell">
                        <?= htmlspecialchars($sess['name']) ?><br>
                        <small><?= $sess['start'] ?>-<?= $sess['end'] ?></small>
                    </td>
                    <?php foreach ($sections as $sec): ?>
                    <td class="<?= isset($pivot[$key][$sec]) ? '' : 'empty-cell' ?>">
                        <?php if (isset($pivot[$key][$sec])):
                            $e = $pivot[$key][$sec];
                            $sn = $e['subject_name'] ?? '';
                            if (!isset($subjectColors[$sn])) {
                                $subjectColors[$sn] = $colors[$colorIdx % count($colors)];
                                $colorIdx++;
                            }
                            $bg = $subjectColors[$sn];
                        ?>
                        <div class="entry" style="background:<?= $bg ?>">
                            <div class="subj"><?= htmlspecialchars($sn) ?></div>
                            <div class="info"><?= htmlspecialchars($e['member_name'] ?? '') ?></div>
                            <div class="info"><?= htmlspecialchars($e['classroom_name'] ?? '') ?></div>
                        </div>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        نظام إدارة الجداول الدراسية — <?= date('Y') ?>
    </div>

    <script>
        // Auto-trigger print dialog
        window.addEventListener('load', function() {
            // Small delay to ensure styles are rendered
            setTimeout(function() { /* User clicks print button */ }, 500);
        });
    </script>
</body>
</html>
        <?php
        exit();
    }

    /**
     * Export generic data table as CSV.
     */
    public static function genericCsv(array $headers, array $rows, string $filename): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF";

        $out = fopen('php://output', 'w');
        fputcsv($out, $headers);
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        fclose($out);
        exit();
    }

    /**
     * Export timetable as printable HTML page (simple version).
     */
    public static function timetableHtml(array $pivotData, string $departmentName, string $levelName): void
    {
        $days = $pivotData['days'];
        $sections = $pivotData['sections'];
        $pivot = $pivotData['pivot'];

        // Get unique sessions
        $sessions = [];
        foreach ($pivotData['entries'] as $e) {
            $key = $e['session_name'];
            if (!isset($sessions[$key])) {
                $sessions[$key] = ['name' => $e['session_name'], 'start' => $e['start_time'], 'end' => $e['end_time']];
            }
        }

        header('Content-Type: text/html; charset=utf-8');
        ?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>الجدول الدراسي - <?= htmlspecialchars($departmentName) ?> - <?= htmlspecialchars($levelName) ?></title>
    <style>
        @media print { body { margin: 0; } .no-print { display: none; } }
        body { font-family: 'Segoe UI', Tahoma, Arial, sans-serif; direction: rtl; padding: 20px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: center; font-size: 13px; }
        th { background: #2c3e50; color: white; }
        td.cell { min-width: 120px; }
        .subject { font-weight: bold; color: #2c3e50; }
        .member { font-size: 11px; color: #666; }
        .classroom { font-size: 11px; color: #888; }
        .btn { padding: 8px 16px; cursor: pointer; margin: 5px; }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:center; margin-bottom:20px;">
        <button class="btn" onclick="window.print()">🖨️ طباعة</button>
        <button class="btn" onclick="window.close()">✖ إغلاق</button>
    </div>
    <h2>الجدول الدراسي</h2>
    <h3 style="text-align:center"><?= htmlspecialchars($departmentName) ?> - <?= htmlspecialchars($levelName) ?></h3>
    <table>
        <thead>
            <tr>
                <th>اليوم</th>
                <th>الفترة</th>
                <?php foreach ($sections as $sec): ?>
                <th><?= htmlspecialchars($sec) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($days as $day): ?>
            <?php $first = true; ?>
            <?php foreach ($sessions as $sess): ?>
                <?php $key = $day . '|' . $sess['name']; ?>
                <tr>
                    <?php if ($first): ?>
                    <td rowspan="<?= count($sessions) ?>" style="background:#f8f9fa; font-weight:bold;"><?= htmlspecialchars($day) ?></td>
                    <?php $first = false; endif; ?>
                    <td style="background:#f8f9fa; font-size:11px;">
                        <?= htmlspecialchars($sess['name']) ?><br>
                        <small><?= $sess['start'] ?> - <?= $sess['end'] ?></small>
                    </td>
                    <?php foreach ($sections as $sec): ?>
                    <td class="cell">
                        <?php if (isset($pivot[$key][$sec])): ?>
                            <?php $e = $pivot[$key][$sec]; ?>
                            <span class="subject"><?= htmlspecialchars($e['subject_name']) ?></span><br>
                            <span class="member"><?= htmlspecialchars($e['member_name']) ?></span><br>
                            <span class="classroom"><?= htmlspecialchars($e['classroom_name']) ?></span>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
        <?php
        exit();
    }
}

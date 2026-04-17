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
     * Export timetable as printable HTML page.
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

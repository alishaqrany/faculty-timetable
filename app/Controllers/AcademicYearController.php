<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/AcademicYear.php';
require_once APP_ROOT . '/app/Models/Semester.php';
require_once APP_ROOT . '/app/Services/AuditService.php';

use App\Models\AcademicYear;
use App\Models\Semester;
use App\Services\AuditService;

class AcademicYearController extends \Controller
{
    private function createDefaultSemestersForYear(int $academicYearId, string $yearStartDate, string $yearEndDate): int
    {
        $db = \Database::getInstance();
        $existing = (int)$db->fetchColumn(
            "SELECT COUNT(*) FROM semesters WHERE academic_year_id = ?",
            [$academicYearId]
        );

        if ($existing > 0) {
            return 0;
        }

        $startYear = (int)date('Y', strtotime($yearStartDate));

        $firstStart = $yearStartDate;
        $firstEnd = sprintf('%04d-01-31', $startYear + 1);
        $secondStart = sprintf('%04d-02-01', $startYear + 1);
        $secondEnd = $yearEndDate;

        if (strtotime($firstEnd) >= strtotime($secondStart)
            && strtotime($firstStart) <= strtotime($firstEnd)
            && strtotime($secondStart) <= strtotime($secondEnd)) {
            Semester::create([
                'semester_name' => 'الفصل الدراسي الأول',
                'academic_year_id' => $academicYearId,
                'start_date' => $firstStart,
                'end_date' => $firstEnd,
                'is_current' => 0,
                'is_active' => 1,
            ]);

            Semester::create([
                'semester_name' => 'الفصل الدراسي الثاني',
                'academic_year_id' => $academicYearId,
                'start_date' => $secondStart,
                'end_date' => $secondEnd,
                'is_current' => 0,
                'is_active' => 1,
            ]);

            return 2;
        }

        // Fallback: if the year window is unusual, create one semester spanning the full year.
        Semester::create([
            'semester_name' => 'الفصل الدراسي',
            'academic_year_id' => $academicYearId,
            'start_date' => $yearStartDate,
            'end_date' => $yearEndDate,
            'is_current' => 0,
            'is_active' => 1,
        ]);

        return 1;
    }

    private function nextSuggestedYear(): array
    {
        $db = \Database::getInstance();
        $maxStartYear = (int)($db->fetchColumn("SELECT MAX(YEAR(start_date)) FROM academic_years") ?? 0);

        if ($maxStartYear <= 0) {
            $nextStartYear = (int)date('Y');
        } else {
            $nextStartYear = $maxStartYear + 1;
        }

        $startDate = sprintf('%04d-09-01', $nextStartYear);
        $endDate = sprintf('%04d-06-30', $nextStartYear + 1);

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'year_name' => $this->normalizedYearName($startDate, $endDate),
            'year_range' => sprintf('%04d-%04d', $nextStartYear, $nextStartYear + 1),
        ];
    }

    private function yearRangeOptions(int $count = 6): array
    {
        $first = $this->nextSuggestedYear();
        $startYear = (int)date('Y', strtotime($first['start_date']));
        $options = [];

        for ($i = 0; $i < $count; $i++) {
            $s = $startYear + $i;
            $options[] = sprintf('%04d-%04d', $s, $s + 1);
        }

        return $options;
    }

    private function parseYearRange(string $input): ?array
    {
        $normalized = strtr(trim($input), [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        $normalized = str_replace(['–', '—', '−', '/', '\\', ' '], '-', $normalized);
        $normalized = preg_replace('/-+/', '-', $normalized);

        if (!preg_match('/^(\d{4})-(\d{4})$/', $normalized, $m)) {
            return null;
        }

        $startYear = (int)$m[1];
        $endYear = (int)$m[2];

        if ($endYear !== $startYear + 1) {
            return null;
        }

        $startDate = sprintf('%04d-09-01', $startYear);
        $endDate = sprintf('%04d-06-30', $endYear);

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'year_name' => $startYear . '-' . $endYear,
            'year_range' => sprintf('%04d-%04d', $startYear, $endYear),
        ];
    }

    private function hasDuplicateRange(string $startDate, string $endDate, ?int $exceptId = null): bool
    {
        $db = \Database::getInstance();

        if ($exceptId === null) {
            $count = (int)$db->fetchColumn(
                "SELECT COUNT(*) FROM academic_years WHERE start_date = ? AND end_date = ?",
                [$startDate, $endDate]
            );
            return $count > 0;
        }

        $count = (int)$db->fetchColumn(
            "SELECT COUNT(*) FROM academic_years WHERE start_date = ? AND end_date = ? AND id <> ?",
            [$startDate, $endDate, $exceptId]
        );
        return $count > 0;
    }

    private function normalizedYearName(string $startDate, string $endDate): string
    {
        $startYear = (int)date('Y', strtotime($startDate));
        $endYear = (int)date('Y', strtotime($endDate));
        return $startYear . '-' . $endYear;
    }

    private function isDateRangeValid(string $startDate, string $endDate): bool
    {
        return strtotime($startDate) <= strtotime($endDate);
    }

    public function index(): void
    {
        $this->authorize('academic_years.view');
        $years = AcademicYear::all('start_date DESC');

        $db = \Database::getInstance();
        foreach ($years as &$y) {
            $y['semester_count'] = (int)$db->fetchColumn(
                "SELECT COUNT(*) FROM semesters WHERE academic_year_id = ?",
                [$y['id']]
            );
        }

        $this->render('academic-years.index', ['years' => $years]);
    }

    public function create(): void
    {
        $this->authorize('academic_years.manage');
        $suggested = $this->nextSuggestedYear();
        $yearRangeOptions = $this->yearRangeOptions();
        $this->render('academic-years.create', ['suggested' => $suggested, 'yearRangeOptions' => $yearRangeOptions]);
    }

    public function store(): void
    {
        $this->authorize('academic_years.manage');
        $this->validateCsrf();

        $yearRangeInput = (string)$this->request->input('year_range', '');
        $parsed = $this->parseYearRange($yearRangeInput);
        if ($parsed === null) {
            $this->redirect('/academic-years/create', 'صيغة السنة غير صحيحة. استخدم مثل: 2025-2026', 'error');
        }

        $data = [
            'year_name' => $parsed['year_name'],
            'start_date' => $parsed['start_date'],
            'end_date' => $parsed['end_date'],
        ];

        if (!$this->isDateRangeValid($data['start_date'], $data['end_date'])) {
            $this->redirect('/academic-years/create', 'تاريخ النهاية يجب أن يكون بعد أو يساوي تاريخ البداية.', 'error');
        }

        if ($this->hasDuplicateRange($data['start_date'], $data['end_date'])) {
            $this->redirect('/academic-years/create', 'هذه السنة الأكاديمية موجودة بالفعل ولا يمكن تكرارها.', 'error');
        }

        $data['is_current'] = $this->request->input('is_current', 0) ? 1 : 0;
        $data['is_active'] = 1;

        if ($data['is_current']) {
            \Database::getInstance()->execute("UPDATE academic_years SET is_current = 0");
        }

        $id = AcademicYear::create($data);
        $createdSemesters = $this->createDefaultSemestersForYear((int)$id, $data['start_date'], $data['end_date']);
        AuditService::log('CREATE', 'academic_years', $id, null, $data);

        if ($createdSemesters > 0) {
            $this->redirect('/academic-years', "تم إنشاء السنة الأكاديمية بنجاح ✓ وتم إنشاء {$createdSemesters} فصل/فصول افتراضيًا");
        }

        $this->redirect('/academic-years', 'تم إنشاء السنة الأكاديمية بنجاح ✓');
    }

    public function edit(string $id): void
    {
        $this->authorize('academic_years.manage');
        $year = AcademicYear::find((int)$id);
        if (!$year) {
            $this->redirect('/academic-years', 'السنة الأكاديمية غير موجودة', 'error');
        }

        $startYear = (int)date('Y', strtotime((string)$year['start_date']));
        $endYear = (int)date('Y', strtotime((string)$year['end_date']));
        $year['year_range'] = sprintf('%04d-%04d', $startYear, $endYear);
        $yearRangeOptions = $this->yearRangeOptions();

        $semesters = Semester::forYear((int)$id);
        $this->render('academic-years.edit', ['year' => $year, 'semesters' => $semesters, 'yearRangeOptions' => $yearRangeOptions]);
    }

    public function update(string $id): void
    {
        $this->authorize('academic_years.manage');
        $this->validateCsrf();

        $year = AcademicYear::find((int)$id);
        if (!$year) {
            $this->redirect('/academic-years', 'السنة الأكاديمية غير موجودة', 'error');
        }

        $yearRangeInput = (string)$this->request->input('year_range', '');
        $parsed = $this->parseYearRange($yearRangeInput);
        if ($parsed === null) {
            $this->redirect("/academic-years/{$id}/edit", 'صيغة السنة غير صحيحة. استخدم مثل: 2025-2026', 'error');
        }

        $data = [
            'year_name' => $parsed['year_name'],
            'start_date' => $parsed['start_date'],
            'end_date' => $parsed['end_date'],
        ];

        if (!$this->isDateRangeValid($data['start_date'], $data['end_date'])) {
            $this->redirect("/academic-years/{$id}/edit", 'تاريخ النهاية يجب أن يكون بعد أو يساوي تاريخ البداية.', 'error');
        }

        if ($this->hasDuplicateRange($data['start_date'], $data['end_date'], (int)$id)) {
            $this->redirect("/academic-years/{$id}/edit", 'هذه السنة الأكاديمية موجودة بالفعل ولا يمكن تكرارها.', 'error');
        }

        $data['is_active'] = $this->request->input('is_active', 1);
        AcademicYear::updateById((int)$id, $data);
        AuditService::log('UPDATE', 'academic_years', (int)$id, $year, $data);

        $this->redirect('/academic-years', 'تم تحديث السنة الأكاديمية بنجاح ✓');
    }

    public function setCurrent(string $id): void
    {
        $this->authorize('academic_years.manage');
        $this->validateCsrf();

        $year = AcademicYear::find((int)$id);
        if (!$year) {
            $this->redirect('/academic-years', 'السنة الأكاديمية غير موجودة', 'error');
        }

        AcademicYear::setCurrent((int)$id);
        AuditService::log('UPDATE', 'academic_years', (int)$id, $year, ['is_current' => 1]);

        $this->redirect('/academic-years', 'تم تعيين السنة الأكاديمية الحالية ✓');
    }

    public function destroy(string $id): void
    {
        $this->authorize('academic_years.manage');
        $this->validateCsrf();

        $year = AcademicYear::find((int)$id);
        if (!$year) {
            $this->redirect('/academic-years', 'السنة الأكاديمية غير موجودة', 'error');
        }

        $semCount = \Database::getInstance()->fetchColumn(
            "SELECT COUNT(*) FROM semesters WHERE academic_year_id = ?",
            [(int)$id]
        );

        if ($semCount > 0) {
            $this->redirect('/academic-years', 'لا يمكن حذف سنة أكاديمية مرتبطة بفصول دراسية', 'error');
        }

        AcademicYear::destroy((int)$id);
        AuditService::log('DELETE', 'academic_years', (int)$id, $year);

        $this->redirect('/academic-years', 'تم حذف السنة الأكاديمية بنجاح ✓');
    }
}

<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/Semester.php';
require_once APP_ROOT . '/app/Models/AcademicYear.php';
require_once APP_ROOT . '/app/Services/AuditService.php';

use App\Models\Semester;
use App\Models\AcademicYear;
use App\Services\AuditService;

class SemesterController extends \Controller
{
    private function buildDefaultSemesters(array $targetYear): array
    {
        $startYear = (int)date('Y', strtotime((string)$targetYear['start_date']));

        return [
            [
                'semester_name' => 'الفصل الدراسي الأول',
                'start_date' => (string)$targetYear['start_date'],
                'end_date' => sprintf('%04d-01-31', $startYear + 1),
                'is_active' => 1,
            ],
            [
                'semester_name' => 'الفصل الدراسي الثاني',
                'start_date' => sprintf('%04d-02-01', $startYear + 1),
                'end_date' => (string)$targetYear['end_date'],
                'is_active' => 1,
            ],
        ];
    }

    private function safeDateForYear(int $year, int $month, int $day): string
    {
        while ($day > 28 && !checkdate($month, $day, $year)) {
            $day--;
        }
        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    private function mapDateToTargetAcademicYear(string $sourceDate, int $targetStartYear): string
    {
        $month = (int)date('m', strtotime($sourceDate));
        $day = (int)date('d', strtotime($sourceDate));
        // Academic year starts in Sep: Sep-Dec belong to start year, Jan-Aug belong to next year.
        $mappedYear = $month >= 9 ? $targetStartYear : ($targetStartYear + 1);
        return $this->safeDateForYear($mappedYear, $month, $day);
    }

    private function buildSemestersFromSource(array $sourceSemesters, array $targetYear): array
    {
        $targetStartYear = (int)date('Y', strtotime((string)$targetYear['start_date']));
        $result = [];

        foreach ($sourceSemesters as $s) {
            $result[] = [
                'semester_name' => (string)$s['semester_name'],
                'start_date' => $this->mapDateToTargetAcademicYear((string)$s['start_date'], $targetStartYear),
                'end_date' => $this->mapDateToTargetAcademicYear((string)$s['end_date'], $targetStartYear),
                'is_active' => (int)($s['is_active'] ?? 1),
            ];
        }

        usort($result, static fn($a, $b) => strcmp($a['start_date'], $b['start_date']));
        return $result;
    }

    public function index(): void
    {
        $this->authorize('semesters.view');
        $semesters = Semester::allWithYear();
        $years = AcademicYear::all('start_date DESC');
        $this->render('semesters.index', ['semesters' => $semesters, 'years' => $years]);
    }

    public function create(): void
    {
        $this->authorize('semesters.manage');
        $years = AcademicYear::all('start_date DESC');
        $this->render('semesters.create', ['years' => $years]);
    }

    public function store(): void
    {
        $this->authorize('semesters.manage');
        $this->validateCsrf();

        $data = $this->request->validate([
            'semester_name'    => 'required|max:100',
            'academic_year_id' => 'required|numeric',
            'start_date'       => 'required',
            'end_date'         => 'required',
        ]);

        if ($data === false) {
            $this->redirect('/semesters/create', 'يرجى تصحيح الأخطاء', 'error');
        }

        $data['is_current'] = $this->request->input('is_current', 0) ? 1 : 0;
        $data['is_active'] = 1;

        if ($data['is_current']) {
            \Database::getInstance()->execute("UPDATE semesters SET is_current = 0");
        }

        $id = Semester::create($data);
        AuditService::log('CREATE', 'semesters', $id, null, $data);

        $this->redirect('/semesters', 'تم إنشاء الفصل الدراسي بنجاح ✓');
    }

    public function edit(string $id): void
    {
        $this->authorize('semesters.manage');
        $semester = Semester::find((int)$id);
        if (!$semester) $this->redirect('/semesters', 'الفصل الدراسي غير موجود', 'error');

        $years = AcademicYear::all('start_date DESC');
        $this->render('semesters.edit', ['semester' => $semester, 'years' => $years]);
    }

    public function update(string $id): void
    {
        $this->authorize('semesters.manage');
        $this->validateCsrf();

        $semester = Semester::find((int)$id);
        if (!$semester) $this->redirect('/semesters', 'الفصل الدراسي غير موجود', 'error');

        $data = $this->request->validate([
            'semester_name'    => 'required|max:100',
            'academic_year_id' => 'required|numeric',
            'start_date'       => 'required',
            'end_date'         => 'required',
        ]);

        if ($data === false) {
            $this->redirect("/semesters/{$id}/edit", 'يرجى تصحيح الأخطاء', 'error');
        }

        $data['is_active'] = $this->request->input('is_active', 1);
        Semester::updateById((int)$id, $data);
        AuditService::log('UPDATE', 'semesters', (int)$id, $semester, $data);

        $this->redirect('/semesters', 'تم تحديث الفصل الدراسي بنجاح ✓');
    }

    public function setCurrent(string $id): void
    {
        $this->authorize('semesters.manage');
        $this->validateCsrf();

        $semester = Semester::find((int)$id);
        if (!$semester) $this->redirect('/semesters', 'الفصل الدراسي غير موجود', 'error');

        Semester::setCurrent((int)$id);
        // Keep current academic year in sync with the selected current semester.
        \Database::getInstance()->execute("UPDATE academic_years SET is_current = 0");
        \Database::getInstance()->execute(
            "UPDATE academic_years SET is_current = 1 WHERE id = ?",
            [(int)$semester['academic_year_id']]
        );
        AuditService::log('UPDATE', 'semesters', (int)$id, $semester, ['is_current' => 1]);

        $this->redirect('/semesters', 'تم تعيين الفصل الدراسي الحالي ✓');
    }

    public function generateForYear(): void
    {
        $this->authorize('semesters.manage');
        $this->validateCsrf();

        $targetYearId = (int)$this->request->input('academic_year_id', 0);
        $mode = (string)$this->request->input('mode', 'two'); // two | copy
        $sourceYearId = (int)$this->request->input('source_year_id', 0);
        $replaceExisting = (int)$this->request->input('replace_existing', 0) === 1;
        $setFirstCurrent = (int)$this->request->input('set_first_current', 0) === 1;

        if ($targetYearId <= 0) {
            $this->redirect('/semesters', 'اختر السنة الأكاديمية المستهدفة.', 'error');
        }

        $targetYear = AcademicYear::find($targetYearId);
        if (!$targetYear) {
            $this->redirect('/semesters', 'السنة الأكاديمية المستهدفة غير موجودة.', 'error');
        }

        $db = \Database::getInstance();
        $existingCount = (int)$db->fetchColumn(
            "SELECT COUNT(*) FROM semesters WHERE academic_year_id = ?",
            [$targetYearId]
        );

        if ($existingCount > 0 && !$replaceExisting) {
            $this->redirect('/semesters', 'هذه السنة تحتوي فصولًا بالفعل. فعّل خيار الاستبدال لإعادة التوليد.', 'error');
        }

        if ($existingCount > 0 && $replaceExisting) {
            $linked = (int)$db->fetchColumn(
                "SELECT COUNT(*) FROM timetable t JOIN semesters s ON s.id = t.semester_id WHERE s.academic_year_id = ?",
                [$targetYearId]
            );
            if ($linked > 0) {
                $this->redirect('/semesters', 'لا يمكن الاستبدال لأن هناك جداول مرتبطة بفصول هذه السنة.', 'error');
            }

            $db->execute("DELETE FROM semesters WHERE academic_year_id = ?", [$targetYearId]);
        }

        if ($mode === 'copy') {
            if ($sourceYearId <= 0) {
                $this->redirect('/semesters', 'اختر سنة مصدر للنسخ.', 'error');
            }
            if ($sourceYearId === $targetYearId) {
                $this->redirect('/semesters', 'لا يمكن النسخ من نفس السنة المستهدفة.', 'error');
            }

            $sourceSemesters = Semester::forYear($sourceYearId);
            if (empty($sourceSemesters)) {
                $this->redirect('/semesters', 'سنة المصدر لا تحتوي فصولًا للنسخ.', 'error');
            }

            $templates = $this->buildSemestersFromSource($sourceSemesters, $targetYear);
        } else {
            $templates = $this->buildDefaultSemesters($targetYear);
        }

        $createdIds = [];
        foreach ($templates as $t) {
            if (strtotime($t['start_date']) > strtotime($t['end_date'])) {
                continue;
            }

            $createdIds[] = Semester::create([
                'semester_name' => $t['semester_name'],
                'academic_year_id' => $targetYearId,
                'start_date' => $t['start_date'],
                'end_date' => $t['end_date'],
                'is_current' => 0,
                'is_active' => $t['is_active'] ?? 1,
            ]);
        }

        if (empty($createdIds)) {
            $this->redirect('/semesters', 'لم يتم إنشاء فصول. تحقق من بيانات القالب.', 'error');
        }

        if ($setFirstCurrent) {
            Semester::setCurrent((int)$createdIds[0]);
            $db->execute("UPDATE academic_years SET is_current = 0");
            $db->execute("UPDATE academic_years SET is_current = 1 WHERE id = ?", [$targetYearId]);
        }

        AuditService::log('CREATE', 'semesters_generation', null, null, [
            'academic_year_id' => $targetYearId,
            'mode' => $mode,
            'source_year_id' => $sourceYearId,
            'count' => count($createdIds),
            'replace_existing' => $replaceExisting,
        ]);

        $this->redirect('/semesters', 'تم توليد الفصول الدراسية بنجاح ✓');
    }

    public function destroy(string $id): void
    {
        $this->authorize('semesters.manage');
        $this->validateCsrf();

        $semester = Semester::find((int)$id);
        if (!$semester) $this->redirect('/semesters', 'الفصل الدراسي غير موجود', 'error');

        // Check for linked timetable/member_courses entries
        $db = \Database::getInstance();
        $linked = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM timetable WHERE semester_id = ?", [(int)$id]
        );
        if ($linked > 0) {
            $this->redirect('/semesters', 'لا يمكن حذف فصل مرتبط بجداول زمنية', 'error');
        }

        Semester::destroy((int)$id);
        AuditService::log('DELETE', 'semesters', (int)$id, $semester);

        $this->redirect('/semesters', 'تم حذف الفصل الدراسي بنجاح ✓');
    }
}

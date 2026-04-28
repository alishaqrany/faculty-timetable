import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:mobile_app/core/widgets/empty_state_widget.dart';
import 'package:mobile_app/core/widgets/error_state_widget.dart';
import 'package:mobile_app/core/widgets/lecture_card.dart';
import 'package:mobile_app/core/widgets/loading_overlay.dart';
import 'package:mobile_app/features/student/timetable_provider.dart';

class YearTimetablePage extends StatefulWidget {
  const YearTimetablePage({super.key});

  @override
  State<YearTimetablePage> createState() => _YearTimetablePageState();
}

class _YearTimetablePageState extends State<YearTimetablePage> {
  @override
  void initState() {
    super.initState();
    // Ensure lookups are loaded
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<TimetableProvider>().loadLookups();
    });
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Consumer<TimetableProvider>(
      builder: (context, provider, _) {
        return Column(
          children: [
            // ── Filter Card ────────────────────────────────────
            Card(
              margin: const EdgeInsets.fromLTRB(16, 12, 16, 4),
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'اختر القسم والفرقة',
                      style: theme.textTheme.labelLarge?.copyWith(
                        fontWeight: FontWeight.w700,
                        color: theme.colorScheme.primary,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: DropdownButtonFormField<int>(
                            key: ValueKey('dept_${provider.selectedDepartmentId}'),
                            decoration: const InputDecoration(
                              labelText: 'القسم',
                              prefixIcon: Icon(Icons.school_outlined),
                              isDense: true,
                            ),
                            initialValue: provider.selectedDepartmentId,
                            items: provider.departments
                                .map((e) => DropdownMenuItem<int>(
                                      value: e['department_id'] as int,
                                      child: Text(
                                        e['department_name'].toString(),
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ))
                                .toList(),
                            onChanged: (v) {
                              provider.selectedDepartmentId = v;
                              provider.yearRows = [];
                            },
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: DropdownButtonFormField<int>(
                            key: ValueKey('level_${provider.selectedLevelId}'),
                            decoration: const InputDecoration(
                              labelText: 'الفرقة',
                              prefixIcon: Icon(Icons.layers_outlined),
                              isDense: true,
                            ),
                            initialValue: provider.selectedLevelId,
                            items: provider.levels
                                .map((e) => DropdownMenuItem<int>(
                                      value: e['level_id'] as int,
                                      child: Text(
                                        e['level_name'].toString(),
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ))
                                .toList(),
                            onChanged: (v) {
                              provider.selectedLevelId = v;
                              provider.yearRows = [];
                            },
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton.icon(
                        onPressed: (provider.selectedDepartmentId != null &&
                                provider.selectedLevelId != null)
                            ? provider.fetchYearTimetable
                            : null,
                        icon: const Icon(Icons.search, size: 18),
                        label: const Text('عرض الجدول'),
                      ),
                    ),
                  ],
                ),
              ),
            ),

            // ── Cache indicator ────────────────────────────────
            if (provider.isFromCache && provider.yearRows.isNotEmpty)
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                child: Row(
                  children: [
                    Icon(Icons.offline_bolt_outlined,
                        size: 14, color: theme.colorScheme.tertiary),
                    const SizedBox(width: 4),
                    Text(
                      'بيانات محفوظة مسبقاً',
                      style: theme.textTheme.bodySmall?.copyWith(
                        color: theme.colorScheme.tertiary,
                      ),
                    ),
                  ],
                ),
              ),

            // ── Content ────────────────────────────────────────
            Expanded(child: _buildContent(provider)),
          ],
        );
      },
    );
  }

  Widget _buildContent(TimetableProvider provider) {
    if (provider.error != null && provider.yearRows.isEmpty) {
      return ErrorStateWidget(
        message: provider.error!,
        onRetry: provider.fetchYearTimetable,
      );
    }

    if (provider.isLoading && provider.yearRows.isEmpty) {
      return const LoadingShimmer(message: 'جاري تحميل الجدول...');
    }

    if (provider.yearRows.isEmpty) {
      return const EmptyStateWidget(
        icon: Icons.view_week_outlined,
        title: 'لا توجد نتائج',
        subtitle: 'اختر القسم والفرقة ثم اضغط "عرض الجدول"',
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.only(top: 4, bottom: 16),
      itemCount: provider.yearRows.length,
      itemBuilder: (_, i) {
        final row = provider.yearRows[i] as Map<String, dynamic>;
        return LectureCard(
          subjectName: row['subject_name']?.toString() ?? '-',
          day: row['day']?.toString(),
          sessionName: row['session_name']?.toString(),
          startTime: row['start_time']?.toString(),
          endTime: row['end_time']?.toString(),
          classroomName: row['classroom_name']?.toString(),
          sectionName: row['section_name']?.toString(),
        );
      },
    );
  }
}

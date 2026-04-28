import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:mobile_app/core/widgets/empty_state_widget.dart';
import 'package:mobile_app/core/widgets/error_state_widget.dart';
import 'package:mobile_app/core/widgets/lecture_card.dart';
import 'package:mobile_app/core/widgets/loading_overlay.dart';
import 'package:mobile_app/features/student/timetable_provider.dart';

class SectionTimetablePage extends StatefulWidget {
  const SectionTimetablePage({super.key});

  @override
  State<SectionTimetablePage> createState() => _SectionTimetablePageState();
}

class _SectionTimetablePageState extends State<SectionTimetablePage> {
  @override
  void initState() {
    super.initState();
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
                      'اختر السكشن',
                      style: theme.textTheme.labelLarge?.copyWith(
                        fontWeight: FontWeight.w700,
                        color: theme.colorScheme.primary,
                      ),
                    ),
                    const SizedBox(height: 12),
                    DropdownButtonFormField<int>(
                      key: ValueKey('sec_${provider.selectedSectionId}'),
                      decoration: const InputDecoration(
                        labelText: 'السكشن',
                        prefixIcon: Icon(Icons.group_outlined),
                        isDense: true,
                      ),
                      initialValue: provider.selectedSectionId,
                      items: provider.sections
                          .map((e) => DropdownMenuItem<int>(
                                value: e['section_id'] as int,
                                child: Text(
                                  e['section_name'].toString(),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ))
                          .toList(),
                      onChanged: (v) {
                        provider.selectedSectionId = v;
                        provider.sectionRows = [];
                      },
                    ),
                    const SizedBox(height: 12),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton.icon(
                        onPressed: provider.selectedSectionId != null
                            ? provider.fetchSectionTimetable
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
            if (provider.isFromCache && provider.sectionRows.isNotEmpty)
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
    if (provider.error != null && provider.sectionRows.isEmpty) {
      return ErrorStateWidget(
        message: provider.error!,
        onRetry: provider.fetchSectionTimetable,
      );
    }

    if (provider.isLoading && provider.sectionRows.isEmpty) {
      return const LoadingShimmer(message: 'جاري تحميل الجدول...');
    }

    if (provider.sectionRows.isEmpty) {
      return const EmptyStateWidget(
        icon: Icons.group_outlined,
        title: 'لا توجد نتائج',
        subtitle: 'اختر السكشن ثم اضغط "عرض الجدول"',
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.only(top: 4, bottom: 16),
      itemCount: provider.sectionRows.length,
      itemBuilder: (_, i) {
        final row = provider.sectionRows[i] as Map<String, dynamic>;
        return LectureCard(
          subjectName: row['subject_name']?.toString() ?? '-',
          day: row['day']?.toString(),
          sessionName: row['session_name']?.toString(),
          startTime: row['start_time']?.toString(),
          endTime: row['end_time']?.toString(),
          classroomName: row['classroom_name']?.toString(),
          memberName: row['member_name']?.toString(),
        );
      },
    );
  }
}

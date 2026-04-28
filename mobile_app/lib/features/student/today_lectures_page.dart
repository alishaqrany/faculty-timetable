import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:mobile_app/core/app_theme.dart';
import 'package:mobile_app/core/widgets/empty_state_widget.dart';
import 'package:mobile_app/core/widgets/error_state_widget.dart';
import 'package:mobile_app/core/widgets/lecture_card.dart';
import 'package:mobile_app/core/widgets/loading_overlay.dart';
import 'package:mobile_app/features/student/timetable_provider.dart';

class TodayLecturesPage extends StatefulWidget {
  const TodayLecturesPage({super.key});

  @override
  State<TodayLecturesPage> createState() => _TodayLecturesPageState();
}

class _TodayLecturesPageState extends State<TodayLecturesPage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final provider = context.read<TimetableProvider>();
      provider.loadLookups();
      provider.fetchTodayLectures();
    });
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    return Consumer<TimetableProvider>(
      builder: (context, provider, _) {
        return Column(
          children: [
            // ── Today header ───────────────────────────────────
            if (provider.todayDay.isNotEmpty || provider.todayDate.isNotEmpty)
              Container(
                width: double.infinity,
                margin: const EdgeInsets.fromLTRB(16, 12, 16, 4),
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  gradient: AppTheme.primaryGradient,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFF4F46E5).withValues(alpha: 0.25),
                      blurRadius: 16,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    Container(
                      width: 48,
                      height: 48,
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.2),
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: const Icon(
                        Icons.calendar_today_rounded,
                        color: Colors.white,
                        size: 22,
                      ),
                    ),
                    const SizedBox(width: 14),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          provider.todayDay,
                          style: theme.textTheme.titleMedium?.copyWith(
                            fontWeight: FontWeight.w800,
                            color: Colors.white,
                          ),
                        ),
                        Text(
                          provider.todayDate,
                          style: theme.textTheme.bodySmall?.copyWith(
                            color: Colors.white.withValues(alpha: 0.8),
                          ),
                        ),
                      ],
                    ),
                    const Spacer(),
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 12, vertical: 6),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.2),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        '${provider.todayLectures.length} محاضرة',
                        style: theme.textTheme.labelMedium?.copyWith(
                          color: Colors.white,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ],
                ),
              ),

            // ── Section filter ─────────────────────────────────
            Card(
              margin: const EdgeInsets.fromLTRB(16, 8, 16, 4),
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                child: Row(
                  children: [
                    Expanded(
                      child: DropdownButtonFormField<int>(
                        key: ValueKey('today_sec_${provider.todaySectionId}'),
                        decoration: InputDecoration(
                          labelText: 'فلتر بالسكشن (اختياري)',
                          prefixIcon: const Icon(Icons.filter_list_rounded),
                          isDense: true,
                          contentPadding: const EdgeInsets.symmetric(
                              horizontal: 12, vertical: 10),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: BorderSide(
                              color: isDark
                                  ? Colors.white.withValues(alpha: 0.1)
                                  : Colors.grey.shade300,
                            ),
                          ),
                        ),
                        initialValue: provider.todaySectionId,
                        items: [
                          const DropdownMenuItem<int>(
                            value: null,
                            child: Text('الكل'),
                          ),
                          ...provider.sections.map((e) => DropdownMenuItem<int>(
                                value: e['section_id'] as int,
                                child: Text(
                                  e['section_name'].toString(),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              )),
                        ],
                        onChanged: (v) {
                          provider.todaySectionId = v;
                          provider.fetchTodayLectures();
                        },
                      ),
                    ),
                    const SizedBox(width: 8),
                    IconButton.filled(
                      onPressed: provider.fetchTodayLectures,
                      icon: const Icon(Icons.refresh, size: 20),
                      tooltip: 'تحديث',
                    ),
                  ],
                ),
              ),
            ),

            // ── Cache indicator ────────────────────────────────
            if (provider.isFromCache && provider.todayLectures.isNotEmpty)
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
    if (provider.error != null && provider.todayLectures.isEmpty) {
      return ErrorStateWidget(
        message: provider.error!,
        onRetry: provider.fetchTodayLectures,
      );
    }

    if (provider.isLoading && provider.todayLectures.isEmpty) {
      return const LoadingShimmer(message: 'جاري تحميل محاضرات اليوم...');
    }

    if (provider.todayLectures.isEmpty) {
      return const EmptyStateWidget(
        icon: Icons.event_available_rounded,
        title: 'لا توجد محاضرات اليوم',
        subtitle: 'استمتع بيومك! 🎉',
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.only(top: 4, bottom: 16),
      itemCount: provider.todayLectures.length,
      itemBuilder: (_, i) {
        final row = provider.todayLectures[i] as Map<String, dynamic>;
        return LectureCard(
          subjectName: row['subject_name']?.toString() ?? '-',
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

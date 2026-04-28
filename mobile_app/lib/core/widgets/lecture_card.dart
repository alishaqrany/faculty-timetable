import 'package:flutter/material.dart';
import 'package:mobile_app/core/app_theme.dart';

/// A premium card widget for displaying a single lecture/session.
class LectureCard extends StatelessWidget {
  const LectureCard({
    super.key,
    required this.subjectName,
    this.sessionName,
    this.day,
    this.startTime,
    this.endTime,
    this.classroomName,
    this.memberName,
    this.sectionName,
    this.isActive = false,
  });

  final String subjectName;
  final String? sessionName;
  final String? day;
  final String? startTime;
  final String? endTime;
  final String? classroomName;
  final String? memberName;
  final String? sectionName;
  final bool isActive;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 5),
      decoration: BoxDecoration(
        gradient: isActive ? AppTheme.primaryGradient : AppTheme.cardGradient(isDark),
        borderRadius: BorderRadius.circular(16),
        border: isActive
            ? null
            : Border.all(
                color: isDark
                    ? Colors.white.withValues(alpha: 0.08)
                    : Colors.grey.shade200,
              ),
        boxShadow: [
          if (!isDark)
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.04),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ── Header: subject name ──────────────────────────
            Row(
              children: [
                Container(
                  width: 4,
                  height: 24,
                  decoration: BoxDecoration(
                    color: isActive ? Colors.white : theme.colorScheme.primary,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    subjectName,
                    style: theme.textTheme.titleSmall?.copyWith(
                      fontWeight: FontWeight.w700,
                      color: isActive ? Colors.white : theme.colorScheme.onSurface,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),

            // ── Detail chips ─────────────────────────────────
            Wrap(
              spacing: 8,
              runSpacing: 6,
              children: [
                if (day != null && day!.isNotEmpty)
                  _chip(context, Icons.calendar_today_rounded, day!),
                if (startTime != null && endTime != null)
                  _chip(context, Icons.access_time_rounded, '$startTime - $endTime'),
                if (startTime != null && endTime == null && sessionName != null)
                  _chip(context, Icons.access_time_rounded, sessionName!),
                if (classroomName != null && classroomName!.isNotEmpty)
                  _chip(context, Icons.location_on_outlined, classroomName!),
                if (memberName != null && memberName!.isNotEmpty)
                  _chip(context, Icons.person_outline_rounded, memberName!),
                if (sectionName != null && sectionName!.isNotEmpty)
                  _chip(context, Icons.group_outlined, sectionName!),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _chip(BuildContext context, IconData icon, String label) {
    final theme = Theme.of(context);
    final textColor = isActive
        ? Colors.white.withValues(alpha: 0.85)
        : theme.colorScheme.onSurface.withValues(alpha: 0.65);

    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 14, color: textColor),
        const SizedBox(width: 4),
        Text(
          label,
          style: theme.textTheme.bodySmall?.copyWith(color: textColor),
        ),
      ],
    );
  }
}

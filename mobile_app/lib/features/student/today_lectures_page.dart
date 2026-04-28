import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:mobile_app/features/auth/auth_service.dart';
import 'package:mobile_app/features/student/lookups_api.dart';
import 'package:mobile_app/features/student/student_api.dart';

class TodayLecturesPage extends StatefulWidget {
  const TodayLecturesPage({super.key, required this.authService});

  final AuthService authService;

  @override
  State<TodayLecturesPage> createState() => _TodayLecturesPageState();
}

class _TodayLecturesPageState extends State<TodayLecturesPage> {
  late final LookupsApi _lookups = LookupsApi(widget.authService.client);
  late final StudentApi _student = StudentApi(widget.authService.client);

  List<dynamic> _sections = [];
  int? _sectionId;
  String _day = '';
  String _date = '';
  List<dynamic> _lectures = [];
  bool _loading = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final sections = await _lookups.sections();
    setState(() => _sections = sections);
    await _fetch();
  }

  Future<void> _fetch() async {
    setState(() => _loading = true);
    try {
      final data = await _student.todayLectures(sectionId: _sectionId);
      setState(() {
        _day = data['day']?.toString() ?? '';
        _date = data['date']?.toString() ?? DateFormat('yyyy-MM-dd').format(DateTime.now());
        _lectures = data['lectures'] as List<dynamic>? ?? <dynamic>[];
      });
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            children: [
              Expanded(
                child: DropdownButtonFormField<int>(
                  decoration: const InputDecoration(labelText: 'فلتر بالسكشن (اختياري)'),
                  initialValue: _sectionId,
                  items: _sections
                      .map((e) => DropdownMenuItem<int>(
                            value: e['section_id'] as int,
                            child: Text(e['section_name'].toString()),
                          ))
                      .toList(),
                  onChanged: (v) => setState(() => _sectionId = v),
                ),
              ),
              const SizedBox(width: 8),
              ElevatedButton(onPressed: _fetch, child: const Text('تحديث')),
            ],
          ),
        ),
        if (_loading) const LinearProgressIndicator(),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          child: Align(
            alignment: Alignment.centerRight,
            child: Text('$_day - $_date'),
          ),
        ),
        Expanded(
          child: ListView.builder(
            itemCount: _lectures.length,
            itemBuilder: (_, i) {
              final row = _lectures[i] as Map<String, dynamic>;
              return ListTile(
                leading: const Icon(Icons.calendar_today),
                title: Text(row['subject_name']?.toString() ?? '-'),
                subtitle: Text(
                  '${row['session_name'] ?? ''} (${row['start_time'] ?? ''}-${row['end_time'] ?? ''})',
                ),
                trailing: Text(row['classroom_name']?.toString() ?? ''),
              );
            },
          ),
        ),
      ],
    );
  }
}

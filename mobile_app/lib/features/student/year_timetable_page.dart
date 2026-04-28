import 'package:flutter/material.dart';
import 'package:mobile_app/features/auth/auth_service.dart';
import 'package:mobile_app/features/student/lookups_api.dart';
import 'package:mobile_app/features/student/student_api.dart';

class YearTimetablePage extends StatefulWidget {
  const YearTimetablePage({super.key, required this.authService});

  final AuthService authService;

  @override
  State<YearTimetablePage> createState() => _YearTimetablePageState();
}

class _YearTimetablePageState extends State<YearTimetablePage> {
  late final LookupsApi _lookups = LookupsApi(widget.authService.client);
  late final StudentApi _student = StudentApi(widget.authService.client);

  List<dynamic> _departments = [];
  List<dynamic> _levels = [];
  List<dynamic> _rows = [];
  int? _departmentId;
  int? _levelId;
  bool _loading = false;

  @override
  void initState() {
    super.initState();
    _loadLookups();
  }

  Future<void> _loadLookups() async {
    final deps = await _lookups.departments();
    final levels = await _lookups.levels();
    setState(() {
      _departments = deps;
      _levels = levels;
    });
  }

  Future<void> _fetch() async {
    if (_departmentId == null || _levelId == null) return;
    setState(() => _loading = true);
    try {
      final rows = await _student.timetable(
        departmentId: _departmentId!,
        levelId: _levelId!,
      );
      setState(() => _rows = rows);
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
                  decoration: const InputDecoration(labelText: 'القسم'),
                  initialValue: _departmentId,
                  items: _departments
                      .map((e) => DropdownMenuItem<int>(
                            value: e['department_id'] as int,
                            child: Text(e['department_name'].toString()),
                          ))
                      .toList(),
                  onChanged: (v) => setState(() => _departmentId = v),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: DropdownButtonFormField<int>(
                  decoration: const InputDecoration(labelText: 'الفرقة'),
                  initialValue: _levelId,
                  items: _levels
                      .map((e) => DropdownMenuItem<int>(
                            value: e['level_id'] as int,
                            child: Text(e['level_name'].toString()),
                          ))
                      .toList(),
                  onChanged: (v) => setState(() => _levelId = v),
                ),
              ),
              const SizedBox(width: 8),
              ElevatedButton(onPressed: _fetch, child: const Text('عرض')),
            ],
          ),
        ),
        if (_loading) const LinearProgressIndicator(),
        Expanded(
          child: ListView.builder(
            itemCount: _rows.length,
            itemBuilder: (_, i) {
              final row = _rows[i] as Map<String, dynamic>;
              return ListTile(
                title: Text(row['subject_name']?.toString() ?? '-'),
                subtitle: Text(
                  '${row['day'] ?? ''} - ${row['session_name'] ?? ''} - ${row['classroom_name'] ?? ''}',
                ),
                trailing: Text(row['section_name']?.toString() ?? ''),
              );
            },
          ),
        ),
      ],
    );
  }
}

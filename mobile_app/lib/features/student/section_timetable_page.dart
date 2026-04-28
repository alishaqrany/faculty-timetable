import 'package:flutter/material.dart';
import 'package:mobile_app/features/auth/auth_service.dart';
import 'package:mobile_app/features/student/lookups_api.dart';
import 'package:mobile_app/features/student/student_api.dart';

class SectionTimetablePage extends StatefulWidget {
  const SectionTimetablePage({super.key, required this.authService});

  final AuthService authService;

  @override
  State<SectionTimetablePage> createState() => _SectionTimetablePageState();
}

class _SectionTimetablePageState extends State<SectionTimetablePage> {
  late final LookupsApi _lookups = LookupsApi(widget.authService.client);
  late final StudentApi _student = StudentApi(widget.authService.client);

  List<dynamic> _sections = [];
  List<dynamic> _rows = [];
  int? _sectionId;
  bool _loading = false;

  @override
  void initState() {
    super.initState();
    _loadSections();
  }

  Future<void> _loadSections() async {
    final sections = await _lookups.sections();
    setState(() => _sections = sections);
  }

  Future<void> _fetch() async {
    if (_sectionId == null) return;
    setState(() => _loading = true);
    try {
      final rows = await _student.sectionTimetable(sectionId: _sectionId!);
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
                  decoration: const InputDecoration(labelText: 'السكشن'),
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
                  '${row['day'] ?? ''} - ${row['session_name'] ?? ''} - ${row['member_name'] ?? ''}',
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

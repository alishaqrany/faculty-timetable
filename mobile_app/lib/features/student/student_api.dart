import 'package:mobile_app/core/api_client.dart';

class StudentApi {
  const StudentApi(this.client);

  final ApiClient client;

  Future<List<dynamic>> timetable({
    required int departmentId,
    required int levelId,
  }) async {
    final res = await client.get('/api/v1/students/timetable', query: {
      'department_id': '$departmentId',
      'level_id': '$levelId',
    });
    return (res['data'] as List<dynamic>? ?? <dynamic>[]);
  }

  Future<List<dynamic>> sectionTimetable({required int sectionId}) async {
    final res = await client.get('/api/v1/students/section-timetable', query: {
      'section_id': '$sectionId',
    });
    return (res['data'] as List<dynamic>? ?? <dynamic>[]);
  }

  Future<Map<String, dynamic>> todayLectures({
    int? sectionId,
    int? departmentId,
    int? levelId,
  }) async {
    final query = <String, String>{};
    if (sectionId != null) query['section_id'] = '$sectionId';
    if (departmentId != null) query['department_id'] = '$departmentId';
    if (levelId != null) query['level_id'] = '$levelId';
    final res = await client.get('/api/v1/students/today-lectures', query: query);
    return (res['data'] as Map<String, dynamic>? ?? <String, dynamic>{});
  }
}

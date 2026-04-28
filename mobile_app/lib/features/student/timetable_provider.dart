import 'package:flutter/foundation.dart';
import 'package:mobile_app/core/api_client.dart';
import 'package:mobile_app/core/cache_manager.dart';
import 'package:mobile_app/features/student/lookups_api.dart';
import 'package:mobile_app/features/student/student_api.dart';

/// Centralised timetable state for all student screens.
class TimetableProvider extends ChangeNotifier {
  TimetableProvider({required ApiClient client})
      : _lookups = LookupsApi(client),
        _student = StudentApi(client);

  final LookupsApi _lookups;
  final StudentApi _student;
  final CacheManager _cache = CacheManager.instance;

  // ── Lookups ──────────────────────────────────────────────────────
  List<dynamic> departments = [];
  List<dynamic> levels = [];
  List<dynamic> sections = [];

  bool _lookupsLoaded = false;
  bool get lookupsLoaded => _lookupsLoaded;

  // ── Year timetable ───────────────────────────────────────────────
  List<dynamic> yearRows = [];
  int? selectedDepartmentId;
  int? selectedLevelId;

  // ── Section timetable ────────────────────────────────────────────
  List<dynamic> sectionRows = [];
  int? selectedSectionId;

  // ── Today lectures ───────────────────────────────────────────────
  String todayDay = '';
  String todayDate = '';
  List<dynamic> todayLectures = [];
  int? todaySectionId;

  // ── Common state ─────────────────────────────────────────────────
  bool isLoading = false;
  String? error;
  bool _isFromCache = false;
  bool get isFromCache => _isFromCache;

  // ── Load lookups (once) ──────────────────────────────────────────
  Future<void> loadLookups() async {
    if (_lookupsLoaded) return;

    // Try cache first
    final cachedDepts = await _cache.get(CacheManager.lookupsKey('departments'));
    final cachedLevels = await _cache.get(CacheManager.lookupsKey('levels'));
    final cachedSections = await _cache.get(CacheManager.lookupsKey('sections'));

    if (cachedDepts != null && cachedLevels != null && cachedSections != null) {
      departments = List<dynamic>.from(cachedDepts as List);
      levels = List<dynamic>.from(cachedLevels as List);
      sections = List<dynamic>.from(cachedSections as List);
      _lookupsLoaded = true;
      notifyListeners();
    }

    // Fetch fresh data in background
    try {
      final freshDepts = await _lookups.departments();
      final freshLevels = await _lookups.levels();
      final freshSections = await _lookups.sections();

      departments = freshDepts;
      levels = freshLevels;
      sections = freshSections;
      _lookupsLoaded = true;

      await _cache.set(CacheManager.lookupsKey('departments'), freshDepts,
          ttl: CacheManager.lookupsTtl);
      await _cache.set(CacheManager.lookupsKey('levels'), freshLevels,
          ttl: CacheManager.lookupsTtl);
      await _cache.set(CacheManager.lookupsKey('sections'), freshSections,
          ttl: CacheManager.lookupsTtl);

      notifyListeners();
    } catch (e) {
      // If we already have cached data, ignore the error
      if (!_lookupsLoaded) {
        // Try stale cache
        final staleDepts =
            await _cache.getStale(CacheManager.lookupsKey('departments'));
        final staleLevels =
            await _cache.getStale(CacheManager.lookupsKey('levels'));
        final staleSections =
            await _cache.getStale(CacheManager.lookupsKey('sections'));

        if (staleDepts != null && staleLevels != null && staleSections != null) {
          departments = List<dynamic>.from(staleDepts as List);
          levels = List<dynamic>.from(staleLevels as List);
          sections = List<dynamic>.from(staleSections as List);
          _lookupsLoaded = true;
          _isFromCache = true;
          notifyListeners();
        } else {
          error = 'تعذر تحميل البيانات الأساسية';
          notifyListeners();
        }
      }
    }
  }

  // ── Fetch Year Timetable ─────────────────────────────────────────
  Future<void> fetchYearTimetable() async {
    if (selectedDepartmentId == null || selectedLevelId == null) return;
    _startLoading();

    final cacheKey = CacheManager.yearTimetableKey(
        selectedDepartmentId!, selectedLevelId!);

    // Show cached data first
    final cached = await _cache.get(cacheKey);
    if (cached != null) {
      yearRows = List<dynamic>.from(cached as List);
      _isFromCache = true;
      notifyListeners();
    }

    try {
      final rows = await _student.timetable(
        departmentId: selectedDepartmentId!,
        levelId: selectedLevelId!,
      );
      yearRows = rows;
      _isFromCache = false;
      await _cache.set(cacheKey, rows);
      _finishLoading();
    } catch (e) {
      if (yearRows.isEmpty) {
        // Try stale
        final stale = await _cache.getStale(cacheKey);
        if (stale != null) {
          yearRows = List<dynamic>.from(stale as List);
          _isFromCache = true;
          _finishLoading();
        } else {
          _finishWithError(_friendlyError(e));
        }
      } else {
        // We have cached data, just note the error
        _isFromCache = true;
        _finishLoading();
      }
    }
  }

  // ── Fetch Section Timetable ──────────────────────────────────────
  Future<void> fetchSectionTimetable() async {
    if (selectedSectionId == null) return;
    _startLoading();

    final cacheKey = CacheManager.sectionTimetableKey(selectedSectionId!);

    final cached = await _cache.get(cacheKey);
    if (cached != null) {
      sectionRows = List<dynamic>.from(cached as List);
      _isFromCache = true;
      notifyListeners();
    }

    try {
      final rows = await _student.sectionTimetable(sectionId: selectedSectionId!);
      sectionRows = rows;
      _isFromCache = false;
      await _cache.set(cacheKey, rows);
      _finishLoading();
    } catch (e) {
      if (sectionRows.isEmpty) {
        final stale = await _cache.getStale(cacheKey);
        if (stale != null) {
          sectionRows = List<dynamic>.from(stale as List);
          _isFromCache = true;
          _finishLoading();
        } else {
          _finishWithError(_friendlyError(e));
        }
      } else {
        _isFromCache = true;
        _finishLoading();
      }
    }
  }

  // ── Fetch Today Lectures ─────────────────────────────────────────
  Future<void> fetchTodayLectures() async {
    _startLoading();

    final cacheKey = CacheManager.todayLecturesKey(sectionId: todaySectionId);

    final cached = await _cache.get(cacheKey);
    if (cached != null) {
      final data = cached as Map<String, dynamic>;
      todayDay = data['day']?.toString() ?? '';
      todayDate = data['date']?.toString() ?? '';
      todayLectures = List<dynamic>.from(data['lectures'] as List? ?? []);
      _isFromCache = true;
      notifyListeners();
    }

    try {
      final data = await _student.todayLectures(sectionId: todaySectionId);
      todayDay = data['day']?.toString() ?? '';
      todayDate = data['date']?.toString() ?? '';
      todayLectures = data['lectures'] as List<dynamic>? ?? [];
      _isFromCache = false;
      await _cache.set(cacheKey, data);
      _finishLoading();
    } catch (e) {
      if (todayLectures.isEmpty) {
        final stale = await _cache.getStale(cacheKey);
        if (stale != null) {
          final data = stale as Map<String, dynamic>;
          todayDay = data['day']?.toString() ?? '';
          todayDate = data['date']?.toString() ?? '';
          todayLectures = List<dynamic>.from(data['lectures'] as List? ?? []);
          _isFromCache = true;
          _finishLoading();
        } else {
          _finishWithError(_friendlyError(e));
        }
      } else {
        _isFromCache = true;
        _finishLoading();
      }
    }
  }

  // ── Helpers ──────────────────────────────────────────────────────
  void _startLoading() {
    isLoading = true;
    error = null;
    notifyListeners();
  }

  void _finishLoading() {
    isLoading = false;
    error = null;
    notifyListeners();
  }

  void _finishWithError(String msg) {
    isLoading = false;
    error = msg;
    notifyListeners();
  }

  String _friendlyError(Object e) {
    if (e is ApiException) return e.message;
    return 'تعذر الاتصال بالخادم';
  }
}

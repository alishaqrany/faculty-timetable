import 'dart:convert';

import 'package:shared_preferences/shared_preferences.dart';

/// Simple cache layer on top of SharedPreferences.
///
/// Each cache entry is stored as a JSON string containing:
/// - `_ts` : millisecondsSinceEpoch when saved
/// - `_ttl`: TTL in milliseconds
/// - `_data`: the actual payload
class CacheManager {
  CacheManager._();
  static final CacheManager instance = CacheManager._();

  static const String _prefix = 'cache_';

  // ── Default TTLs ─────────────────────────────────────────────────
  static const Duration lookupsTtl = Duration(hours: 24);
  static const Duration timetableTtl = Duration(hours: 1);

  // ── Public API ───────────────────────────────────────────────────

  /// Retrieve a cached value. Returns `null` if missing or expired.
  Future<dynamic> get(String key) async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString('$_prefix$key');
    if (raw == null) return null;

    try {
      final envelope = jsonDecode(raw) as Map<String, dynamic>;
      final ts = envelope['_ts'] as int;
      final ttl = envelope['_ttl'] as int;
      final now = DateTime.now().millisecondsSinceEpoch;

      if (now - ts > ttl) {
        // Expired — remove and return null
        await prefs.remove('$_prefix$key');
        return null;
      }
      return envelope['_data'];
    } catch (_) {
      return null;
    }
  }

  /// Retrieve a cached value even if expired (for offline fallback).
  Future<dynamic> getStale(String key) async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString('$_prefix$key');
    if (raw == null) return null;

    try {
      final envelope = jsonDecode(raw) as Map<String, dynamic>;
      return envelope['_data'];
    } catch (_) {
      return null;
    }
  }

  /// Store a value with a given TTL.
  Future<void> set(String key, dynamic value, {Duration ttl = timetableTtl}) async {
    final prefs = await SharedPreferences.getInstance();
    final envelope = {
      '_ts': DateTime.now().millisecondsSinceEpoch,
      '_ttl': ttl.inMilliseconds,
      '_data': value,
    };
    await prefs.setString('$_prefix$key', jsonEncode(envelope));
  }

  /// Remove a specific cache entry.
  Future<void> invalidate(String key) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('$_prefix$key');
  }

  /// Remove all cache entries (e.g. on logout).
  Future<void> clearAll() async {
    final prefs = await SharedPreferences.getInstance();
    final keys = prefs.getKeys().where((k) => k.startsWith(_prefix)).toList();
    for (final key in keys) {
      await prefs.remove(key);
    }
  }

  // ── Key builders ────────────────────────────────────────────────

  static String lookupsKey(String type) => 'lookups_$type';

  static String yearTimetableKey(int deptId, int levelId) =>
      'timetable_dept${deptId}_level$levelId';

  static String sectionTimetableKey(int sectionId) =>
      'section_timetable_$sectionId';

  static String todayLecturesKey({int? sectionId, int? deptId, int? levelId}) {
    if (sectionId != null) return 'today_sec_$sectionId';
    return 'today_dept${deptId}_level$levelId';
  }
}

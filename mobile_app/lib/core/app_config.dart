import 'package:flutter/foundation.dart';

class AppConfig {
  // ── بيئة التشغيل (Environment) ──────────────────────────────────
  // قم بتغيير هذه القيمة إلى true عند رفع التطبيق للإنتاج (Play Store / App Store)
  static const bool isProduction = false;

  // ── روابط الخادم (URLs) ────────────────────────────────────────
  // 1. رابط الخادم الفعلي (الاستضافة)
  static const String _productionUrl = 'https://your-domain.com/timetable';

  // 2. رابط بيئة التطوير المحلية
  static const String _developmentUrl = kIsWeb
      ? 'http://localhost/timetable'
      : 'http://10.0.2.2/timetable';

  // ── الرابط النشط حالياً ────────────────────────────────────────
  static const String apiBaseUrl = isProduction ? _productionUrl : _developmentUrl;
}

# تطبيق الجداول الدراسية — Flutter Mobile App

تطبيق هاتف مبني بـ Flutter متكامل مع نظام إدارة الجداول الدراسية (PHP MVC + MySQL) عبر واجهات API v1.

## المميزات

- **تسجيل الدخول** مع إدارة الجلسة عبر Provider
- **جدول الفرقة** — عرض الجدول حسب القسم والمستوى
- **جدول السكشن** — عرض الجدول حسب السكشن
- **محاضرات اليوم** — عرض محاضرات اليوم مع فلترة بالسكشن
- **تخزين مؤقت محلي** — تخزين بيانات البحث لتقليل طلبات الشبكة
- **تصميم Material 3** مع دعم كامل للغة العربية (RTL)

## التقنيات

- Flutter stable / Dart 3.11+
- Provider لإدارة الحالة
- Material 3 Design
- HTTP client مع إدارة التوكن
- SharedPreferences للتخزين المحلي

## هيكل المشروع

```text
lib/
├── main.dart                       نقطة الدخول
├── core/
│   ├── api_client.dart             عميل HTTP
│   ├── app_config.dart             إعدادات التطبيق
│   ├── app_theme.dart              ثيم Material 3
│   ├── cache_manager.dart          إدارة التخزين المؤقت
│   ├── session_store.dart          تخزين الجلسة
│   └── widgets/                    عناصر واجهة مشتركة
│       ├── empty_state_widget.dart
│       ├── error_state_widget.dart
│       ├── lecture_card.dart
│       └── loading_overlay.dart
└── features/
    ├── auth/                       المصادقة
    │   ├── auth_provider.dart
    │   ├── auth_service.dart
    │   └── login_page.dart
    ├── home/                       الصفحة الرئيسية
    │   └── home_page.dart
    └── student/                    شاشات الطالب
        ├── lookups_api.dart
        ├── student_api.dart
        ├── timetable_provider.dart
        ├── year_timetable_page.dart
        ├── section_timetable_page.dart
        └── today_lectures_page.dart
```

## التشغيل

### متطلبات

- Flutter SDK (stable channel)
- Dart 3.11+
- خادم PHP يعمل مع نظام الجداول

### الخطوات

```bash
cd mobile_app
flutter pub get
flutter run
```

### إعداد الخادم

- عدّل `lib/core/app_config.dart` لتحديد عنوان الخادم.
- لمحاكي Android: استخدم `10.0.2.2` بدلاً من `localhost`.
- لجهاز فعلي: استخدم عنوان IP الخادم على الشبكة المحلية.

## التحقق

```bash
flutter analyze    # تحليل الكود
flutter test       # تشغيل الاختبارات
```

## التوثيق

- [مواصفات API v1](../docs/mobile-api-v1-spec.md)
- [مواصفات التطبيق](../docs/mobile-app-spec-ar.md)
- [خطة التطوير](../docs/mobile-app-development-plan-ar.md)

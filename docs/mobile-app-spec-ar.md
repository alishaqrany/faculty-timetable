# مواصفات تطبيق الهاتف (Flutter) لنظام الجداول

## 1) نظرة عامة

هذا المستند يحدد المواصفات الرسمية لتطبيق الهاتف المبني بـ Flutter والمتكامل مع نظام الجداول الحالي (PHP MVC + MySQL) عبر واجهات `API v1`.

الهدف الأساسي:
- تمكين الطالب من عرض:
  - جدول الفرقة.
  - جدول السكشن.
  - محاضرات اليوم.
- تمكين الإدارة من تنفيذ عمليات عالية الأثر من الموبايل بصورة آمنة وتدريجية.

## 2) النطاق الوظيفي

### 2.1 وظائف الطالب (منفذة في الإصدار الحالي)

- تسجيل الدخول مع إدارة الجلسة عبر Provider.
- تصفح الأقسام والفرق والسكاشن.
- عرض جدول الفرقة (Department + Level).
- عرض جدول السكشن (Section).
- عرض محاضرات اليوم مع فلترة اختيارية بالسكشن.
- تخزين مؤقت محلي (CacheManager) لبيانات البحث.

### 2.2 وظائف الإدارة (API phase 1 منفذة)

CRUD عبر API للكيانات:
- Departments
- Subjects
- Sections
- Sessions

ملاحظة: واجهات Flutter الإدارية التفصيلية ما زالت في مرحلة التطوير اللاحقة، بينما طبقة API الإدارية الأساسية متاحة.

## 3) الأدوار والصلاحيات

- Student/Faculty/Viewer: يستهلكون endpoints القراءة حسب التوثيق والصلاحية.
- Admin/DeptHead: يستطيعون استخدام `/api/v1/admin/*`.

سياسة الصلاحية الحالية:
- middleware: `ApiRoleMiddleware`
- الأدوار المسموح لها في admin API: `admin`, `dept_head`

## 4) المواصفات التقنية

### 4.1 Backend

- لغة: PHP 8+
- قاعدة بيانات: MySQL
- Router: مخصص داخل `core/Router.php`
- API Auth: Bearer token مخزن بشكل hash في `api_tokens`
- Rate Limiting: عبر `RateLimitMiddleware`

### 4.2 Mobile

- Flutter stable
- Dart 3.11+
- إدارة الحالة: Provider
- تصميم: Material 3 مع دعم عربي (RTL)
- مكتبات مستخدمة:
  - `http` — طلبات الشبكة
  - `shared_preferences` — تخزين محلي
  - `intl` — تنسيق التواريخ والأرقام
  - `provider` — إدارة الحالة

### 4.3 هيكل تطبيق Flutter الحالي

```text
lib/
├── main.dart                       نقطة الدخول مع إعداد Provider
├── core/
│   ├── api_client.dart             عميل HTTP مع إدارة التوكن
│   ├── app_config.dart             إعدادات التطبيق (URL, timeout)
│   ├── app_theme.dart              ثيم Material 3 مع دعم عربي
│   ├── cache_manager.dart          إدارة التخزين المؤقت المحلي
│   ├── session_store.dart          تخزين جلسة المستخدم
│   └── widgets/
│       ├── empty_state_widget.dart  عنصر حالة فارغة
│       ├── error_state_widget.dart  عنصر حالة خطأ
│       ├── lecture_card.dart        بطاقة محاضرة
│       └── loading_overlay.dart    مؤشر تحميل
├── features/
│   ├── auth/
│   │   ├── auth_provider.dart      Provider لإدارة المصادقة
│   │   ├── auth_service.dart       خدمة المصادقة
│   │   └── login_page.dart         صفحة تسجيل الدخول
│   ├── home/
│   │   └── home_page.dart          الصفحة الرئيسية
│   └── student/
│       ├── lookups_api.dart        واجهة بيانات البحث
│       ├── student_api.dart        واجهة بيانات الطالب
│       ├── timetable_provider.dart Provider لإدارة الجداول
│       ├── year_timetable_page.dart صفحة جدول الفرقة
│       ├── section_timetable_page.dart صفحة جدول السكشن
│       └── today_lectures_page.dart صفحة محاضرات اليوم
```

## 5) مواصفات API

المرجع الرسمي: `docs/mobile-api-v1-spec.md`

عقد الاستجابة:
- Success: `success`, `message`, `data`, `meta`
- Error: `success=false`, `message`, `errors`

Endpoints الرئيسية:
- Auth: `/api/v1/auth/*`, `/api/v1/me`
- Student: `/api/v1/students/*`
- Lookups: `/api/v1/lookups/*`
- Admin: `/api/v1/admin/*`

## 6) الأداء

تمت إضافة فهارس DB لتحسين أداء استعلامات الجداول:
- `subjects(department_id, level_id)`
- `sessions(day, start_time)`
- `member_courses(section_id)`
- `member_courses(subject_id)`
- `timetable(member_course_id, session_id)`

### التخزين المؤقت المحلي

- `CacheManager` يخزن بيانات lookups محلياً.
- يقلل عدد طلبات الشبكة عند تكرار الوصول.
- تحديث تلقائي عند انتهاء صلاحية البيانات.

## 7) الأمان

- مصادقة token-based.
- تخزين token hashed في السيرفر.
- صلاحيات admin API عبر middleware مخصص (`ApiRoleMiddleware`).
- Rate limiting مطبق على جميع مجموعات API.
- حماية CSRF للمسارات الويب.

## 8) متطلبات الاختبار

### 8.1 Backend

- فحص syntax بــ `php -l` على الملفات الجديدة.
- اختبار smoke endpoints:
  - login → me
  - student timetable
  - today lectures
  - admin list endpoints

### 8.2 Mobile

- `flutter analyze` بدون مشاكل.
- `flutter test` لسيناريو إقلاع التطبيق وشاشة الدخول.

## 9) متطلبات التشغيل

- XAMPP (Apache + MySQL) يعمل.
- مسار النظام المحلي: `http://localhost/timetable`
- لمحاكي Android: استخدام `10.0.2.2` في `app_config.dart`.

## 10) قيود الإصدار الحالي

- حفظ token حالياً عبر `shared_preferences` (يمكن ترقية ذلك إلى secure storage في الإصدارات اللاحقة).
- شاشات الإدارة في Flutter لم تكتمل بعد رغم جاهزية API الأساسية.
- تخزين مؤقت أساسي عبر CacheManager (يمكن تعزيزه بـ offline mode كامل).

## 11) معايير القبول

يُعد التطبيق مطابقاً للمواصفات الحالية إذا:
- المستخدم يسجل الدخول بنجاح عبر Provider.
- تظهر شاشات الطالب الثلاث وتعرض بيانات فعلية.
- التخزين المؤقت يعمل لتقليل طلبات الشبكة.
- العناصر المشتركة (empty state, error state, loading) تعمل بشكل صحيح.
- endpoints `api/v1` تعمل بعقد موحد.
- admin APIs الأساسية تعمل مع صلاحيات صحيحة.
- التحليل والاختبارات الآلية تمر بدون أخطاء.

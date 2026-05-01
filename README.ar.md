# نظام إدارة الجدول الدراسي

## الفهرس

- [نظرة عامة](#نظرة-عامة)
- [أهم المميزات](#أهم-المميزات)
- [التقنيات المستخدمة](#التقنيات-المستخدمة)
- [البنية المعمارية](#البنية-المعمارية)
- [هيكل المشروع](#هيكل-المشروع)
- [التشغيل المحلي](#التشغيل-المحلي)
- [لقطات الشاشة](#لقطات-الشاشة)
- [الوحدات الرئيسية](#الوحدات-الرئيسية)
- [نظام الأولوية](#نظام-الأولوية)
- [الفلترة الديناميكية AJAX](#الفلترة-الديناميكية-ajax)
- [دورة الاستخدام](#دورة-الاستخدام)
- [الصلاحيات RBAC](#الصلاحيات-rbac)
- [ملخص واجهات API](#ملخص-واجهات-api)
- [تطبيق الهاتف](#تطبيق-الهاتف)
- [ملاحظات مهمة](#ملاحظات-مهمة)
- [توثيق إضافي](#توثيق-إضافي)

## نظرة عامة

هذا المشروع هو نظام لإدارة الجدول الدراسي الجامعي مبني على إطار MVC مخصص بلغة PHP وقاعدة بيانات MySQL. يدعم النظام دورة الإدارة الأكاديمية الكاملة: إعداد البيانات المرجعية، توزيع المواد، الجدولة بنظام الأولوية مع منع التعارضات، عرض الجدول وتصديره، التقارير، الإشعارات، النسخ الاحتياطي السحابي، والوصول عبر تطبيق الهاتف.

يحتوي المستودع على:

- تطبيق PHP 8 بأسلوب MVC داخل `app/` و `core/` و `config/` و `public/`
- تطبيق هاتف Flutter داخل `mobile_app/`
- 35 ملف ترحيل قاعدة بيانات داخل `database/migrations/`
- نسخة إجرائية قديمة داخل `_legacy/` للرجوع إليها عند الحاجة

نقطة الدخول الفعلية هي `public/index.php` التي تعمل كـ Front Controller لتوجيه جميع المسارات.

## أهم المميزات

- **بنية MVC مخصصة** — موجه مسارات، سلسلة Middleware، متحكم أساسي، نموذج ORM، ومحرك قوالب
- **نظام صلاحيات RBAC** — 4 أدوار مع أكثر من 50 صلاحية تفصيلية بنمط `module.action`
- **نظام أولوية متقدم** — 4 أوضاع جدولة (معطل، عام، متوازي بالقسم، تسلسلي بالقسم) مع تصنيفات ومجموعات واستثناءات
- **فلترة AJAX ديناميكية** — فلترة صفحات الجدول والجدولة بدون إعادة تحميل مع إدارة تاريخ URL
- **هرمية الشُعب والسكاشن** — الشُعب (divisions) تحتوي على السكاشن (sections) لتنظيم مرن
- **سياق أكاديمي** — إدارة السنوات الأكاديمية والفصول الدراسية مع تتبع الفترة الحالية
- **نقل البيانات** — استيراد وتصدير SQL و Excel مع دعم البيانات النموذجية
- **النسخ الاحتياطي والمزامنة السحابية** — نسخ احتياطي محلي + تكامل مع Google Drive و Supabase و Firebase
- **التقارير والإحصائيات** — لوحات إحصائية مع رسوم بيانية وتقارير مفصلة
- **سجلات التدقيق** — تتبع كامل للعمليات الإدارية
- **الإشعارات** — نظام إشعارات داخلي مع تتبع حالة القراءة
- **واجهة API للموبايل (v1)** — واجهات RESTful مع مصادقة Bearer token وتحديد معدل الطلبات
- **تطبيق Flutter** — عرض جداول الطلاب مع Provider لإدارة الحالة وMaterial 3 وتخزين مؤقت محلي
- **التصدير** — تصدير PDF و Excel للجدول

## التقنيات المستخدمة

- PHP 8.0 أو أحدث
- MySQL أو MariaDB
- إطار MVC مخصص (Router, Controller, Model, View, Request, Session, Database)
- سلسلة Middleware (Auth, CSRF, RBAC, API Auth, API Role, Rate Limiting)
- طبقة قاعدة بيانات MySQLi مع Prepared Statements و Transactions
- واجهة AdminLTE 3 مع دعم RTL
- Flutter (Dart 3.11+) لتطبيق الهاتف
- XAMPP أو Apache أو أي استضافة PHP متوافقة

## البنية المعمارية

النظام يتبع نمط MVC مُصمت (Monolith):

```
طلب المتصفح
  → public/index.php (Front Controller)
  → core/Application.php (التهيئة)
  → core/Router.php (مطابقة المسارات)
  → سلسلة Middleware (Auth → CSRF → RBAC)
  → app/Controllers/*Controller.php
  → app/Models/*.php + app/Services/*.php
  → app/Views/ (مع القوالب والأجزاء المشتركة)
  → الاستجابة
```

## هيكل المشروع

```text
app/
├── Controllers/            المتحكمات (24 متحكم ويب + 7 متحكم API)
│   └── Api/
│       ├── AuthController.php
│       ├── TimetableController.php
│       └── V1/             متحكمات API v1 للموبايل
├── Models/                 22 نموذج بيانات
├── Services/               خدمات المنطق التجاري
│   ├── PriorityService.php
│   ├── SchedulingService.php
│   ├── ExportService.php
│   ├── BackupService.php
│   ├── DataTransferService.php
│   ├── AuditService.php
│   ├── NotificationService.php
│   └── Cloud/              تكامل السحابة
│       ├── GoogleDriveService.php
│       ├── SupabaseService.php
│       └── FirebaseService.php
├── Middleware/             6 ملفات Middleware
├── Views/                  26 مجلد عرض مع القوالب والأجزاء المشتركة
├── Helpers/
│   └── functions.php       دوال مساعدة
config/
├── app.php                 اسم التطبيق والإصدار والمسار والمنطقة الزمنية
├── database.php            إعدادات قاعدة البيانات
├── routes.php              جميع المسارات (~200 سطر)
├── permissions.php         خريطة صلاحيات RBAC (50+ صلاحية)
└── cloud.php               إعدادات مزودي السحابة
core/
├── Application.php         التهيئة وتسجيل المسارات والإرسال
├── Router.php              مطابقة URL ومجموعات المسارات
├── Controller.php          الأساس: view(), redirect(), json(), authorize()
├── Model.php               الأساس: find(), all(), create(), update(), delete(), paginate()
├── Database.php            MySQLi مع prepared statements + transactions
├── Request.php             معالجة المدخلات والتحقق
├── Session.php             إدارة الجلسات والرسائل المؤقتة
├── View.php                عرض القوالب مع التخطيطات والأقسام
├── auth.php                ملف توافق قديم
├── bootstrap.php           ملف توافق قديم
├── csrf.php                ملف توافق قديم
└── flash.php               ملف توافق قديم
database/
├── migrations/             35 ملف ترحيل (001–035)
├── seeds/                  البيانات الأساسية الافتراضية
└── migrate_legacy_data.php أداة ترحيل البيانات القديمة
docs/                       ملفات التوثيق
mobile_app/                 تطبيق الهاتف Flutter
public/
├── index.php               المتحكم الأمامي
├── install.php             معالج التثبيت
├── .htaccess               قواعد إعادة توجيه URL
└── assets/                 ملفات CSS و JS
storage/
├── backups/                ملفات النسخ الاحتياطي
├── exports/                ملفات التصدير
├── logs/                   سجلات التطبيق
├── cache/                  ملفات الذاكرة المؤقتة
├── import_samples/         ملفات استيراد نموذجية
└── rate_limits/            بيانات تحديد المعدل
_legacy/                    الكود الإجرائي القديم (مرجع فقط)
```

## التشغيل المحلي

1. ضع المشروع داخل مجلد الويب المحلي، مثل `c:/xampp/htdocs/timetable`.
2. شغّل Apache و MySQL.
3. تأكد من توفر PHP 8.0 أو أحدث.
4. افتح معالج التثبيت:

   `http://localhost/timetable/install.php`

5. أكمل خطوات التثبيت:
   - فحص المتطلبات
   - إعداد قاعدة البيانات
   - تشغيل ملفات الترحيل والبذور
   - إنشاء حساب المدير
   - حفظ إعدادات النظام
6. افتح الصفحة الرئيسية العامة:

   `http://localhost/timetable/`

7. سجّل الدخول إلى لوحة الإدارة:

   `http://localhost/timetable/login`

## لقطات الشاشة

اللقطات التالية أُخذت من بيئة محلية تعمل فعليًا.

### صفحة الدخول

![لقطة صفحة الدخول المحلية](docs/screenshots/local-login.png)

### لوحة التحكم

![لقطة لوحة التحكم المحلية](docs/screenshots/local-dashboard.png)

### أعضاء هيئة التدريس

![لقطة صفحة أعضاء هيئة التدريس](docs/screenshots/local-members.png)

### الأقسام

![لقطة صفحة الأقسام](docs/screenshots/local-departments.png)

## الوحدات الرئيسية

| الوحدة | المتحكم | الوصف |
|--------|---------|-------|
| الصفحة الرئيسية | `HomeController` | الصفحة العامة للزوار |
| المصادقة | `AuthController` | تسجيل الدخول والخروج والملف الشخصي |
| لوحة التحكم | `DashboardController` | لوحة إدارية مع إحصائيات |
| الأقسام | `DepartmentController` | إدارة الأقسام الأكاديمية |
| المستويات | `LevelController` | إدارة الفرق الدراسية + بذر القيم الافتراضية |
| أعضاء هيئة التدريس | `MemberController` | إدارة بيانات الأعضاء |
| المواد | `SubjectController` | إدارة المقررات الدراسية |
| الشُعب | `DivisionController` | إدارة الشُعب (divisions) |
| السكاشن | `SectionController` | إدارة السكاشن + التوليد التلقائي |
| القاعات | `ClassroomController` | إدارة قاعات التدريس |
| الفترات | `SessionController` | إدارة الفترات الزمنية + التوليد الجماعي |
| توزيع المواد | `MemberCourseController` | إدارة تكليفات التدريس |
| الجدولة | `SchedulingController` | تسكين المواد مع منع التعارضات + فلترة AJAX |
| الأولوية | `PriorityController` | أوضاع الأولوية والتصنيفات والمجموعات والاستثناءات |
| الجدول النهائي | `TimetableController` | عرض الجدول وتصديره + فلترة AJAX |
| التقارير | `ReportsController` | تقارير إحصائية وتحليلات |
| السنوات الأكاديمية | `AcademicYearController` | إدارة السنوات الأكاديمية |
| الفصول الدراسية | `SemesterController` | إدارة الفصول + التوليد التلقائي |
| المستخدمون | `UserController` | إدارة حسابات المستخدمين |
| سجلات التدقيق | `AuditLogController` | سجل العمليات الإدارية (قراءة فقط) |
| الإشعارات | `NotificationController` | إدارة الإشعارات والرسائل |
| الإعدادات | `SettingController` | إعدادات النظام |
| نقل البيانات | `DataTransferController` | استيراد وتصدير SQL/Excel وبيانات نموذجية وإعادة تعيين |
| النسخ الاحتياطي | `BackupController` | نسخ احتياطي محلي + مزامنة سحابية |

## نظام الأولوية

يدعم النظام 4 أوضاع للجدولة:

| الوضع | الوصف |
|-------|-------|
| معطل | جدولة مفتوحة بدون قيود أولوية |
| عام | ترتيب الجدولة يتبع تقدم التصنيفات/المجموعات عالميًا |
| متوازي بالقسم | كل قسم يجدول مستقلًا حسب ترتيب مجموعاته |
| تسلسلي بالقسم | الأقسام تتناوب على الجدولة واحدًا تلو الآخر |

**المفاهيم الأساسية:**
- **التصنيفات** — مجموعات كالأستاذ والأستاذ المساعد
- **المجموعات** — مجموعات مرتبة داخل كل تصنيف
- **الاستثناءات** — استثناءات فردية أو جماعية تتجاوز ترتيب الأولوية
- **التسجيل المباشر** — المدير يمنح أو يسحب صلاحية التسجيل مباشرة
- **ترتيب الأقسام** — يتحكم في تسلسل الأقسام في الوضع التسلسلي

## الفلترة الديناميكية AJAX

صفحات الجدول والجدولة تدعم فلترة AJAX ديناميكية:
- فلترة النتائج بدون إعادة تحميل الصفحة
- تحديث URL عبر `pushState` لحالات فلترة قابلة للمشاركة والإشارة المرجعية
- مؤشرات تحميل أثناء جلب البيانات
- المسارات: `GET /timetable/ajax-filter`، `GET /scheduling/ajax-filter`

## الصلاحيات RBAC

| الدور | الوصف |
|-------|-------|
| `admin` — مدير النظام | وصول كامل لجميع الوحدات |
| `dept_head` — رئيس قسم | إدارة نطاق قسمه |
| `faculty` — عضو هيئة تدريس | جدولة مقرراته + عرض الجدول |
| `viewer` — مراقب | قراءة فقط بدون تعديل |

الصلاحيات تتبع نمط `module.action` (مثل `departments.view`، `scheduling.manage`، `priority.grant_exception`). هناك أكثر من 50 صلاحية تفصيلية في `config/permissions.php`.

## دورة الاستخدام

1. تثبيت النظام عبر معالج التثبيت.
2. تسجيل الدخول بحساب المدير.
3. إنشاء الأقسام والمستويات والشُعب والسكاشن والمواد والقاعات والفترات.
4. إضافة أعضاء هيئة التدريس وحسابات المستخدمين.
5. توزيع المواد على الأعضاء (تكليفات التدريس).
6. إعداد نظام الأولوية (الأوضاع، التصنيفات، المجموعات، ترتيب الأقسام).
7. فتح الجدولة ليقوم الأعضاء بتسكين المواد.
8. تقديم المجموعات/الأقسام التالية عند اكتمال عمل الحاليين.
9. مراجعة الجدول النهائي أو تصديره.

## ملخص واجهات API

### واجهة API القديمة (`/api/*`)

مسار المصادقة العام:
- `POST /api/auth/login`

المسارات المحمية (Bearer Token):
- `GET /api/timetable`
- `GET /api/departments`
- `GET /api/members`
- `GET /api/classrooms`
- `GET /api/subjects`
- `GET /api/sections`
- `GET /api/sessions`

### واجهة API v1 للموبايل (`/api/v1/*`)

المصادقة:
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout` (يتطلب مصادقة)
- `GET /api/v1/me` (يتطلب مصادقة)

مسارات الطالب (تتطلب مصادقة):
- `GET /api/v1/students/timetable?department_id=&level_id=`
- `GET /api/v1/students/section-timetable?section_id=`
- `GET /api/v1/students/today-lectures?section_id=&date=`

مسارات البحث (تتطلب مصادقة):
- `GET /api/v1/lookups/departments`
- `GET /api/v1/lookups/levels`
- `GET /api/v1/lookups/sections`
- `GET /api/v1/lookups/subjects`
- `GET /api/v1/lookups/sessions`
- `GET /api/v1/lookups/classrooms`

مسارات الإدارة (تتطلب مصادقة + دور admin/dept_head):
- الأقسام: `GET|POST /api/v1/admin/departments`، `POST .../departments/{id}`، `POST .../departments/{id}/delete`
- المواد: نفس نمط CRUD
- السكاشن: نفس نمط CRUD
- الفترات: نفس نمط CRUD

أعراف الاستعلام: `page`، `per_page` (حد أقصى 100)، `q` (بحث نصي)

جميع مسارات API محددة المعدل. المسارات المحمية تحتاج `Authorization: Bearer <token>`.

## تطبيق الهاتف

تطبيق Flutter الموجود في `mobile_app/` يوفر:

- **وظائف الطالب** — جدول الفرقة، جدول السكشن، محاضرات اليوم
- **البنية** — Provider لإدارة الحالة، تصميم Material 3، واجهة عربية
- **تخزين مؤقت محلي** — تخزين بيانات البحث عبر `CacheManager`
- **عناصر واجهة مشتركة** — مؤشرات تحميل، حالات فارغة، حالات خطأ، بطاقات محاضرات

### هيكل تطبيق Flutter

```text
mobile_app/lib/
├── main.dart
├── core/
│   ├── api_client.dart
│   ├── app_config.dart
│   ├── app_theme.dart
│   ├── cache_manager.dart
│   ├── session_store.dart
│   └── widgets/
│       ├── empty_state_widget.dart
│       ├── error_state_widget.dart
│       ├── lecture_card.dart
│       └── loading_overlay.dart
└── features/
    ├── auth/
    │   ├── auth_provider.dart
    │   ├── auth_service.dart
    │   └── login_page.dart
    ├── home/
    │   └── home_page.dart
    └── student/
        ├── lookups_api.dart
        ├── student_api.dart
        ├── timetable_provider.dart
        ├── year_timetable_page.dart
        ├── section_timetable_page.dart
        └── today_lectures_page.dart
```

لمحاكي Android: استخدم `10.0.2.2` بدلًا من `localhost` في `app_config.dart`.

## ملاحظات مهمة

- يقوم معالج التثبيت بحفظ إعدادات قاعدة البيانات داخل `config/database.php`.
- بعض الوظائف تعتمد على صلاحية كتابة داخل `storage/` مثل النسخ الاحتياطي والتصدير والسجلات وتحديد المعدل.
- إعدادات المزامنة السحابية توجد في `config/cloud.php`.
- مجلد `core/` يحتوي على فئات MVC الجديدة وملفات توافق قديمة (`auth.php`، `bootstrap.php`، `csrf.php`، `flash.php`).
- إصدار التطبيق الحالي `2.0.0` كما هو معرّف في `config/app.php`.

## توثيق إضافي

- [English Guide](README.en.md)
- [توثيق الموقع الكامل](docs/complete-site-documentation-ar.md)
- [مخطط البنية المعمارية](docs/site-blueprint-ar.md)
- [خريطة توافق الروابط](docs/url-compatibility-map.md)
- [مواصفات API الموبايل v1](docs/mobile-api-v1-spec.md)
- [مواصفات تطبيق الهاتف](docs/mobile-app-spec-ar.md)
- [خطة تطوير تطبيق الهاتف](docs/mobile-app-development-plan-ar.md)
# التوثيق الكامل لموقع إدارة الجدول الدراسي

## 1) نظرة عامة

هذا الموقع نظام ويب لإدارة بناء جدول دراسي جامعي بشكل تدريجي ومنظم، بدءاً من إعداد الكيانات الأساسية (الأقسام، المستويات، الشُعب، السكاشن، المواد، القاعات، الفترات)، ثم توزيع المواد على أعضاء هيئة التدريس، ثم تسكين كل مادة في قاعة وفترة زمنية مع قيود تمنع التعارض، وصولاً إلى التصدير والتقارير والنسخ الاحتياطي.

النظام مبني بإطار MVC مخصص بلغة PHP 8+ وقاعدة بيانات MySQL/MariaDB عبر MySQLi مع Prepared Statements، ويعمل عادة على XAMPP أو أي استضافة PHP متوافقة.

## 2) الفكرة التي يقوم عليها الموقع

### 2.1 المشكلة التي يحلها

تكوين جدول دراسي يدوياً غالباً يسبب:
- تعارضات قاعات.
- تعارضات زمنية داخل نفس الفرقة/القسم.
- صعوبة تتبع من له الحق في إدخال/تعديل التسكين.
- عدم وجود نظام أولوية لتنظيم ترتيب الإدخال.

### 2.2 منهج الحل في النظام

النظام يعتمد على 4 مراحل:
- **مرحلة التهيئة**: إدخال البيانات المرجعية (الأقسام/المستويات/الشُعب/السكاشن/المواد/القاعات/الفترات).
- **مرحلة الربط الأكاديمي**: ربط عضو هيئة تدريس بمادة وسكشن/شعبة (member_courses).
- **مرحلة إعداد الأولوية**: تكوين التصنيفات والمجموعات وترتيب الأقسام.
- **مرحلة التسكين**: اختيار (مادة عضو) + (قاعة) + (فترة) وتسجيلها في timetable مع التحقق من عدم التعارض.

### 2.3 فلسفة الصلاحيات

- النظام يعتمد على RBAC بأربعة أدوار (admin, dept_head, faculty, viewer).
- أكثر من 50 صلاحية تفصيلية بنمط `module.action`.
- إدخال التسكين مقيد بنظام الأولوية (PriorityService) الذي يوفر 4 أوضاع للجدولة.
- تعديل/حذف سجل تسكين مقيد بملكية السجل (owner_member_id).
- حماية المسارات عبر AuthMiddleware و RbacMiddleware.

## 3) الجمهور المستهدف

- **مدير النظام**: إعداد أولي + إدارة كل الكيانات + إدارة الأولوية.
- **رئيس قسم**: بناء الهيكل الأكاديمي لقسمه وإتمام التسكين.
- **عضو هيئة تدريس**: تسكين مقرراته فقط + عرض الجدول.
- **مراقب**: عرض فقط بدون تعديل.
- **طالب** (عبر تطبيق الهاتف أو API): عرض جداول الفرق والسكاشن ومحاضرات اليوم.

## 4) دورة حياة النظام (من الصفر إلى التشغيل)

1. فتح `http://localhost/timetable/install.php` لتشغيل معالج التثبيت.
2. إعداد قاعدة البيانات (تشغيل الترحيلات والبذور).
3. إنشاء حساب المدير.
4. تسجيل الدخول عبر `/login`.
5. من لوحة التحكم `/dashboard`:
   - إضافة الأقسام.
   - إضافة المستويات (مع خيار بذر القيم الافتراضية).
   - إضافة أعضاء هيئة التدريس.
   - إنشاء الفترات الزمنية (مع خيار التوليد الجماعي).
   - إضافة القاعات.
   - إنشاء الشُعب.
   - إضافة السكاشن (مع خيار التوليد التلقائي).
   - إضافة المواد.
   - توزيع المواد على الأعضاء (تكليفات التدريس).
   - إعداد نظام الأولوية.
   - فتح شاشة الجدولة للتسكين.
   - عرض الجدول النهائي وتصديره.
   - عرض التقارير والإحصائيات.

## 5) الوحدات والشاشات (Feature Modules)

### 5.1 الصفحة الرئيسية (Home)
- المتحكم: `HomeController@index`
- المسار: `GET /`
- صفحة عامة للزوار بدون تسجيل دخول

### 5.2 المصادقة (Auth)
- المتحكم: `AuthController`
- المسارات:
  - `GET /login` — صفحة تسجيل الدخول
  - `POST /login` — معالجة تسجيل الدخول
  - `POST /logout` — تسجيل الخروج
  - `GET /profile` — الملف الشخصي
  - `POST /profile` — تحديث الملف الشخصي

### 5.3 لوحة التحكم (Dashboard)
- المتحكم: `DashboardController@index`
- المسار: `GET /dashboard`
- إحصائيات ورسوم بيانية وملخص النظام

### 5.4 الأقسام (Departments)
- المتحكم: `DepartmentController`
- المسارات: CRUD كامل عبر `$router->resource('/departments', ...)`

### 5.5 المستويات (Levels)
- المتحكم: `LevelController`
- المسارات: CRUD كامل + `POST /levels/seed-defaults` لبذر القيم الافتراضية

### 5.6 أعضاء هيئة التدريس (Members)
- المتحكم: `MemberController`
- المسارات: CRUD كامل عبر `$router->resource('/members', ...)`

### 5.7 المواد (Subjects)
- المتحكم: `SubjectController`
- المسارات: CRUD كامل عبر `$router->resource('/subjects', ...)`

### 5.8 الشُعب (Divisions)
- المتحكم: `DivisionController`
- المسارات: CRUD كامل + `GET /api/divisions/by-dept-level` (API داخلي)

### 5.9 السكاشن (Sections)
- المتحكم: `SectionController`
- المسارات: CRUD كامل + `POST /sections/generate-auto` للتوليد التلقائي + `GET /api/sections/by-division` (API داخلي)

### 5.10 القاعات (Classrooms)
- المتحكم: `ClassroomController`
- المسارات: CRUD كامل عبر `$router->resource('/classrooms', ...)`

### 5.11 الفترات (Sessions)
- المتحكم: `SessionController`
- المسارات: CRUD كامل + `POST /sessions/generate` للتوليد الجماعي + `POST /sessions/destroy-all` لحذف الكل

### 5.12 توزيع المواد على الأعضاء (Member Courses)
- المتحكم: `MemberCourseController`
- المسارات: CRUD كامل عبر `$router->resource('/member-courses', ...)`

### 5.13 الجدولة / التسكين (Scheduling)
- المتحكم: `SchedulingController`
- المسارات:
  - `GET /scheduling` — شاشة التسكين
  - `GET /scheduling/ajax-filter` — فلترة AJAX ديناميكية
  - `POST /scheduling` — إنشاء سجل تسكين
  - `GET /scheduling/{id}/edit` — تعديل سجل
  - `POST /scheduling/{id}` — تحديث سجل
  - `POST /scheduling/{id}/delete` — حذف سجل
  - `POST /scheduling/pass-role` — تمرير الدور

### 5.14 إدارة الأولوية (Priority Management)
- المتحكم: `PriorityController`
- خدمة الأولوية: `PriorityService`
- المسارات:
  - `GET /priority` — الواجهة الرئيسية والتحكم في الأوضاع
  - `POST /priority/mode` — تحديث وضع الأولوية
  - `POST /priority/set-category` — تعيين التصنيف النشط
  - التصنيفات: CRUD عبر `/priority/categories/*`
  - المجموعات: CRUD عبر `/priority/categories/{id}/groups` و `/priority/groups/*`
  - أعضاء المجموعات: `/priority/groups/{id}/members`
  - الاستثناءات: `/priority/exceptions`
  - ترتيب الأقسام: `/priority/dept-order`
  - تقدم المجموعات: `/priority/advance-group`، `/priority/advance-dept`
  - التسجيل المباشر: `/priority/grant-register/{id}`، `/priority/revoke-register/{id}`
  - إعادة التعيين: `/priority/reset`

### 5.15 عرض الجدول النهائي (Timetable)
- المتحكم: `TimetableController`
- المسارات:
  - `GET /timetable` — عرض الجدول
  - `GET /timetable/ajax-filter` — فلترة AJAX ديناميكية
  - `GET /timetable/export` — تصدير الجدول

### 5.16 التقارير والإحصائيات (Reports)
- المتحكم: `ReportsController@index`
- المسار: `GET /reports`

### 5.17 السنوات الأكاديمية (Academic Years)
- المتحكم: `AcademicYearController`
- المسارات: CRUD كامل + `POST /academic-years/{id}/set-current`

### 5.18 الفصول الدراسية (Semesters)
- المتحكم: `SemesterController`
- المسارات: CRUD كامل + `POST /semesters/{id}/set-current` + `GET /semesters/by-year` + `POST /semesters/generate-for-year`

### 5.19 المستخدمون (Users)
- المتحكم: `UserController`
- المسارات: CRUD كامل عبر `$router->resource('/users', ...)`

### 5.20 سجلات التدقيق (Audit Logs)
- المتحكم: `AuditLogController`
- المسارات: `GET /audit-logs`، `GET /audit-logs/{id}`

### 5.21 الإشعارات (Notifications)
- المتحكم: `NotificationController`
- المسارات:
  - `GET /notifications` — قائمة الإشعارات
  - `POST /notifications/send` — إرسال إشعار
  - `POST /notifications/{id}/read` — تعليم كمقروء
  - `POST /notifications/read-all` — تعليم الكل كمقروء
  - `POST /notifications/{id}/delete` — حذف إشعار

### 5.22 الإعدادات ونقل البيانات (Settings & Data Transfer)
- المتحكمات: `SettingController`، `DataTransferController`
- المسارات:
  - `GET /settings` — عرض الإعدادات
  - `POST /settings` — تحديث الإعدادات
  - نقل البيانات:
    - `GET /settings/data-transfer/export-sql` — تصدير SQL
    - `GET /settings/data-transfer/export-excel` — تصدير Excel
    - `GET /settings/data-transfer/sample-sql` — تنزيل عينة SQL
    - `GET /settings/data-transfer/sample-accounts` — عينة حسابات
    - `POST /settings/data-transfer/import-sample` — استيراد عينة
    - `POST /settings/data-transfer/import-sql` — استيراد SQL
    - `POST /settings/data-transfer/import-excel` — استيراد Excel
    - `POST /settings/data-transfer/reset-system` — إعادة تعيين النظام

### 5.23 النسخ الاحتياطي والمزامنة السحابية (Backups & Cloud Sync)
- المتحكم: `BackupController`
- الخدمات: `BackupService`، `GoogleDriveService`، `SupabaseService`، `FirebaseService`
- المسارات:
  - `GET /backups` — قائمة النسخ الاحتياطية
  - `POST /backups/create-sql` — إنشاء نسخة SQL
  - `POST /backups/create-excel` — إنشاء نسخة Excel
  - `GET /backups/download/{filename}` — تنزيل نسخة
  - `POST /backups/delete/{filename}` — حذف نسخة
  - `POST /backups/restore/{filename}` — استعادة نسخة
  - `POST /backups/upload-cloud/{filename}` — رفع إلى السحابة
  - إعدادات السحابة: `GET|POST /backups/cloud-settings`
  - Google Drive: مصادقة OAuth وتصفح الملفات
  - Supabase: مزامنة وتصفح
  - Firebase: مزامنة وتصفح
  - `POST /backups/download-from-cloud` — تنزيل من السحابة

## 6) نموذج البيانات (Database Model)

### الجداول الأساسية

| الجدول | الأعمدة الرئيسية |
|--------|-------------------|
| `settings` | key, value |
| `academic_years` | id, name, is_current, timestamps |
| `semesters` | id, name, academic_year_id, is_current, timestamps |
| `roles` | id, name, display_name, timestamps |
| `permissions` | id, slug, label |
| `role_permissions` | role_id, permission_id |
| `departments` | department_id, department_name, code, is_active, timestamps |
| `levels` | level_id, level_name, code, sort_order, timestamps |
| `academic_degrees` | id, degree_name, sort_order |
| `faculty_members` | member_id, member_name, email, phone, department_id, degree_id, is_active, timestamps |
| `users` | id, username, password, email, role_id, member_id, is_active, last_login_at, timestamps |
| `subjects` | subject_id, subject_name, code, department_id, level_id, hours, credit_hours, subject_type, timestamps |
| `divisions` | id, name, department_id, level_id, timestamps |
| `sections` | section_id, section_name, department_id, level_id, division_id, capacity, is_active, timestamps |
| `classrooms` | classroom_id, classroom_name, capacity, type, building, floor, is_active, timestamps |
| `sessions` | session_id, day, session_name, start_time, end_time, duration, is_active, timestamps |
| `member_courses` | member_course_id, member_id, subject_id, section_id, division_id, semester_id, assignment_type, timestamps |
| `timetable` | timetable_id, member_course_id, classroom_id, session_id, semester_id, status, created_by, timestamps |
| `priority_categories` | id, name, sort_order, timestamps |
| `priority_groups` | id, category_id, name, sort_order, is_active, timestamps |
| `priority_group_members` | id, group_id, member_id |
| `priority_exceptions` | id, member_id, group_id, type, granted_by, timestamps |
| `department_priority_order` | id, department_id, sort_order, current_group_id, timestamps |
| `audit_logs` | id, user_id, action, module, record_id, old_values, new_values, ip, user_agent, timestamps |
| `notifications` | id, user_id, title, message, type, is_read, link, timestamps |
| `api_tokens` | id, user_id, token_hash, expires_at, timestamps |

### العلاقات الرئيسية

- `users.role_id` → `roles.id`
- `users.member_id` → `faculty_members.member_id`
- `faculty_members.department_id` → `departments.department_id`
- `faculty_members.degree_id` → `academic_degrees.id`
- `subjects.department_id` → `departments.department_id`
- `subjects.level_id` → `levels.level_id`
- `divisions.department_id` → `departments.department_id`
- `divisions.level_id` → `levels.level_id`
- `sections.department_id` → `departments.department_id`
- `sections.level_id` → `levels.level_id`
- `sections.division_id` → `divisions.id`
- `member_courses.member_id` → `faculty_members.member_id`
- `member_courses.subject_id` → `subjects.subject_id`
- `member_courses.section_id` → `sections.section_id`
- `member_courses.division_id` → `divisions.id`
- `member_courses.semester_id` → `semesters.id`
- `timetable.member_course_id` → `member_courses.member_course_id`
- `timetable.classroom_id` → `classrooms.classroom_id`
- `timetable.session_id` → `sessions.session_id`
- `priority_groups.category_id` → `priority_categories.id`
- `priority_group_members.group_id` → `priority_groups.id`
- `priority_group_members.member_id` → `faculty_members.member_id`

## 7) قواعد العمل (Business Rules)

### 7.1 في التسكين (Scheduling)

- لا يمكن التسكين إلا إذا كان العضو مستحقاً لدوره وفقاً لنظام الأولوية المفعّل (PriorityService).
- يتم تخطي دور الأولوية في حال وجود استثناء فردي أو استثناء جماعي أو منحة تسجيل مباشرة من المدير.
- لا يمكن للمستخدم إدخال member_course لا يخصه.
- يمنع تكرار (classroom_id + session_id).
- يمنع تعارض الفترة على نفس المستوى/القسم (حسب الاستعلام الداخلي).

### 7.2 في التعديل/الحذف

- تعديل/حذف التسكين مقيد بملكية السجل.
- الحذف يتم عبر POST مع CSRF.

### 7.3 في نظام الأولوية

- 4 أوضاع: معطل، عام، متوازي بالقسم، تسلسلي بالقسم.
- المدير يتحكم في التقدم بين المجموعات والأقسام.
- الاستثناءات تتجاوز ترتيب الأولوية.
- التسجيل المباشر يمنح/يسحب الصلاحية فوراً.

### 7.4 في تسجيل الدخول

- التحقق بكلمة مرور مشفرة عبر password_verify.
- حماية الجلسة عبر AuthMiddleware.
- مصادقة API عبر Bearer Token (ApiAuthMiddleware).

## 8) الأمان المطبق

- **AuthMiddleware**: حماية المسارات المصادق عليها.
- **CsrfMiddleware**: حماية CSRF على POST/PUT/DELETE.
- **RbacMiddleware**: فحص صلاحيات RBAC.
- **ApiAuthMiddleware**: مصادقة API عبر Bearer Token.
- **ApiRoleMiddleware**: فحص أدوار API الإدارية.
- **RateLimitMiddleware**: تحديد معدل الطلبات على مسارات API.
- **Prepared Statements**: في جميع مسارات CRUD.
- **التحقق من المدخلات**: عبر `core/Request.php`.
- **إخفاء الأخطاء**: عدم كشف تفاصيل أخطاء قاعدة البيانات للمستخدم النهائي.

## 9) نظام التوافق مع الروابط القديمة

يوجد توثيق كامل في:
- [docs/url-compatibility-map.md](url-compatibility-map.md)

الغرض:
- استمرار عمل الروابط القديمة أثناء الانتقال.
- توفير mapping old → new قبل إزالة أي wrapper.

## 10) طريقة الاستخدام اليومية

1. دخول المدير.
2. تحديث الكيانات المرجعية (أقسام/مستويات/شُعب/سكاشن/مواد/قاعات/فترات).
3. إعداد السنة الأكاديمية والفصل الدراسي الحالي.
4. إعداد نظام الأولوية: إنشاء التصنيفات والمجموعات وترتيب الأقسام.
5. توزيع المواد على الأعضاء.
6. تفعيل وضع الأولوية المناسب.
7. فتح شاشة التسكين وإدخال السجلات بدون تعارض.
8. تقديم المجموعة/القسم التالي عند اكتمال عمل الحاليين.
9. مراجعة الناتج في شاشة عرض الجدول.
10. تصدير الجدول أو عرض التقارير.
11. إنشاء نسخة احتياطية ومزامنتها سحابياً عند الحاجة.

## 11) التشغيل والصيانة

### 11.1 التشغيل المحلي

- Apache + MySQL عبر XAMPP.
- ضع المشروع داخل htdocs.
- افتح `http://localhost/timetable/install.php` أول مرة فقط.

### 11.2 النسخ الاحتياطي

- إنشاء نسخ احتياطية SQL أو Excel من واجهة `/backups`.
- رفع النسخ إلى Google Drive أو Supabase أو Firebase.
- إعداد المزامنة السحابية من `/backups/cloud-settings`.
- إمكانية استعادة النسخ الاحتياطية مباشرة من الواجهة.

### 11.3 مراقبة الأخطاء

- السجلات تُحفظ في `storage/logs/`.
- سجلات التدقيق متاحة من `/audit-logs`.
- بيانات تحديد المعدل في `storage/rate_limits/`.

## 12) ملاحظات مهمة

- إصدار التطبيق الحالي: `2.0.0`.
- مجلد `core/` يحتوي على فئات MVC الجديدة وملفات توافق قديمة.
- مجلد `_legacy/` يحتوي على الكود الإجرائي القديم كمرجع فقط.
- ملفات الترحيل (35 ملف) في `database/migrations/` تُنشئ جميع الجداول المطلوبة.

## 13) خارطة تطوير مقترحة

- إكمال واجهات الإدارة في تطبيق Flutter.
- إضافة اختبارات تكامل شاملة.
- دعم تعدد اللغات (عربي/إنجليزي) الكامل.
- إشعارات Push لتطبيق الهاتف.
- تقويم أسبوعي تفاعلي.
- Offline mode متقدم لتطبيق الهاتف.

## 14) مرجع سريع للملفات الأساسية

### الإطار الأساسي (Core)
| الملف | الوظيفة |
|-------|---------|
| `public/index.php` | المتحكم الأمامي |
| `public/install.php` | معالج التثبيت |
| `core/Application.php` | التهيئة وتسجيل المسارات |
| `core/Router.php` | مطابقة URL ومجموعات المسارات |
| `core/Controller.php` | متحكم أساسي |
| `core/Model.php` | نموذج أساسي |
| `core/Database.php` | طبقة قاعدة البيانات |
| `core/Request.php` | معالجة الطلبات |
| `core/Session.php` | إدارة الجلسات |
| `core/View.php` | محرك القوالب |

### الإعدادات (Config)
| الملف | الوظيفة |
|-------|---------|
| `config/app.php` | إعدادات التطبيق |
| `config/database.php` | إعدادات قاعدة البيانات |
| `config/routes.php` | تعريف المسارات |
| `config/permissions.php` | خريطة الصلاحيات |
| `config/cloud.php` | إعدادات السحابة |

### الخدمات (Services)
| الملف | الوظيفة |
|-------|---------|
| `app/Services/PriorityService.php` | منطق نظام الأولوية |
| `app/Services/SchedulingService.php` | منطق التسكين ومنع التعارض |
| `app/Services/ExportService.php` | تصدير PDF/Excel |
| `app/Services/BackupService.php` | النسخ الاحتياطي |
| `app/Services/DataTransferService.php` | نقل البيانات |
| `app/Services/AuditService.php` | سجل التدقيق |
| `app/Services/NotificationService.php` | الإشعارات |

### Middleware
| الملف | الوظيفة |
|-------|---------|
| `app/Middleware/AuthMiddleware.php` | حماية المسارات |
| `app/Middleware/CsrfMiddleware.php` | حماية CSRF |
| `app/Middleware/RbacMiddleware.php` | فحص الصلاحيات |
| `app/Middleware/ApiAuthMiddleware.php` | مصادقة API |
| `app/Middleware/ApiRoleMiddleware.php` | أدوار API |
| `app/Middleware/RateLimitMiddleware.php` | تحديد المعدل |

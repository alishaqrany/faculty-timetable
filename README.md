# Timetable Management System | نظام إدارة الجدول الدراسي

Arabic below. English starts first for repository readability.

---

## English

### Overview

This project is a university timetable management system built with PHP and MySQL. It helps academic staff and administrators manage departments, levels, members, sections, subjects, classrooms, sessions, teaching assignments, and final timetable scheduling with conflict prevention.

The repository contains two application layers:

- A current PHP 8 MVC-style application under `app/`, `core/`, `config/`, and `public/`.
- A legacy procedural codebase under `_legacy/` kept for reference and compatibility work.

The current production-ready entry point is the `public/` application.

### Key Features

- Authentication and protected admin/staff workflows.
- Academic structure management: departments, levels, divisions/sections, subjects.
- Faculty and user management.
- Classroom and session management.
- Member course assignment.
- Scheduling with conflict checks.
- Priority-based scheduling workflow.
- Timetable viewing and export.
- Audit logs, notifications, settings, and data transfer tools.
- Backup and cloud sync integrations.
- API endpoints for timetable-related data.

### Tech Stack

- PHP 8.0+
- MySQL or MariaDB
- MySQLi / custom database layer
- Server environments such as XAMPP, Apache, or compatible PHP hosting

### Main Project Structure

```text
app/            MVC controllers, models, services, views
config/         Application, database, routes, permissions, cloud settings
core/           Bootstrap, router, request, session, database, view helpers
database/       Migrations, seeds, legacy migration helper
docs/           Arabic technical documentation and compatibility notes
public/         Web entry point and install wizard
storage/        Backups, exports, logs, cache, rate limits
_legacy/        Older procedural version kept for reference
```

### Local Setup

1. Place the project inside your local web root, for example `c:/xampp/htdocs/timetable`.
2. Start Apache and MySQL from XAMPP.
3. Make sure the server is running PHP 8.0 or newer.
4. Open the installer:

   `http://localhost/timetable/public/install.php`

5. Follow the installer steps:
   - Check requirements
   - Configure the database
   - Run migrations and seeds
   - Create the admin account
   - Save system settings
6. After installation, open:

   `http://localhost/timetable/public/login`

### Important Notes

- The active application entry point is `public/index.php`.
- The installer writes database settings into `config/database.php`.
- Some features write files under `storage/`, so the web server must have write access there.
- Cloud backup and sync features are optional and depend on `config/cloud.php`.
- The repository includes legacy paths and wrappers for backward compatibility during migration.

### Application Flow

Typical usage order:

1. Install the system.
2. Log in as administrator.
3. Create departments, levels, sections, subjects, classrooms, and sessions.
4. Add faculty members and users.
5. Assign member courses.
6. Configure priority scheduling if needed.
7. Schedule timetable entries.
8. Review and export the final timetable.

### API Summary

Available routes include:

- `POST /api/auth/login`
- `GET /api/timetable`
- `GET /api/departments`
- `GET /api/members`
- `GET /api/classrooms`
- `GET /api/subjects`
- `GET /api/sections`
- `GET /api/sessions`

Authenticated API routes require a Bearer token.

### Documentation

- `docs/complete-site-documentation-ar.md`
- `docs/site-blueprint-ar.md`
- `docs/url-compatibility-map.md`

---

## العربية

### نظرة عامة

هذا المشروع هو نظام لإدارة الجدول الدراسي الجامعي باستخدام PHP و MySQL. يتيح لمدير النظام أو منسق الجدول إدارة الأقسام والفرق والشعب والمواد وأعضاء هيئة التدريس والقاعات والفترات، ثم توزيع المواد على الأعضاء، ثم تسكينها داخل الجدول النهائي مع منع التعارضات.

يحتوي المستودع على مستويين من التطبيق:

- التطبيق الحالي المبني بأسلوب MVC داخل المجلدات `app/` و `core/` و `config/` و `public/`.
- نسخة قديمة إجرائية داخل `_legacy/` للاحتفاظ بالتوافق والرجوع إليها عند الحاجة.

نقطة التشغيل الأساسية الحالية هي التطبيق الموجود داخل `public/`.

### أهم المميزات

- تسجيل دخول وصلاحيات للصفحات المحمية.
- إدارة الكيانات الأكاديمية: الأقسام، الفرق، الشعب، المواد.
- إدارة أعضاء هيئة التدريس والمستخدمين.
- إدارة القاعات والفترات.
- ربط المواد بالأعضاء عبر `member-courses`.
- تسكين المواد داخل الجدول مع التحقق من التعارض.
- نظام أولوية للجدولة.
- عرض الجدول وتصديره.
- سجلات تدقيق، إشعارات، إعدادات، وأدوات نقل بيانات.
- نسخ احتياطية ومزامنة سحابية.
- واجهات API لبيانات الجدول وبعض الكيانات.

### التقنيات المستخدمة

- PHP 8.0 أو أحدث
- MySQL أو MariaDB
- MySQLi مع طبقة قاعدة بيانات مخصصة
- بيئات تشغيل مثل XAMPP أو Apache أو أي استضافة PHP متوافقة

### هيكل المشروع الرئيسي

```text
app/            المتحكمات والنماذج والخدمات والواجهات
config/         إعدادات التطبيق وقاعدة البيانات والمسارات والصلاحيات والسحابة
core/           النواة: التهيئة، التوجيه، الطلب، الجلسات، قاعدة البيانات، العرض
database/       ملفات الترحيل والبذور وأداة ترحيل البيانات القديمة
docs/           توثيق عربي وملاحظات التوافق
public/         نقطة الدخول للويب ومعالج التثبيت
storage/        النسخ الاحتياطية والتصدير والسجلات والذاكرة المؤقتة
_legacy/        النسخة الإجرائية القديمة للرجوع والتوافق
```

### التشغيل المحلي

1. ضع المشروع داخل مجلد الويب المحلي، مثل:

   `c:/xampp/htdocs/timetable`

2. شغّل Apache و MySQL من XAMPP.
3. تأكد أن إصدار PHP هو 8.0 أو أحدث.
4. افتح معالج التثبيت من الرابط:

   `http://localhost/timetable/public/install.php`

5. اتبع خطوات التثبيت:
   - فحص المتطلبات
   - إعداد قاعدة البيانات
   - تشغيل ملفات التهيئة والبذور
   - إنشاء حساب المدير
   - حفظ إعدادات النظام
6. بعد اكتمال التثبيت، افتح صفحة الدخول:

   `http://localhost/timetable/public/login`

### ملاحظات مهمة

- نقطة الدخول الفعلية للتطبيق الحالي هي `public/index.php`.
- يقوم معالج التثبيت بحفظ إعدادات قاعدة البيانات داخل `config/database.php`.
- بعض الوظائف تحتاج صلاحية كتابة داخل `storage/` مثل النسخ الاحتياطي والتصدير والسجلات.
- إعدادات النسخ السحابي والمزامنة اختيارية وتعتمد على `config/cloud.php`.
- ما زالت هناك مسارات وملفات توافقية لدعم الانتقال من النسخة القديمة إلى البنية الحالية.

### دورة الاستخدام المختصرة

1. تثبيت النظام.
2. تسجيل الدخول بحساب المدير.
3. إنشاء الأقسام والفرق والشعب والمواد والقاعات والفترات.
4. إضافة أعضاء هيئة التدريس والمستخدمين.
5. توزيع المواد على الأعضاء.
6. إعداد نظام الأولوية إذا كان مطلوبًا.
7. تنفيذ التسكين داخل الجدول.
8. مراجعة الجدول النهائي وتصديره.

### ملخص API

من المسارات المتاحة:

- `POST /api/auth/login`
- `GET /api/timetable`
- `GET /api/departments`
- `GET /api/members`
- `GET /api/classrooms`
- `GET /api/subjects`
- `GET /api/sections`
- `GET /api/sessions`

المسارات المحمية في API تحتاج Bearer Token.

### التوثيق الإضافي

- `docs/complete-site-documentation-ar.md`
- `docs/site-blueprint-ar.md`
- `docs/url-compatibility-map.md`

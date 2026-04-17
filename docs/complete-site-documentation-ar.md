# التوثيق الكامل لموقع إدارة الجدول الدراسي

## 1) نظرة عامة

هذا الموقع نظام ويب لإدارة بناء جدول دراسي جامعي بشكل تدريجي ومنظم، بدءا من إعداد الكيانات الأساسية (الأقسام، الفرق، السكاشن، المواد، القاعات، الفترات)، ثم توزيع المواد على أعضاء هيئة التدريس، ثم تسكين كل مادة في قاعة وفترة زمنية مع قيود تمنع التعارض.

النظام مبني بـ PHP (إجرائي) و MySQL/MariaDB عبر MySQLi، ويعمل عادة على XAMPP.

## 2) الفكرة التي يقوم عليها الموقع

### 2.1 المشكلة التي يحلها

تكوين جدول دراسي يدويا غالبا يسبب:
- تعارضات قاعات.
- تعارضات زمنية داخل نفس الفرقة/القسم.
- صعوبة تتبع من له الحق في إدخال/تعديل التسكين.

### 2.2 منهج الحل في النظام

النظام يعتمد على 3 مراحل:
- مرحلة التهيئة: إدخال البيانات المرجعية (الأقسام/الفرق/السكاشن/المواد/القاعات/الفترات).
- مرحلة الربط الأكاديمي: ربط عضو هيئة تدريس بمادة وسكشن (member_courses).
- مرحلة التسكين: اختيار (مادة عضو) + (قاعة) + (فترة) وتسجيلها في timetable مع التحقق من عدم التعارض.

### 2.3 فلسفة الصلاحيات

- كل المستخدمين يعتمدون على تسجيل دخول مرتبط بعضو هيئة تدريس.
- إدخال التسكين في [scheduling.php](../scheduling.php) مقيد بحالة registration_status.
- تعديل/حذف سجل تسكين مقيد بملكية السجل (owner_member_id).

## 3) الجمهور المستهدف

- مدير النظام: إعداد أولي + إدارة كل الكيانات.
- منسق الجداول/القسم: بناء الهيكل الأكاديمي وإتمام التسكين.
- عضو هيئة تدريس: يظهر ضمن سجلات التوزيع والتسكين بحسب الربط.

## 4) دورة حياة النظام (من الصفر إلى التشغيل)

1. تشغيل [install.php](../install.php).
2. إدخال إعدادات قاعدة البيانات لإنشاء [db_config.php](../db_config.php).
3. إنشاء الجداول وحساب المدير.
4. تسجيل الدخول عبر [login.php](../login.php).
5. من لوحة [index.php](../index.php):
   - إضافة الأقسام.
   - إضافة الفرق.
   - إضافة أعضاء هيئة التدريس.
   - إنشاء الفترات.
   - إضافة القاعات.
   - إضافة السكاشن.
   - إضافة المواد.
   - توزيع المواد على الأعضاء.
   - تسكين المواد.
   - عرض الجدول النهائي.

## 5) الوحدات والشاشات (Feature Modules)

### 5.1 الأقسام Departments

- المسار الكانوني:
  - [modules/departments/list.php](../modules/departments/list.php)
  - [modules/departments/edit.php](../modules/departments/edit.php)
  - [modules/departments/delete.php](../modules/departments/delete.php)
- مسارات توافقية قديمة:
  - [departments/departments.php](../departments/departments.php)
  - [departments/edit_department.php](../departments/edit_department.php)
  - [departments/delete_department.php](../departments/delete_department.php)

### 5.2 الفرق Levels

- [levels/levels.php](../levels/levels.php)
- [levels/edit_level.php](../levels/edit_level.php)
- [levels/delete_level.php](../levels/delete_level.php)

### 5.3 أعضاء هيئة التدريس Members

- [members/faculty_members.php](../members/faculty_members.php)
- [members/edit_member.php](../members/edit_member.php)
- [members/delete_member.php](../members/delete_member.php)
- إضافة درجات علمية: [members/add_degree.php](../members/add_degree.php)

### 5.4 السكاشن Sections

- [sections/sections.php](../sections/sections.php)
- [sections/edit_section.php](../sections/edit_section.php)
- [sections/delete_section.php](../sections/delete_section.php)

### 5.5 المواد Subjects

- [subjects/subjects.php](../subjects/subjects.php)
- [subjects/edit_subject.php](../subjects/edit_subject.php)
- [subjects/delete_subject.php](../subjects/delete_subject.php)

### 5.6 القاعات Classrooms

- [classrooms/classrooms.php](../classrooms/classrooms.php)
- [classrooms/edit_classroom.php](../classrooms/edit_classroom.php)
- [classrooms/delete_classroom.php](../classrooms/delete_classroom.php)

### 5.7 الفترات Sessions

- [sessions/sessions.php](../sessions/sessions.php)

### 5.8 توزيع المواد على الأعضاء Member Courses

- [membercourses/membercourses.php](../membercourses/membercourses.php)
- [membercourses/edit_record.php](../membercourses/edit_record.php)
- [membercourses/delete_record.php](../membercourses/delete_record.php)

### 5.9 تسكين المواد Scheduling

- [scheduling.php](../scheduling.php)
- [edit_scheduling.php](../edit_scheduling.php)
- [delete_scheduling.php](../delete_scheduling.php)
- صفحة منع الوصول: [unauthorized.php](../unauthorized.php)

### 5.10 عرض الجدول النهائي

- [table_query.php](../table_query.php)

## 6) نموذج البيانات (Database Model)

### الجداول الأساسية

- faculty_members:
  - member_id, member_name, academic_degree, join_date, ranking, role
- users:
  - id, username, password (hashed), member_id, registration_status
- departments:
  - department_id, department_name
- levels:
  - level_id, level_name
- sections:
  - section_id, section_name, department_id, level_id
- subjects:
  - subject_id, subject_name, department_id, level_id, hours
- classrooms:
  - classroom_id, classroom_name
- sessions:
  - session_id, day, session_name, start_time, end_time, duration
- member_courses:
  - member_course_id, member_id, subject_id, section_id
- timetable:
  - timetable_id, member_course_id, classroom_id, session_id
- academic_degrees:
  - id, degree_name

### العلاقات

- users.member_id -> faculty_members.member_id
- subjects.department_id -> departments.department_id
- subjects.level_id -> levels.level_id
- sections.department_id -> departments.department_id
- sections.level_id -> levels.level_id
- member_courses.member_id -> faculty_members.member_id
- member_courses.subject_id -> subjects.subject_id
- member_courses.section_id -> sections.section_id
- timetable.member_course_id -> member_courses.member_course_id
- timetable.classroom_id -> classrooms.classroom_id
- timetable.session_id -> sessions.session_id

## 7) قواعد العمل (Business Rules)

### 7.1 في التسكين [scheduling.php](../scheduling.php)

- لا يمكن التسكين إذا registration_status != 1.
- لا يمكن للمستخدم إدخال member_course لا يخصه.
- يمنع تكرار (classroom_id + session_id).
- يمنع تعارض الفترة على نفس المستوى/القسم (حسب الاستعلام الداخلي).

### 7.2 في التعديل/الحذف

- تعديل/حذف التسكين مقيد بملكية السجل.
- الحذف يتم عبر POST مع CSRF في المسارات الفعالة.

### 7.3 في تسجيل الدخول

- التحقق بكلمة مرور مشفرة عبر password_verify.
- عند غياب الجداول يظهر تنبيه بضرورة تشغيل install.

## 8) الأمان المطبق

- Session Auth Guard على الصفحات المحمية.
- CSRF Token للعمليات الحساسة POST.
- Prepared Statements في أغلب مسارات CRUD الفعالة.
- التحقق من المعرفات الرقمية (ctype_digit).
- إخفاء تفاصيل أخطاء قاعدة البيانات عن المستخدم النهائي مع log داخلي.

## 9) نظام التوافق مع الروابط القديمة

يوجد توثيق كامل في:
- [docs/url-compatibility-map.md](url-compatibility-map.md)

الغرض:
- استمرار عمل الروابط القديمة أثناء الانتقال.
- توفير mapping old -> new قبل إزالة أي wrapper.

## 10) طريقة الاستخدام اليومية (مختصرة)

1. دخول المدير.
2. تحديث الكيانات المرجعية (أقسام/فرق/سكاشن/مواد/قاعات/فترات).
3. توزيع المواد على الأعضاء.
4. فتح شاشة التسكين وإدخال السجلات دون تعارض.
5. تمرير الدور للمستخدم التالي عند اكتمال عمل الحالي.
6. مراجعة الناتج في شاشة عرض الجدول.

## 11) التشغيل والصيانة

### 11.1 التشغيل المحلي

- Apache + MySQL عبر XAMPP.
- ضع المشروع داخل htdocs.
- افتح [install.php](../install.php) أول مرة فقط.

### 11.2 النسخ الاحتياطي

- Backup دوري لقاعدة البيانات (mysqldump أو phpMyAdmin export).
- حفظ نسخة من [db_config.php](../db_config.php) في مكان آمن.

### 11.3 مراقبة الأخطاء

- الأخطاء التقنية تسجل عبر error_log (حسب إعدادات PHP/XAMPP).

## 12) ملاحظات مهمة معروفة

- زر "تسكين المواد" في [index.php](../index.php) يشير إلى timetable.php (مسار قديم).
  - تم توفير Wrapper توافق لهذا المسار في [timetable.php](../timetable.php).
- صفحة [unauthorized.php](../unauthorized.php) تحتوي جزءا كبيرا من المحتوى داخل تعليق، لذلك قد تظهر كسلوك مبسط.

## 13) خارطة تطوير مقترحة

- توحيد جميع الوحدات على نمط bootstrap/core بشكل كامل (كما في departments canonical).
- استخراج CSS/JS المكرر إلى ملفات مشتركة.
- بناء طبقة صلاحيات Role-Based أو Permission Matrix.
- إضافة اختبارات تكامل لتدفقات CRUD والتسكين.

## 14) مرجع سريع للملفات الأساسية

- الإعداد الأولي: [install.php](../install.php)
- الاتصال بقاعدة البيانات: [db_config.php](../db_config.php)
- الدخول/الخروج: [login.php](../login.php), [logout.php](../logout.php)
- لوحة النظام: [index.php](../index.php)
- التسكين: [scheduling.php](../scheduling.php)
- عرض الجدول: [table_query.php](../table_query.php)
- bootstrap/security helpers: [core/bootstrap.php](../core/bootstrap.php), [core/auth.php](../core/auth.php), [core/flash.php](../core/flash.php), [core/csrf.php](../core/csrf.php)

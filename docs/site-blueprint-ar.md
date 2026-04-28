# Blueprint - مخطط النظام

## 1) Architecture Blueprint

النظام يتبع نمط Monolith بسيط:
- واجهة صفحات PHP إجرائية.
- طبقة مساعدة Core (Auth, CSRF, Flash, Bootstrap).
- طبقة بيانات MySQL عبر MySQLi.

## 2) Request Flow Blueprint

```mermaid
flowchart TD
    A[Browser Request] --> B{Page Public?}
    B -->|Yes| C[Render Public Page]
    B -->|No| D[session_start + Auth Check]
    D -->|No member_id| E[Redirect login.php]
    D -->|Authenticated| F[Load db_config + helpers]
    F --> G{POST sensitive action?}
    G -->|Yes| H[Validate CSRF]
    H --> I[Validate Inputs]
    G -->|No| I
    I --> J[Run DB Query/Prepared Statement]
    J --> K[Set Flash Message]
    K --> L[Redirect or Render]
```

## 3) Scheduling Workflow Blueprint

```mermaid
flowchart LR
    U[Logged User] --> P{Priority Mode?}
    P -->|Disabled| R1[Check registration_status]
    P -->|Global| R2[Check Priority Group]
    P -->|Parallel| R3[Check Dept + Group]
    P -->|Sequential| R4[Check Dept turn + Group]
    
    R1 & R2 & R3 & R4 --> V0{Has turn/exception?}
    V0 -->|No| X0[Flash: Not your turn]
    V0 -->|Yes| MC[Select member_course]
    
    MC --> V1{Ownership check}
    V1 -->|Fail| X1[unauthorized.php]
    V1 -->|Pass| CL[Select classroom]
    CL --> SS[Select session]
    
    SS --> V3{classroom/session conflict?}
    V3 -->|Yes| X3[Reject with message]
    V3 -->|No| V4{section-level time conflict?}
    V4 -->|Yes| X4[Reject with message]
    V4 -->|No| OK[Insert timetable record]
```

## 4) Data Relationship Blueprint (ER Simplified)

```mermaid
erDiagram
    FACULTY_MEMBERS ||--o{ USERS : has
    DEPARTMENTS ||--o{ SUBJECTS : contains
    LEVELS ||--o{ SUBJECTS : classifies
    DEPARTMENTS ||--o{ SECTIONS : owns
    LEVELS ||--o{ SECTIONS : classifies
    FACULTY_MEMBERS ||--o{ MEMBER_COURSES : teaches
    SUBJECTS ||--o{ MEMBER_COURSES : assigned
    SECTIONS ||--o{ MEMBER_COURSES : for
    MEMBER_COURSES ||--o{ TIMETABLE : scheduled_as
    CLASSROOMS ||--o{ TIMETABLE : hosts
    SESSIONS ||--o{ TIMETABLE : occurs_in
    PRIORITY_CATEGORIES ||--o{ PRIORITY_GROUPS : contains
    PRIORITY_GROUPS ||--o{ PRIORITY_GROUP_MEMBERS : groups
    FACULTY_MEMBERS ||--o{ PRIORITY_GROUP_MEMBERS : enrolled
    FACULTY_MEMBERS ||--o{ PRIORITY_EXCEPTIONS : individual_exception
    PRIORITY_GROUPS ||--o{ PRIORITY_EXCEPTIONS : group_exception
    DEPARTMENTS ||--o{ DEPARTMENT_PRIORITY_ORDER : ordered
```

## 5) Module Blueprint

- Setup & Access:
  - [install.php](../install.php)
  - [login.php](../login.php)
  - [logout.php](../logout.php)
- Core:
  - [core/bootstrap.php](../core/bootstrap.php)
  - [core/auth.php](../core/auth.php)
  - [core/csrf.php](../core/csrf.php)
  - [core/flash.php](../core/flash.php)
- Domain CRUD:
  - departments/levels/sections/subjects/classrooms/sessions/members/membercourses
- Priority Management:
  - priority/categories, priority/groups, priority/exceptions, priority/dept-order
- Scheduling:
  - [scheduling.php](../scheduling.php)
  - [edit_scheduling.php](../edit_scheduling.php)
  - [delete_scheduling.php](../delete_scheduling.php)
- Query/Output:
  - [table_query.php](../table_query.php)

## 6) Security Blueprint

- Session guard: منع الوصول بدون session.
- CSRF guard: تأمين POST الحساسة.
- Ownership guard: حماية تعديل/حذف سجلات التسكين.
- Input guard: فحص المعرفات والحقول المطلوبة.
- DB guard: Prepared statements حيثما تم تحديث المسارات.

## 7) Compatibility Blueprint

- توجد مسارات canonical جديدة مع wrappers قديمة.
- مرجع التحويل في [docs/url-compatibility-map.md](url-compatibility-map.md).
- الهدف المرحلي: الاحتفاظ بالتوافق ثم إزالة تدريجية بعد ثبات الاستخدام.

## 8) Deployment Blueprint (Local XAMPP)

1. Apache + MySQL ON.
2. فتح [install.php](../install.php) لأول مرة.
3. إنشاء db_config والجداول.
4. تسجيل دخول المدير.
5. تشغيل الوحدات حسب ترتيب الإدخال.

## 9) Operational Checks Blueprint

- Check 1: إنشاء قسم/فرقة/سكشن/مادة.
- Check 2: إنشاء عضو + user مربوط.
- Check 3: إنشاء member_course.
- Check 4: تسكين صحيح.
- Check 5: محاولة تعارض (يجب أن يرفض).
- Check 6: تعديل/حذف من غير مالك (يجب أن يرفض).
- Check 7: عرض الجدول النهائي.

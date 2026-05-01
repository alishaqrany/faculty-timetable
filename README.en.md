# Timetable Management System

## Table of Contents

- [Overview](#overview)
- [Key Features](#key-features)
- [Tech Stack](#tech-stack)
- [Architecture](#architecture)
- [Project Structure](#project-structure)
- [Local Setup](#local-setup)
- [Screenshots](#screenshots)
- [Main Modules](#main-modules)
- [Priority Scheduling System](#priority-scheduling-system)
- [AJAX Dynamic Filtering](#ajax-dynamic-filtering)
- [Typical Workflow](#typical-workflow)
- [API Summary](#api-summary)
- [Mobile App](#mobile-app)
- [Permissions (RBAC)](#permissions-rbac)
- [Important Notes](#important-notes)
- [Documentation](#documentation)

## Overview

This project is a university timetable management system built with a custom PHP MVC framework and MySQL. It supports the full academic administration lifecycle: master data setup, teaching assignments, priority-based scheduling with conflict prevention, timetable review, exports, reports, notifications, cloud backup, and mobile access.

The repository includes:

- A PHP 8 MVC application in `app/`, `core/`, `config/`, and `public/`
- A Flutter mobile application in `mobile_app/`
- A legacy procedural implementation in `_legacy/` kept for reference
- 35 database migration files in `database/migrations/`

The web entry point is `public/index.php`, which serves as the front controller dispatching all routes.

## Key Features

- **MVC Architecture** — Custom router, middleware pipeline, base controller, model ORM, and templating engine
- **RBAC Permissions** — Role-based access control with 4 roles and granular `module.action` permissions
- **Priority Scheduling** — 4 scheduling modes (disabled, global, parallel by dept, sequential by dept) with categories, groups, exceptions, and direct-grant controls
- **AJAX Filtering** — Dynamic filtering on timetable and scheduling pages without page reloads, with URL history management
- **Division/Section Hierarchy** — Divisions (شُعب) containing sections (سكاشن) for flexible academic grouping
- **Academic Context** — Academic years and semesters with current-period tracking
- **Data Transfer** — SQL and Excel import/export with sample data support
- **Backup & Cloud Sync** — Local backup plus Google Drive, Supabase, and Firebase cloud integrations
- **Reports & Statistics** — Statistical dashboards with charts and detailed reports
- **Audit Logs** — Full administrative action traceability
- **Notifications** — In-app notification system with read/unread tracking
- **Mobile API (v1)** — RESTful API with Bearer token auth, rate limiting, student, lookup, and admin endpoints
- **Flutter Mobile App** — Student timetable views, Provider state management, Material 3 UI, and offline caching
- **Export** — PDF and Excel export for timetable data

## Tech Stack

- PHP 8.0+
- MySQL or MariaDB
- Custom MVC core framework (Router, Controller, Model, View, Request, Session, Database)
- Middleware pipeline (Auth, CSRF, RBAC, API Auth, API Role, Rate Limiting)
- MySQLi-based database layer with prepared statements
- AdminLTE 3 UI framework with RTL support
- Flutter (Dart 3.11+) for the mobile app
- XAMPP, Apache, or any compatible PHP hosting environment

## Architecture

The system follows a monolithic MVC pattern:

```
Browser Request
  → public/index.php (Front Controller)
  → core/Application.php (Bootstrap)
  → core/Router.php (URL Matching)
  → Middleware Pipeline (Auth → CSRF → RBAC)
  → app/Controllers/*Controller.php
  → app/Models/*.php + app/Services/*.php
  → app/Views/ (with layouts/partials)
  → Response
```

API requests follow a similar flow but use API-specific middleware (ApiAuthMiddleware, ApiRoleMiddleware, RateLimitMiddleware).

## Project Structure

```text
app/
├── Controllers/            Web + API controllers (24 web + 7 API)
│   └── Api/
│       ├── AuthController.php
│       ├── TimetableController.php
│       └── V1/             Mobile API v1 controllers
│           ├── BaseApiController.php
│           ├── AuthController.php
│           ├── StudentController.php
│           ├── LookupController.php
│           └── AdminController.php
├── Models/                 22 Eloquent-style models
├── Services/               Business logic services
│   ├── PriorityService.php
│   ├── SchedulingService.php
│   ├── ExportService.php
│   ├── BackupService.php
│   ├── DataTransferService.php
│   ├── AuditService.php
│   ├── NotificationService.php
│   └── Cloud/              Cloud provider integrations
│       ├── GoogleDriveService.php
│       ├── SupabaseService.php
│       └── FirebaseService.php
├── Middleware/             6 middleware classes
│   ├── AuthMiddleware.php
│   ├── CsrfMiddleware.php
│   ├── RbacMiddleware.php
│   ├── ApiAuthMiddleware.php
│   ├── ApiRoleMiddleware.php
│   └── RateLimitMiddleware.php
├── Views/                  26 view directories with layouts + partials
├── Helpers/
│   └── functions.php       Helper functions (url, asset, csrf, auth, etc.)
config/
├── app.php                 App name, URL, base path, timezone, version
├── database.php            DB credentials
├── routes.php              All route definitions (~200 lines)
├── permissions.php         RBAC permissions map (50+ permissions)
└── cloud.php               Cloud provider settings
core/
├── Application.php         Bootstrap, register routes, dispatch
├── Router.php              URL matching + param extraction + route groups
├── Controller.php          Base: view(), redirect(), json(), authorize()
├── Model.php               Base: find(), all(), create(), update(), delete(), paginate()
├── Database.php            MySQLi singleton with prepared statements + transactions
├── Request.php             Input abstraction + validation
├── Session.php             Session wrapper + flash messages
├── View.php                Template rendering with layouts + sections
├── auth.php                Legacy auth helper (compatibility)
├── bootstrap.php           Legacy bootstrap (compatibility)
├── csrf.php                Legacy CSRF helper (compatibility)
└── flash.php               Legacy flash helper (compatibility)
database/
├── migrations/             35 migration files (001–035)
├── seeds/                  Default seed data
│   └── 001_seed_defaults.php
└── migrate_legacy_data.php Legacy data migration tool
docs/                       Documentation files
mobile_app/                 Flutter mobile application
public/
├── index.php               Front controller
├── install.php             Installation wizard
├── .htaccess               URL rewriting rules
└── assets/                 CSS, JS, vendor assets
storage/
├── backups/                Database backup files
├── exports/                Export output files
├── logs/                   Application logs
├── cache/                  Cache files
├── import_samples/         Sample import files
└── rate_limits/            Rate limiting data
_legacy/                    Old procedural codebase (reference only)
```

## Local Setup

1. Put the project in your local web root, for example `c:/xampp/htdocs/timetable`.
2. Start Apache and MySQL.
3. Make sure PHP 8.0 or newer is available.
4. Open the installer:

   `http://localhost/timetable/install.php`

5. Complete the install steps:
   - Check requirements
   - Configure the database
   - Run migrations and seeds
   - Create the admin account
   - Save system settings
6. Visit the public home page:

   `http://localhost/timetable/`

7. Sign in to the admin area:

   `http://localhost/timetable/login`

## Screenshots

The following screenshots were captured from a working local environment.

### Login

![Local login screen](docs/screenshots/local-login.png)

### Dashboard

![Local dashboard](docs/screenshots/local-dashboard.png)

### Members

![Local members page](docs/screenshots/local-members.png)

### Departments

![Local departments page](docs/screenshots/local-departments.png)

## Main Modules

| Module | Controller | Description |
|--------|-----------|-------------|
| Home | `HomeController` | Public landing page |
| Authentication | `AuthController` | Login, logout, profile management |
| Dashboard | `DashboardController` | Admin dashboard with statistics |
| Departments | `DepartmentController` | Academic department CRUD |
| Levels | `LevelController` | Academic year/level CRUD + seed defaults |
| Members | `MemberController` | Faculty member CRUD |
| Subjects | `SubjectController` | Course/subject CRUD |
| Divisions | `DivisionController` | Division (شُعب) CRUD |
| Sections | `SectionController` | Section (سكاشن) CRUD + auto-generation |
| Classrooms | `ClassroomController` | Teaching room CRUD |
| Sessions | `SessionController` | Time-slot CRUD + bulk generation |
| Member Courses | `MemberCourseController` | Teaching assignment management |
| Scheduling | `SchedulingController` | Timetable entry creation with conflict prevention + AJAX filtering |
| Priority | `PriorityController` | Priority mode, categories, groups, exceptions, dept order |
| Timetable | `TimetableController` | Final timetable view, export, AJAX filtering |
| Reports | `ReportsController` | Statistical reports and analytics |
| Academic Years | `AcademicYearController` | Academic year management |
| Semesters | `SemesterController` | Semester management + auto-generation |
| Users | `UserController` | System user account CRUD |
| Audit Logs | `AuditLogController` | Administrative action logs (read-only) |
| Notifications | `NotificationController` | In-app messaging and notification management |
| Settings | `SettingController` | System configuration |
| Data Transfer | `DataTransferController` | SQL/Excel import, export, sample data, system reset |
| Backups | `BackupController` | Local backup + cloud sync (Google Drive, Supabase, Firebase) |

## Priority Scheduling System

The system supports 4 scheduling modes:

| Mode | Arabic | Description |
|------|--------|-------------|
| Disabled | معطل | Open scheduling, no priority restrictions |
| Global | عام | Scheduling order follows global category/group progression |
| Parallel | متوازي بالقسم | Each department schedules independently by its own group order |
| Sequential | تسلسلي بالقسم | Departments take turns scheduling one at a time |

**Key concepts:**
- **Categories** — Groupings like "Professor", "Assistant Professor"
- **Groups** — Ordered groups within each category
- **Exceptions** — Individual or group-based overrides that bypass priority order
- **Direct Grant** — Admin can grant/revoke scheduling access to any member at any time
- **Department Order** — Controls the sequence of departments in sequential mode

## AJAX Dynamic Filtering

The timetable and scheduling views support AJAX-based filtering that:
- Filters results without full page reloads
- Updates the URL via `pushState` for shareable/bookmarkable filter states
- Shows loading indicators during data fetch
- Endpoints: `GET /timetable/ajax-filter`, `GET /scheduling/ajax-filter`

## Typical Workflow

1. Install the system via the setup wizard.
2. Sign in as the administrator.
3. Create departments, levels, divisions, sections, subjects, classrooms, and sessions.
4. Add faculty members and user accounts.
5. Assign member courses (teaching assignments).
6. Configure the priority scheduling system (modes, categories, groups, department order).
7. Open scheduling for members to create timetable entries.
8. Advance priority groups/departments as each completes.
9. Review the final timetable or export it.

## API Summary

### Legacy API (`/api/*`)

Public authentication:
- `POST /api/auth/login`

Authenticated routes (Bearer token):
- `GET /api/timetable`
- `GET /api/departments`
- `GET /api/members`
- `GET /api/classrooms`
- `GET /api/subjects`
- `GET /api/sections`
- `GET /api/sessions`

### Mobile API v1 (`/api/v1/*`)

Authentication:
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout` (auth required)
- `GET /api/v1/me` (auth required)

Student endpoints (auth required):
- `GET /api/v1/students/timetable?department_id=&level_id=`
- `GET /api/v1/students/section-timetable?section_id=`
- `GET /api/v1/students/today-lectures?section_id=&date=`

Lookup endpoints (auth required):
- `GET /api/v1/lookups/departments`
- `GET /api/v1/lookups/levels`
- `GET /api/v1/lookups/sections`
- `GET /api/v1/lookups/subjects`
- `GET /api/v1/lookups/sessions`
- `GET /api/v1/lookups/classrooms`

Admin endpoints (auth + admin/dept_head role required):
- Departments: `GET|POST /api/v1/admin/departments`, `POST .../departments/{id}`, `POST .../departments/{id}/delete`
- Subjects: same CRUD pattern
- Sections: same CRUD pattern
- Sessions: same CRUD pattern

Query conventions: `page`, `per_page` (max 100), `q` (search)

All API routes are rate-limited. Authenticated routes require `Authorization: Bearer <token>`.

## Mobile App

The Flutter mobile app is located in `mobile_app/` and provides:

- **Student features** — Year timetable, section timetable, today's lectures
- **Architecture** — Provider state management, Material 3 design, Arabic UI
- **Offline caching** — Cached lookup data via `CacheManager`
- **Reusable widgets** — Loading overlays, empty states, error states, lecture cards

### Mobile App Structure

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

For Android emulator, use `10.0.2.2` instead of `localhost` in `app_config.dart`.

## Permissions (RBAC)

| Role | Arabic | Access Level |
|------|--------|-------------|
| `admin` | مدير النظام | Full access to all modules |
| `dept_head` | رئيس قسم | Department-scoped management |
| `faculty` | عضو هيئة تدريس | Scheduling own courses + view timetable |
| `viewer` | مراقب | Read-only access |

Permissions follow the `module.action` pattern (e.g., `departments.view`, `scheduling.manage`, `priority.grant_exception`). There are 50+ granular permissions defined in `config/permissions.php`.

## Important Notes

- Database settings are written by the installer into `config/database.php`.
- Features such as backups, logs, exports, and rate limiting depend on writable paths under `storage/`.
- Cloud integrations are configured through `config/cloud.php`.
- The `core/` directory contains both new MVC classes and legacy compatibility files (`auth.php`, `bootstrap.php`, `csrf.php`, `flash.php`).
- The application version is `2.0.0` as defined in `config/app.php`.

## Documentation

- [الدليل العربي](README.ar.md)
- [Arabic Site Documentation](docs/complete-site-documentation-ar.md)
- [Architecture Blueprint](docs/site-blueprint-ar.md)
- [URL Compatibility Map](docs/url-compatibility-map.md)
- [Mobile API v1 Spec](docs/mobile-api-v1-spec.md)
- [Mobile App Specification (Arabic)](docs/mobile-app-spec-ar.md)
- [Mobile App Development Plan (Arabic)](docs/mobile-app-development-plan-ar.md)
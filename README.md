# Timetable Management System

نظام إدارة الجداول الدراسية الجامعية — University timetable management system built with PHP MVC and MySQL.

## Readme Languages

- [English Guide](README.en.md)
- [الدليل العربي](README.ar.md)

## Project At A Glance

| Item | Path |
|------|------|
| Web entry point | `public/index.php` (via front controller) |
| Installation wizard | `public/install.php` |
| Application layers | `app/`, `core/`, `config/`, `public/` |
| Database migrations | `database/migrations/` (35 migration files) |
| Mobile app (Flutter) | `mobile_app/` |
| Legacy reference code | `_legacy/` |
| Storage and runtime | `storage/` |
| Public homepage | `HomeController@index` at `/` |

## Quick Start

1. Run Apache and MySQL (e.g. via XAMPP).
2. Use PHP 8.0 or newer.
3. Open `http://localhost/timetable/install.php` to run the setup wizard.
4. Complete the installer, then:
   - Public home page: `http://localhost/timetable/`
   - Admin login: `http://localhost/timetable/login`
   - Dashboard: `http://localhost/timetable/dashboard`

## Key Features

- MVC architecture with custom router, middleware, and templating
- RBAC permission system (admin, dept_head, faculty, viewer)
- Priority-based scheduling with 4 modes (disabled, global, parallel, sequential)
- AJAX-based dynamic filtering for timetable and scheduling views
- Academic year and semester management
- Division and section hierarchy
- Cloud backup (Google Drive, Supabase, Firebase)
- Data transfer (SQL/Excel import/export)
- Reports and statistics dashboard
- Mobile API v1 with Bearer token authentication
- Flutter mobile app for students and administrators
- Audit logs, notifications, and system settings

## Documentation

- [English Guide](README.en.md)
- [الدليل العربي](README.ar.md)
- [Arabic Site Documentation](docs/complete-site-documentation-ar.md)
- [Arabic Architecture Blueprint](docs/site-blueprint-ar.md)
- [URL Compatibility Map](docs/url-compatibility-map.md)
- [Mobile API v1 Spec](docs/mobile-api-v1-spec.md)
- [Mobile App Specification (Arabic)](docs/mobile-app-spec-ar.md)
- [Mobile App Development Plan (Arabic)](docs/mobile-app-development-plan-ar.md)

## Screenshots Preview

### Login

![Local login screen](docs/screenshots/local-login.png)

### Dashboard

![Local dashboard](docs/screenshots/local-dashboard.png)

### Members

![Local members page](docs/screenshots/local-members.png)

### Departments

![Local departments page](docs/screenshots/local-departments.png)

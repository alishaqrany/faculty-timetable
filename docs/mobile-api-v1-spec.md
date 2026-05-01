# Mobile API v1 Spec

This document tracks the implemented mobile API layer that was added on top of the existing PHP MVC system.

## What was added

- New namespace: `Api\V1` controllers.
- Unified API response contract through `BaseApiController`.
- New role middleware for admin mobile endpoints (`ApiRoleMiddleware`).
- New route groups in `config/routes.php` under `/api/v1/*`.
- DB index migration for mobile-heavy timetable queries.
- Rate limiting on all API routes via `RateLimitMiddleware`.

## Implemented backend files

- `app/Controllers/Api/V1/BaseApiController.php` — Unified response helpers
- `app/Controllers/Api/V1/AuthController.php` — Login, logout, me
- `app/Controllers/Api/V1/StudentController.php` — Student timetable endpoints
- `app/Controllers/Api/V1/LookupController.php` — Reference data endpoints
- `app/Controllers/Api/V1/AdminController.php` — Admin CRUD endpoints
- `app/Middleware/ApiAuthMiddleware.php` — Bearer token authentication
- `app/Middleware/ApiRoleMiddleware.php` — Admin role verification
- `app/Middleware/RateLimitMiddleware.php` — Request rate limiting
- `database/migrations/035_add_mobile_api_indexes.php` — DB indexes

## Base URL and auth

- Base path: `/api/v1`
- Token type: Bearer
- Header:
  - `Authorization: Bearer <token>`
- Tokens are stored as hashes in the `api_tokens` table

## Response contract

### Success
```json
{
  "success": true,
  "message": "OK",
  "data": {},
  "meta": {}
}
```

### Error
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field": ["details"]
  }
}
```

## Status code convention

- `200` Success
- `201` Created
- `400` Invalid request
- `401` Unauthenticated
- `403` Unauthorized
- `404` Not found
- `422` Validation error
- `429` Too many requests
- `500` Internal error

## Endpoints

### Auth

- `POST /api/v1/auth/login`
  - body: `username`, `password` (form fields)
  - returns: token, token type, expires date, user data
- `POST /api/v1/auth/logout` (auth required)
- `GET /api/v1/me` (auth required)
  - returns: user profile data

### Student

- `GET /api/v1/students/timetable?department_id=&level_id=`
  - returns: timetable entries for a department/level combination
- `GET /api/v1/students/section-timetable?section_id=`
  - returns: timetable entries for a specific section
- `GET /api/v1/students/today-lectures?section_id=&date=`
  - returns: today's lectures filtered by section
- `GET /api/v1/students/today-lectures?department_id=&level_id=&date=`
  - returns: today's lectures filtered by department/level

### Lookups

- `GET /api/v1/lookups/departments`
- `GET /api/v1/lookups/levels`
- `GET /api/v1/lookups/sections`
- `GET /api/v1/lookups/subjects`
- `GET /api/v1/lookups/sessions`
- `GET /api/v1/lookups/classrooms`

### Admin (Phase 1)

Admin routes require `ApiRoleMiddleware` and currently allow `admin` and `dept_head`.

- Departments
  - `GET /api/v1/admin/departments`
  - `POST /api/v1/admin/departments`
  - `POST /api/v1/admin/departments/{id}`
  - `POST /api/v1/admin/departments/{id}/delete`
- Subjects
  - `GET /api/v1/admin/subjects`
  - `POST /api/v1/admin/subjects`
  - `POST /api/v1/admin/subjects/{id}`
  - `POST /api/v1/admin/subjects/{id}/delete`
- Sections
  - `GET /api/v1/admin/sections`
  - `POST /api/v1/admin/sections`
  - `POST /api/v1/admin/sections/{id}`
  - `POST /api/v1/admin/sections/{id}/delete`
- Sessions
  - `GET /api/v1/admin/sessions`
  - `POST /api/v1/admin/sessions`
  - `POST /api/v1/admin/sessions/{id}`
  - `POST /api/v1/admin/sessions/{id}/delete`

## Legacy API (`/api/*`)

In addition to the v1 API, there is a legacy API layer:

- `POST /api/auth/login` — Legacy authentication
- `GET /api/timetable` — Timetable data
- `GET /api/departments` — Departments list
- `GET /api/members` — Members list
- `GET /api/classrooms` — Classrooms list
- `GET /api/subjects` — Subjects list
- `GET /api/sections` — Sections list
- `GET /api/sessions` — Sessions list

Legacy API controllers: `Api\AuthController`, `Api\TimetableController`.

## Query conventions (admin list endpoints)

- `page` default `1`
- `per_page` default `20`, max `100`
- `q` for free text search

## Rate limiting

All API routes are rate-limited via `RateLimitMiddleware`. Rate limit data is stored in `storage/rate_limits/`.

## Added database indexes

Migration: `database/migrations/035_add_mobile_api_indexes.php`

- `subjects(department_id, level_id)`
- `sessions(day, start_time)`
- `member_courses(section_id)`
- `member_courses(subject_id)`
- `timetable(member_course_id, session_id)`

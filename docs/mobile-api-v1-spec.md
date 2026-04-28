# Mobile API v1 Spec

## Current API Inventory (Reusable)

### Authentication
- `POST /api/auth/login` -> `Api\AuthController@login`
  - body: `username`, `password`
  - returns: token + user basics

### Timetable Data
- `GET /api/timetable` -> `Api\TimetableController@index`
  - filters: `department_id`, `level_id`, `member_id`, `classroom_id`
- `GET /api/departments`
- `GET /api/members`
- `GET /api/classrooms`
- `GET /api/subjects`
- `GET /api/sections`
- `GET /api/sessions`

### Internal AJAX Endpoints (Web UI)
- `GET /api/divisions/by-dept-level`
- `GET /api/sections/by-division`

## v1 Contract

### Base path
- `/api/v1`

### Authentication
- Token type: Bearer
- Header: `Authorization: Bearer <token>`

### Unified response envelope
- Success:
```json
{
  "success": true,
  "message": "OK",
  "data": {},
  "meta": {}
}
```
- Error:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field": ["details"]
  }
}
```

### Error status codes
- `400` invalid request
- `401` unauthenticated
- `403` unauthorized
- `404` resource not found
- `422` validation error
- `429` too many requests
- `500` internal server error

### Student Endpoints
- `GET /api/v1/students/timetable?department_id=&level_id=`
- `GET /api/v1/students/section-timetable?section_id=`
- `GET /api/v1/students/today-lectures?section_id=|department_id=&level_id=&date=`

### Auth Endpoints
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `GET /api/v1/me`

### Lookup Endpoints
- `GET /api/v1/lookups/departments`
- `GET /api/v1/lookups/levels`
- `GET /api/v1/lookups/sections`
- `GET /api/v1/lookups/subjects`
- `GET /api/v1/lookups/sessions`
- `GET /api/v1/lookups/classrooms`

### Admin Endpoints (Phase 1)
- `GET /api/v1/admin/departments`
- `POST /api/v1/admin/departments`
- `POST /api/v1/admin/departments/{id}`
- `POST /api/v1/admin/departments/{id}/delete`
- same shape for:
  - `subjects`
  - `sections`
  - `sessions`

### Pagination/filter/sort convention
- Query:
  - `page` (default 1)
  - `per_page` (default 20, max 100)
  - `q` (search)
  - `sort_by`
  - `sort_dir` (`asc`|`desc`)

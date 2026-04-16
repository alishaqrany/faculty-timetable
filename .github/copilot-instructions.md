# Project Guidelines

## Code Style
- Use plain PHP + MySQLi style consistent with the existing codebase (procedural scripts with inline HTML).
- Preserve existing naming conventions and file patterns (for example: `edit_*.php`, `delete_*.php`, and module-local `navbar.php`).
- Keep Arabic UI text and RTL behavior intact unless the task explicitly requests text/design changes.
- Prefer minimal, targeted edits in existing files over broad refactors.

## Architecture
- This is a monolithic PHP application with feature folders: `members/`, `subjects/`, `sections/`, `sessions/`, `classrooms/`, `departments/`, `levels/`, and `membercourses/`.
- Entry flow:
  - `install.php`: initial DB config/table setup.
  - `login.php`: authentication.
  - `index.php`: dashboard and navigation.
- Database connection is centralized in `db_config.php` and imported via `require_once`.
- Each feature folder typically owns its list/create/edit/delete pages and a local `navbar.php`.

## Build and Test
- No package manager, build system, or automated tests are configured.
- Run locally with XAMPP (Apache + MySQL) and open app pages in a browser.
- Validate changes manually by exercising affected flows (login, CRUD actions, redirects, flash messages, and table rendering).
- Before testing app behavior, ensure DB schema exists (via `install.php`) and `db_config.php` points to the active local database.

## Conventions
- Session/auth pattern is required on protected pages:
  - call `session_start()` early.
  - redirect to `login.php` when `$_SESSION['member_id']` is missing.
- Reuse the existing include approach:
  - root pages include `navbar.php` from root.
  - module pages include their local `navbar.php` and use relative `require_once("../db_config.php")` when needed.
- Keep changes backward-compatible with current schema and existing query/result handling unless explicitly asked to redesign.

## Safety and Pitfalls
- Many queries are string-interpolated; avoid introducing additional SQL injection risk.
- Prefer prepared statements for new query code, but do not partially refactor unrelated files unless requested.
- Do not expose raw DB errors to end users in new code paths.
- `db_config.php` is generated/edited by setup flow; preserve its expected variable names: `$servername`, `$username`, `$password`, `$dbname`, `$conn`.
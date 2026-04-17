# URL Compatibility Map (Phase E)

This document tracks legacy URL wrappers during migration.
Do not remove wrappers until all checks in the review section are complete.

## Old -> New Mapping

| Legacy URL | Canonical URL | Compatibility Strategy | Reviewed |
|---|---|---|---|
| departments/departments.php | modules/departments/list.php | 302 redirect | Yes |
| departments/edit_department.php?id=... | modules/departments/edit.php?id=... | id passthrough redirect | Yes |
| departments/delete_department.php?id=... | modules/departments/delete.php | GET-to-POST bridge with CSRF auto-submit | Yes |
| scheduling old.php | scheduling.php | 302 redirect | Yes |
| members/faculty_members old.php | members/faculty_members.php | 302 redirect | Yes |
| sections/2.php | sections.php | 302 redirect | Yes |
| subjects/edit_test.php?subject_id=... | subjects/edit_subject.php?subject_id=... | query passthrough redirect | Yes |
| table-query-draft.php | table_query.php | 302 redirect | Yes |
| sessions/add custom_sessions.php | sessions/sessions.php | 302 redirect | Yes |
| subjects/navbar copy.php | subjects/navbar.php | include wrapper | Yes |
| subjects/navbar22.php | subjects/navbar.php | include wrapper | Yes |
| _legacy/scheduling old.php | scheduling.php | 302 redirect | Yes |
| _legacy/members/faculty_members old.php | members/faculty_members.php | 302 redirect | Yes |
| _legacy/sections/2.php | sections/sections.php | 302 redirect | Yes |
| _legacy/subjects/edit_test.php?subject_id=... | subjects/edit_subject.php?subject_id=... | query passthrough redirect | Yes |
| _legacy/table-query-draft.php | table_query.php | 302 redirect | Yes |
| _legacy/sessions/add custom_sessions.php | sessions/sessions.php | 302 redirect | Yes |
| _legacy/subjects/navbar copy.php | subjects/navbar.php | include wrapper | Yes |
| _legacy/subjects/navbar22.php | subjects/navbar.php | include wrapper | Yes |

## Wrapper Removal Review (Before Deletion)

- Verify access logs show no traffic to legacy URLs for at least 14 days.
- Verify internal links no longer point to legacy URLs.
- Verify external integrations/bookmarks are updated.
- Verify no automation/scripts call legacy URLs.
- Remove wrapper in one PR and keep this mapping updated.

## Notes

- Wrapper behavior is intentionally minimal to reduce migration risk.
- Destructive legacy GET routes are bridged to POST where needed.

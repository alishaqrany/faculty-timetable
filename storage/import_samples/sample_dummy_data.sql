-- Sample dummy data for Timetable system
-- Use for testing and demos only

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM timetable;
DELETE FROM member_courses;
DELETE FROM sessions;
DELETE FROM classrooms;
DELETE FROM sections;
DELETE FROM subjects;
DELETE FROM users WHERE username IN ('demo_head', 'demo_faculty');
DELETE FROM faculty_members WHERE email IN ('head@example.test', 'faculty@example.test');
DELETE FROM semesters WHERE semester_name IN ('الفصل الأول (تجريبي)', 'الفصل الثاني (تجريبي)');
DELETE FROM academic_years WHERE year_name = '2026/2027 (تجريبي)';
DELETE FROM levels WHERE level_code IN ('L1T', 'L2T');
DELETE FROM departments WHERE department_code IN ('CSE-T', 'MAT-T');

INSERT INTO departments (department_name, department_code, description, is_active)
VALUES
('قسم علوم الحاسب (تجريبي)', 'CSE-T', 'قسم تجريبي للاختبار', 1),
('قسم الرياضيات (تجريبي)', 'MAT-T', 'قسم تجريبي للاختبار', 1);

INSERT INTO levels (level_name, level_code, sort_order, is_active)
VALUES
('المستوى الأول (تجريبي)', 'L1T', 1, 1),
('المستوى الثاني (تجريبي)', 'L2T', 2, 1);

INSERT INTO academic_years (year_name, start_date, end_date, is_current, is_active)
VALUES ('2026/2027 (تجريبي)', '2026-09-01', '2027-06-30', 1, 1);

SET @year_id = (SELECT id FROM academic_years WHERE year_name = '2026/2027 (تجريبي)' LIMIT 1);

INSERT INTO semesters (semester_name, academic_year_id, start_date, end_date, is_current, is_active)
VALUES
('الفصل الأول (تجريبي)', @year_id, '2026-09-01', '2027-01-15', 1, 1),
('الفصل الثاني (تجريبي)', @year_id, '2027-02-01', '2027-06-30', 0, 1);

SET @sem_id = (SELECT id FROM semesters WHERE semester_name = 'الفصل الأول (تجريبي)' LIMIT 1);
SET @dept_cse = (SELECT department_id FROM departments WHERE department_code = 'CSE-T' LIMIT 1);
SET @lvl_1 = (SELECT level_id FROM levels WHERE level_code = 'L1T' LIMIT 1);

INSERT INTO subjects (subject_name, subject_code, department_id, level_id, hours, credit_hours, subject_type, is_active)
VALUES
('مدخل إلى البرمجة (تجريبي)', 'CS101-T', @dept_cse, @lvl_1, 3, 3, 'نظري', 1),
('هياكل البيانات (تجريبي)', 'CS102-T', @dept_cse, @lvl_1, 3, 3, 'نظري وعملي', 1);

INSERT INTO sections (section_name, department_id, level_id, capacity, is_active)
VALUES
('شعبة A (تجريبي)', @dept_cse, @lvl_1, 35, 1),
('شعبة B (تجريبي)', @dept_cse, @lvl_1, 35, 1);

INSERT INTO classrooms (classroom_name, capacity, classroom_type, building, floor, is_active)
VALUES
('قاعة C-101 (تجريبي)', 40, 'قاعة', 'المبنى C', '1', 1),
('معمل C-LAB1 (تجريبي)', 30, 'معمل', 'المبنى C', '2', 1);

INSERT INTO sessions (day, session_name, start_time, end_time, duration, is_active)
VALUES
('الأحد', 'المحاضرة 1', '08:00:00', '09:30:00', 90, 1),
('الأحد', 'المحاضرة 2', '09:45:00', '11:15:00', 90, 1),
('الاثنين', 'المحاضرة 1', '08:00:00', '09:30:00', 90, 1),
('الثلاثاء', 'المحاضرة 1', '08:00:00', '09:30:00', 90, 1);

INSERT INTO faculty_members (member_name, email, phone, department_id, degree, join_date, ranking, role, is_active)
VALUES
('د. أحمد تجريبي', 'head@example.test', '0500000001', @dept_cse, 'دكتوراه', '2024-09-01', 1, 'رئيس قسم', 1),
('أ. محمد تجريبي', 'faculty@example.test', '0500000002', @dept_cse, 'ماجستير', '2025-01-15', 2, 'عضو هيئة تدريس', 1);

SET @member_head = (SELECT member_id FROM faculty_members WHERE email = 'head@example.test' LIMIT 1);
SET @member_faculty = (SELECT member_id FROM faculty_members WHERE email = 'faculty@example.test' LIMIT 1);
SET @role_dept_head = (SELECT id FROM roles WHERE role_slug = 'dept_head' LIMIT 1);
SET @role_faculty = (SELECT id FROM roles WHERE role_slug = 'faculty' LIMIT 1);

INSERT INTO users (username, email, password, member_id, role_id, registration_status, is_active)
VALUES
('demo_head', 'head@example.test', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZag0J7sXh8M6gZfQnUnxE27XGr0aG', @member_head, @role_dept_head, 1, 1),
('demo_faculty', 'faculty@example.test', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZag0J7sXh8M6gZfQnUnxE27XGr0aG', @member_faculty, @role_faculty, 1, 1);

SET @subject1 = (SELECT subject_id FROM subjects WHERE subject_code = 'CS101-T' LIMIT 1);
SET @subject2 = (SELECT subject_id FROM subjects WHERE subject_code = 'CS102-T' LIMIT 1);
SET @sectionA = (SELECT section_id FROM sections WHERE section_name = 'شعبة A (تجريبي)' LIMIT 1);
SET @sectionB = (SELECT section_id FROM sections WHERE section_name = 'شعبة B (تجريبي)' LIMIT 1);
SET @class1 = (SELECT classroom_id FROM classrooms WHERE classroom_name = 'قاعة C-101 (تجريبي)' LIMIT 1);
SET @class2 = (SELECT classroom_id FROM classrooms WHERE classroom_name = 'معمل C-LAB1 (تجريبي)' LIMIT 1);
SET @sess1 = (SELECT session_id FROM sessions WHERE day = 'الأحد' AND session_name = 'المحاضرة 1' LIMIT 1);
SET @sess2 = (SELECT session_id FROM sessions WHERE day = 'الأحد' AND session_name = 'المحاضرة 2' LIMIT 1);
SET @admin_user = (SELECT id FROM users WHERE username = 'admin' LIMIT 1);

INSERT INTO member_courses (member_id, subject_id, section_id, semester_id, academic_year_id)
VALUES
(@member_faculty, @subject1, @sectionA, @sem_id, @year_id),
(@member_faculty, @subject2, @sectionB, @sem_id, @year_id);

SET @mc1 = (SELECT member_course_id FROM member_courses WHERE member_id = @member_faculty AND subject_id = @subject1 LIMIT 1);
SET @mc2 = (SELECT member_course_id FROM member_courses WHERE member_id = @member_faculty AND subject_id = @subject2 LIMIT 1);

INSERT INTO timetable (member_course_id, classroom_id, session_id, semester_id, academic_year_id, status, created_by)
VALUES
(@mc1, @class1, @sess1, @sem_id, @year_id, 'منشور', COALESCE(@admin_user, 1)),
(@mc2, @class2, @sess2, @sem_id, @year_id, 'منشور', COALESCE(@admin_user, 1));

SET FOREIGN_KEY_CHECKS = 1;

-- Demo login accounts created by this file:
-- username: demo_head    | password: password
-- username: demo_faculty | password: password

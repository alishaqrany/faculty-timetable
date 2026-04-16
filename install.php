<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['db_host'])) {
        // استقبال بيانات الاتصال بقاعدة البيانات وإنشاء ملف db_config.php
        $db_host = $_POST['db_host'];
        $db_user = $_POST['db_user'];
        $db_pass = $_POST['db_pass'];
        $db_name = $_POST['db_name'];

        $configContent = "<?php\n";
        $configContent .= "\$servername = '$db_host';\n";
        $configContent .= "\$username = '$db_user';\n";
        $configContent .= "\$password = '$db_pass';\n";
        $configContent .= "\$dbname = '$db_name';\n";
        $configContent .= "\$conn = new mysqli(\$servername, \$username, \$password, \$dbname);\n";
        $configContent .= "if (\$conn->connect_error) {\n";
        $configContent .= "    die('Connection failed: ' . \$conn->connect_error);\n";
        $configContent .= "}\n";
        $configContent .= "?>";

        file_put_contents('db_config.php', $configContent);
    } elseif (isset($_POST['admin_user'])) {
        // استقبال بيانات المدير وإنشاء الجداول وإضافة حساب المدير
        require_once("db_config.php");

        function createTable($conn, $tableName, $createTableQuery) {
            $createTableResult = mysqli_query($conn, $createTableQuery);
            if (!$createTableResult) {
                echo "<p>حدث خطأ أثناء إنشاء جدول $tableName: " . mysqli_error($conn) . "</p>";
            }
        }

        $admin_user = $_POST['admin_user'];
        $admin_pass = password_hash($_POST['admin_pass'], PASSWORD_DEFAULT);

        // إنشاء الجداول
        createTable($conn, "faculty_members", "
        CREATE TABLE IF NOT EXISTS faculty_members (
            member_id INT AUTO_INCREMENT PRIMARY KEY,
            member_name VARCHAR(255) NOT NULL,
            academic_degree VARCHAR(255) NOT NULL,
            join_date DATE,
            ranking INT,
            role VARCHAR(255)
        )");

        createTable($conn, "academic_degrees", "
        CREATE TABLE IF NOT EXISTS academic_degrees (
            id INT AUTO_INCREMENT PRIMARY KEY,
            degree_name VARCHAR(255) NOT NULL
        )" );

        createTable($conn, "departments", "
        CREATE TABLE IF NOT EXISTS departments (
            department_id INT AUTO_INCREMENT PRIMARY KEY,
            department_name NVARCHAR(255) NOT NULL
        )");

        createTable($conn, "classrooms", "
        CREATE TABLE IF NOT EXISTS classrooms (
            classroom_id INT AUTO_INCREMENT PRIMARY KEY,
            classroom_name NVARCHAR(255) NOT NULL
        )");

        createTable($conn, "users", "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            member_id INT,
            registration_status TINYINT NOT NULL DEFAULT 0,
            FOREIGN KEY (member_id) REFERENCES faculty_members(member_id) ON DELETE CASCADE
        )");

        createTable($conn, "levels", "
        CREATE TABLE IF NOT EXISTS levels (
            level_id INT AUTO_INCREMENT PRIMARY KEY,
            level_name NVARCHAR(255) NOT NULL
        )");

        createTable($conn, "subjects", "
        CREATE TABLE IF NOT EXISTS subjects (
            subject_id INT AUTO_INCREMENT PRIMARY KEY,
            subject_name VARCHAR(255) NOT NULL,
            department_id INT NOT NULL,
            level_id INT NOT NULL,
            hours INT NOT NULL,
            FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE CASCADE,
            FOREIGN KEY (level_id) REFERENCES levels(level_id) ON DELETE CASCADE
        )");

        createTable($conn, "sections", "
        CREATE TABLE IF NOT EXISTS sections (
            section_id INT AUTO_INCREMENT PRIMARY KEY,
            section_name NVARCHAR(255) NOT NULL,
            department_id INT NOT NULL,
            level_id INT NOT NULL,
            FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE CASCADE,
            FOREIGN KEY (level_id) REFERENCES levels(level_id) ON DELETE CASCADE
        )");

        createTable($conn, "sessions", "
        CREATE TABLE IF NOT EXISTS sessions (
            session_id INT AUTO_INCREMENT PRIMARY KEY,
            day VARCHAR(255) NOT NULL,
            session_name VARCHAR(255) NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            duration INT NOT NULL
        )");

        createTable($conn, "member_courses", "
        CREATE TABLE IF NOT EXISTS member_courses (
            member_course_id INT AUTO_INCREMENT PRIMARY KEY,
            member_id INT NOT NULL,
            subject_id INT NOT NULL,
            section_id INT NOT NULL,
            FOREIGN KEY (member_id) REFERENCES faculty_members(member_id) ON DELETE CASCADE,
            FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
            FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE CASCADE
        )");

        createTable($conn, "timetable", "
        CREATE TABLE IF NOT EXISTS timetable (
            timetable_id INT AUTO_INCREMENT PRIMARY KEY,
            member_course_id INT,
            classroom_id INT,
            session_id INT,
            FOREIGN KEY (member_course_id) REFERENCES member_courses(member_course_id) ON DELETE CASCADE,
            FOREIGN KEY (classroom_id) REFERENCES classrooms(classroom_id) ON DELETE CASCADE,
            FOREIGN KEY (session_id) REFERENCES sessions(session_id) ON DELETE CASCADE
        )");

        // إضافة المدير إلى faculty_members
        $adminName = 'مدير النظام';
        $academicDegree = 'غير محدد';
        $joinDate = date('Y-m-d');
        $ranking = 1;
        $role = 'مدير';

        $insertFacultyQuery = "INSERT INTO faculty_members (member_name, academic_degree, join_date, ranking, role) VALUES ('$adminName', '$academicDegree', '$joinDate', $ranking, '$role')";
        $insertFacultyResult = mysqli_query($conn, $insertFacultyQuery);

        if ($insertFacultyResult) {
            $adminMemberId = mysqli_insert_id($conn);

            // إضافة حساب المدير في جدول users
            $insertAdminQuery = "INSERT INTO users (username, password, member_id, registration_status) VALUES ('$admin_user', '$admin_pass', $adminMemberId, 1)";
            $insertAdminResult = mysqli_query($conn, $insertAdminQuery);

            if ($insertAdminResult) {
                echo "<p>تم إنشاء حساب المدير بنجاح.</p>";
            } else {
                echo "<p>حدث خطأ أثناء إنشاء حساب المدير: " . mysqli_error($conn) . "</p>";
            }

            $insertDegreesQuery = "INSERT INTO academic_degrees (degree_name) VALUES ('أستاذ'), ('أستاذ مساعد'), ('مدرس'), ('مدرس مساعد'), ('معيد')";
            $insertDegreesResult = mysqli_query($conn, $insertDegreesQuery);
            
            if ($insertDegreesResult) {
                echo "<p>تم إضافة البيانات بنجاح.</p>";
            } else {
                echo "<p>حدث خطأ أثناء إضافة البيانات: " . mysqli_error($conn) . "</p>";
            }
        } else {
            echo "<p>حدث خطأ أثناء إضافة المدير إلى جدول faculty_members: " . mysqli_error($conn) . "</p>";
        }

        echo "<p>تمت عملية التحقق وإنشاء الجداول بنجاح.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعداد النظام</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f5f7fb;
            color: #222;
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 16px 24px;
        }

        .card {
            background: #fff;
            border: 1px solid #e3e7ef;
            border-radius: 10px;
            padding: 18px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        h1 {
            margin: 0 0 16px;
            font-size: 24px;
        }

        h2 {
            margin: 0 0 14px;
            font-size: 20px;
        }

        .hint {
            margin: 0 0 12px;
            color: #555;
            font-size: 14px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 14px;
        }

        input {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #cfd7e6;
            border-radius: 8px;
            padding: 10px;
            font-size: 14px;
        }

        button {
            margin-top: 14px;
            border: 0;
            border-radius: 8px;
            background: #0d6efd;
            color: #fff;
            font-size: 14px;
            padding: 10px 14px;
            cursor: pointer;
        }

        button:hover {
            background: #0b5ed7;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>إعداد النظام لأول مرة</h1>

        <div class="card">
            <h2>1) إعداد الاتصال بقاعدة البيانات</h2>
            <p class="hint">أدخل بيانات MySQL أولاً لحفظ ملف db_config.php.</p>
            <form method="POST" action="">
                <div class="grid">
                    <div>
                        <label for="db_host">الخادم</label>
                        <input type="text" id="db_host" name="db_host" value="localhost" required>
                    </div>
                    <div>
                        <label for="db_user">اسم المستخدم</label>
                        <input type="text" id="db_user" name="db_user" value="root" required>
                    </div>
                    <div>
                        <label for="db_pass">كلمة المرور</label>
                        <input type="password" id="db_pass" name="db_pass">
                    </div>
                    <div>
                        <label for="db_name">اسم قاعدة البيانات</label>
                        <input type="text" id="db_name" name="db_name" value="timetable1" required>
                    </div>
                </div>
                <button type="submit">حفظ إعدادات قاعدة البيانات</button>
            </form>
        </div>

        <div class="card">
            <h2>2) إنشاء الجداول وحساب المدير</h2>
            <p class="hint">بعد حفظ الاتصال، أنشئ الجداول وأضف أول حساب مدير.</p>
            <form method="POST" action="">
                <div class="grid">
                    <div>
                        <label for="admin_user">اسم مستخدم المدير</label>
                        <input type="text" id="admin_user" name="admin_user" required>
                    </div>
                    <div>
                        <label for="admin_pass">كلمة مرور المدير</label>
                        <input type="password" id="admin_pass" name="admin_pass" required>
                    </div>
                </div>
                <button type="submit">إنشاء الجداول وحساب المدير</button>
            </form>
        </div>
    </div>
</body>
</html>

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
            FOREIGN KEY (member_id) REFERENCES faculty_members(member_id)
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
            FOREIGN KEY (department_id) REFERENCES departments(department_id),
            FOREIGN KEY (level_id) REFERENCES levels(level_id)
        )");

        createTable($conn, "sections", "
        CREATE TABLE IF NOT EXISTS sections (
            section_id INT AUTO_INCREMENT PRIMARY KEY,
            section_name NVARCHAR(255) NOT NULL,
            department_id INT NOT NULL,
            level_id INT NOT NULL,
            FOREIGN KEY (department_id) REFERENCES departments(department_id),
            FOREIGN KEY (level_id) REFERENCES levels(level_id)
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
            FOREIGN KEY (member_id) REFERENCES faculty_members(member_id),
            FOREIGN KEY (subject_id) REFERENCES subjects(subject_id),
            FOREIGN KEY (section_id) REFERENCES sections(section_id)
        )");

        createTable($conn, "timetable", "
        CREATE TABLE IF NOT EXISTS timetable (
            timetable_id INT AUTO_INCREMENT PRIMARY KEY,
            member_course_id INT,
            classroom_id INT,
            session_id INT,
            FOREIGN KEY (member_course_id) REFERENCES member_courses(member_course_id),
            FOREIGN KEY (classroom_id) REFERENCES classrooms(classroom_id),
            FOREIGN KEY (session_id) REFERENCES sessions(session_id)
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
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تثبيت النظام</title>
    <script>
        function sendDatabaseInfo() {
            var dbHost = document.getElementById('db_host').value;
            var dbUser = document.getElementById('db_user').value;
            var dbPass = document.getElementById('db_pass').value;
            var dbName = document.getElementById('db_name').value;

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "install.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById('admin_form').style.display = 'block';
                    document.getElementById('db_form').style.display = 'none';
                }
            };
            xhr.send("db_host=" + dbHost + "&db_user=" + dbUser + "&db_pass=" + dbPass + "&db_name=" + dbName);
        }

        function sendAdminInfo() {
            var adminUser = document.getElementById('admin_user').value;
            var adminPass = document.getElementById('admin_pass').value;

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "install.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById('result').innerHTML = xhr.responseText;
                }
            };
            xhr.send("admin_user=" + adminUser + "&admin_pass=" + adminPass);
        }
    </script>
</head>
<body>
    <h1>تثبيت النظام</h1>
    
    <div id="db_form">
        <h2>معلومات الاتصال بقاعدة البيانات</h2>
        <label for="db_host">المضيف:</label>
        <input type="text" id="db_host" name="db_host" required><br><br>
        
        <label for="db_user">اسم المستخدم:</label>
        <input type="text" id="db_user" name="db_user" required><br><br>
        
        <label for="db_pass">كلمة المرور:</label>
        <input type="password" id="db_pass" name="db_pass" required><br><br>
        
        <label for="db_name">اسم قاعدة البيانات:</label>
        <input type="text" id="db_name" name="db_name" required><br><br>
        
        <button type="button" onclick="sendDatabaseInfo()">التالي</button>
    </div>

    <div id="admin_form" style="display: none;">
        <h2>معلومات تسجيل دخول المدير</h2>
        <label for="admin_user">اسم المستخدم:</label>
        <input type="text" id="admin_user" name="admin_user" required><br><br>
        
        <label for="admin_pass">كلمة المرور:</label>
        <input type="password" id="admin_pass" name="admin_pass" required><br><br>
        
        <button type="button" onclick="sendAdminInfo()">إنشاء الحساب</button>
    </div>

    <div id="result"></div>
</body>
</html>





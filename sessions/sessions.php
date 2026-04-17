<?php
require_once("../db_config.php");
require_once("../core/flash.php");

// التحقق من تسجيل الدخول
session_start();

// التحقق من وجود معرف الجلسة للمستخدم المسجل
if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php"); // إعادة توجيه المستخدم إلى صفحة تسجيل الدخول إذا لم يكن مسجل الدخول
    exit();
}

// Define the sessions with their start time, end time, and duration
$tableName = "sessions";

$sessions = array(
    array("session_name" => "8:10", "start_time" => "8:00", "end_time" => "10:00", "duration" => 2),
    array("session_name" => "10:12", "start_time" => "10:00", "end_time" => "12:00", "duration" => 2),
    array("session_name" => "12:2", "start_time" => "12:00", "end_time" => "14:00", "duration" => 2),
    array("session_name" => "2:4", "start_time" => "14:00", "end_time" => "16:00", "duration" => 2),
    array("session_name" => "4:6", "start_time" => "16:00", "end_time" => "18:00", "duration" => 2)
);

list($message, $message_type) = flash_consume();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        // إضافة الفترات
        $insertFailed = false;

        // Loop through the days and sessions and save the time slots in the database
$days = ["الأحد", "الاثنين", "الثلاثاء", "الأربعاء", "الخميس"];
foreach ($days as $day) {
    foreach ($sessions as $session) {
        $session_name = $session["session_name"];
        $start_time = $session["start_time"];
        $end_time = $session["end_time"];
        $duration = $session["duration"];

        $insertQuery = "INSERT INTO sessions (day, session_name, start_time, end_time, duration) VALUES (?, ?, ?, ?, ?)";
        $insertStmt = mysqli_prepare($conn, $insertQuery);
        if ($insertStmt) {
            mysqli_stmt_bind_param($insertStmt, "ssssi", $day, $session_name, $start_time, $end_time, $duration);
            $insertResult = mysqli_stmt_execute($insertStmt);
            mysqli_stmt_close($insertStmt);
        } else {
            $insertResult = false;
        }

        if (!$insertResult) {
            error_log("Insert session failed: " . mysqli_error($conn));
            $insertFailed = true;
        }
    }
}

        if ($insertFailed) {
            flash_redirect('sessions.php', 'حدث خطأ أثناء حفظ بعض الفترات.', 'error');
        }

        flash_redirect('sessions.php', 'تمت إضافة الفترات بنجاح!', 'success');

    } elseif (isset($_POST['delete'])) {
        // حذف الجدول بالكامل
        $deleteQuery = "DROP TABLE $tableName";
        $deleteResult = mysqli_query($conn, $deleteQuery);
        if ($deleteResult) {
            flash_redirect('sessions.php', 'تم حذف الجدول بنجاح!', 'success');
        } else {
            error_log("Drop sessions table failed: " . mysqli_error($conn));
            flash_redirect('sessions.php', 'حدث خطأ أثناء حذف الجدول.', 'error');
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>نموذج البيانات</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <style>
        .alert {
            position: fixed;
            top: 80px;
            right: 20px;
            padding: 20px;
            background-color: #4CAF50;
            color: white;
            opacity: 1;
            transition: opacity 0.6s;
            border-radius: 5px;
            z-index: 1000;
        }

        .alert.error {
            background-color: #f44336;
        }
    </style>
    <script>
        function showAlert(message, type) {
            const alertBox = document.createElement('div');
            alertBox.className = 'alert';
            if (type === 'error') {
                alertBox.classList.add('error');
            }
            alertBox.textContent = message;

            document.body.appendChild(alertBox);

            setTimeout(() => {
                alertBox.style.opacity = '0';
                setTimeout(() => {
                    alertBox.remove();
                }, 600);
            }, 3000);
        }

        <?php if ($message): ?>
        window.onload = function() {
            showAlert("<?php echo $message; ?>", "<?php echo $message_type; ?>");
        }
        <?php endif; ?>
    </script>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 20px;
        }

        /* table {
            margin: 0 auto;
            border-collapse: collapse;
        }

        table td, table th {
            padding: 10px;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #f2f2f2;
        } */

        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            font-size:18px;
            font-size:18px;
            color: white;
            border: none;
            cursor: pointer;
            border-radius:8px;
            border-radius:8px;
        }

        button:hover {
            background-color: #45a049;
        }

        .delete{
            background-color: #CE2029;
        }

        .delete:hover{
            background-color: #B0171F;
        }

    </style>
    
    <link rel="stylesheet" type="text/css" href="../style.css">

</head>
<body>

    <?php include 'navbar.php'; ?>

    <h1>جدول الجدول الزمني</h1>
    <form method="post">
        <button type="submit" name="add">إضافة الفترات</button>
        
        
    </form>
    <form method="post">
        <button type="submit" class="delete" name="delete">حذف الجدول </button>
    </form>

    <table>
        <thead>
            <tr>
                <th>اليوم</th>
                <th>رقم الجلسة</th>
                <th>وقت البداية</th>
                <th>وقت النهاية</th>
                <th>عدد الساعات</th>
            </tr>
        </thead>
        <tbody>
            <?php
                // اختر الجدول إذا كان موجودًا
                $selectTableQuery = "SHOW TABLES LIKE 'sessions'";
                $selectTableResult = mysqli_query($conn, $selectTableQuery);
                if (mysqli_num_rows($selectTableResult) > 0) {
                    // الجدول موجود، قم بتنفيذ الاستعلام
                    $selectQuery = "SELECT * FROM sessions";
                    $selectResult = mysqli_query($conn, $selectQuery);
                    if ($selectResult) {
                        while ($row = mysqli_fetch_assoc($selectResult)) {
                            echo "<tr>";
                            echo "<td>{$row['day']}</td>";
                            echo "<td>{$row['session_id']}</td>";
                            echo "<td>{$row['start_time']}</td>";
                            echo "<td>{$row['end_time']}</td>";
                            echo "<td>{$row['duration']}</td>";
                            echo "</tr>";
                        }
                    } else {
                        error_log("Select sessions failed: " . mysqli_error($conn));
                        echo "<p>حدث خطأ أثناء استعلام قاعدة البيانات.</p>";
                    }
                } else {
                    // الجدول غير موجود
                    echo "<p>الجدول غير موجود!</p>";
                }
            ?>
        </tbody>
    </table>
</body>
</html>
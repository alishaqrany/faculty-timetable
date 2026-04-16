<?php
require_once("../db_config.php");

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        // إضافة الفترات

        // Loop through the days and sessions and save the time slots in the database
$days = ["الأحد", "الاثنين", "الثلاثاء", "الأربعاء", "الخميس"];
foreach ($days as $day) {
    foreach ($sessions as $session) {
        $session_name = $session["session_name"];
        $start_time = $session["start_time"];
        $end_time = $session["end_time"];
        $duration = $session["duration"];

        $insertQuery = "
        INSERT INTO sessions (day, session_name, start_time, end_time, duration)
        VALUES ('$day', '$session_name', '$start_time', '$end_time', '$duration')
        ";
        $insertResult = mysqli_query($conn, $insertQuery);
        if (!$insertResult) {
            echo "<p>حدث خطأ أثناء حفظ الفترة: " . mysqli_error($conn) . "</p>";
        }
    }
}

    } elseif (isset($_POST['delete'])) {
        // حذف الجدول بالكامل
        $deleteQuery = "DROP TABLE $tableName";
        $deleteResult = mysqli_query($conn, $deleteQuery);
        if ($deleteResult) {
            echo "<p>تم حذف الجدول بنجاح!</p>";
        } else {
            echo "<p>حدث خطأ أثناء حذف الجدول: " . mysqli_error($conn) . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>نموذج البيانات</title>
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
                        echo "<p>حدث خطأ أثناء استعلام قاعدة البيانات: " . mysqli_error($conn) . "</p>";
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
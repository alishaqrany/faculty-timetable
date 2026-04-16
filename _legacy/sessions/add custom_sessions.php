<?php
require_once("../db_config.php");
session_start();

if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

$tableName = "sessions";

$createTableQuery = "
CREATE TABLE IF NOT EXISTS $tableName (
    session_id INT AUTO_INCREMENT PRIMARY KEY,
    day_of_week VARCHAR(20) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    duration INT NOT NULL
)";

$createTableResult = mysqli_query($conn, $createTableQuery);

if ($createTableResult) {
    // echo "<p>تم إنشاء جدول الفترات (Sessions) بنجاح!</p>";
} else {
    echo "<p>حدث خطأ أثناء إنشاء الجدول: " . mysqli_error($conn) . "</p>";
}

// Check if the form is submitted and insert data if so
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $day_of_week = mysqli_real_escape_string($conn, $_POST['day_of_week']);
    $start_time = mysqli_real_escape_string($conn, $_POST['start_time']);
    $end_time = mysqli_real_escape_string($conn, $_POST['end_time']);
    $duration = 2; // عدد الساعات لكل فترة

    // Execute INSERT INTO query to insert data into the table
    $insertQuery = "
    INSERT INTO $tableName (day_of_week, start_time, end_time, duration)
    VALUES ('$day_of_week', '$start_time', '$end_time', '$duration')
    ";

    $insertResult = mysqli_query($conn, $insertQuery);

    if ($insertResult) {
        echo "<p>تم إدخال الفترة (Session) بنجاح!</p>";
    } else {
        echo "<p>حدث خطأ أثناء إدخال الفترة (Session): " . mysqli_error($conn) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>إضافة فترة دراسية جديدة</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 20px;
        }

        form {
            display: inline-block;
            text-align: right;
        }

        label {
            display: block;
            margin-bottom: 10px;
        }

        select, input[type="time"] {
            width: 200px;
            padding: 5px;
            margin-bottom: 10px;
        }

        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
    
    <link rel="stylesheet" type="text/css" href="../style.css">

</head>
<body>

    <?php include 'navbar.php'; ?>

    <h1>إضافة فترة دراسية جديدة</h1>
    <form method="POST" action="">
        <div>
            <label for="day_of_week">يوم الأسبوع:</label>
            <select name="day_of_week" id="day_of_week" required>
                <option value="الأحد">الأحد</option>
                <option value="الاثنين">الاثنين</option>
                <option value="الثلاثاء">الثلاثاء</option>
                <option value="الأربعاء">الأربعاء</option>
                <option value="الخميس">الخميس</option>
            </select>
        </div>

        <div>
            <label for="start_time">وقت بدء الفترة:</label>
            <input type="time" name="start_time" id="start_time" required>
        </div>

        <div>
            <label for="end_time">وقت انتهاء الفترة:</label>
            <input type="time" name="end_time" id="end_time" required>
        </div>

        <input type="submit" value="إضافة" name="submit">
    </form>
</body>
</html>

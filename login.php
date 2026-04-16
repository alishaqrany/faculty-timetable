<?php
session_start();
require_once('db_config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);
                if (password_verify($password, $row['password'])) {
                    $_SESSION['member_id'] = $row['member_id'];
                    header("Location: index.php");
                    exit();
                } else {
                    echo "كلمة المرور غير صحيحة.";
                }
            } else {
                echo "اسم المستخدم غير صحيح.";
            }

            mysqli_stmt_close($stmt);
        } else {
            echo "اسم المستخدم غير صحيح.";
        }

    } catch (mysqli_sql_exception $e) {
        // يحدث هذا غالباً عند عدم إنشاء الجداول بعد التثبيت.
        echo "قاعدة البيانات غير مكتملة. يرجى تشغيل صفحة التثبيت install.php أولاً.";

    }
}
?>




<!DOCTYPE html>
<html>
<head>
    <title>إضافة أعضاء هيئة التدريس</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 20px;
            direction: rtl;
        }

        form {
            display: inline-block;
            text-align: right;
            direction: rtl;
        }

        table {
            margin: 0 auto;
            border-collapse: collapse;
        }

        table td, table th {
            padding: 10px;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #f2f2f2;
        }

        label {
            display: block;
            margin-bottom: 10px;
        }

        input, select {
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
    
    <link rel="stylesheet" type="text/css" href="style.css">

</head>
<body>

    <?php include 'navbar.php'; ?>

    <h1>تسجيل الدخول</h1>


<form action="login.php" method="POST">
    <label for="username">اسم المستخدم:</label>
    <input type="text" name="username" id="username" required><br>

    <label for="password">كلمة المرور:</label>
    <input type="password" name="password" id="password" required><br>

    <input type="submit" value="تسجيل الدخول">
</form>


<!-- <a href="logout.php">تسجيل الخروج</a> -->

</body>
</html>


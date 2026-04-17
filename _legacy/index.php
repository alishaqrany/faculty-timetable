<?php
require_once("db_config.php");

// التحقق من تسجيل الدخول
session_start();

// التحقق من وجود معرف الجلسة للمستخدم المسجل
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php"); // إعادة توجيه المستخدم إلى صفحة تسجيل الدخول إذا لم يكن مسجل الدخول
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>صفحة الأزرار</title>
    <style>
        body {
            /* background: linear-gradient(135deg, #f0f0f0 0%, #d3d3d3 100%); */
            background-color: #FFDEE9;
            background-image: linear-gradient(0deg, #FFDEE9 0%, #B5FFFC 100%);
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            direction: rtl;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            max-width: 800px;
            width: 90vw; /* تعديل العرض ليكون بناءً على عرض الشاشة */
            padding: 20px;
        }

        .glassmorphism {
            position: relative;
            padding: 20px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .glassmorphism:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.15);
        }

        .button {
            display: block;
            width: 100%;
            padding: 12px;
            text-align: center;
            background-color: #C850C0; /* لون الزر الباستيل */
            color: white;
            text-decoration: none;
            font-size: 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .button:hover {
            background-color: #FF6F99; /* لون الزر عند التحويم */
        }
    </style>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="glassmorphism">
            <a class="button" href="departments/departments.php">اضافة شعبة</a>
        </div>
        <div class="glassmorphism">
            <a class="button" href="levels/levels.php">اضافة فرقة</a>
        </div>
        <div class="glassmorphism">
            <a class="button" href="members/faculty_members.php">اضافة عضو هيئة تدريس</a>
        </div>
        <div class="glassmorphism">
            <a class="button" href="sessions/sessions.php">الفترات</a>
        </div>
        <div class="glassmorphism">
            <a class="button" href="classrooms/classrooms.php">اضافة قاعة</a>
        </div>
        <div class="glassmorphism">
            <a class="button" href="sections/sections.php">اضافة سكشن</a>
        </div>
        <div class="glassmorphism">
            <a class="button" href="subjects/subjects.php">اضافة مادة</a>
        </div>
        <div class="glassmorphism">
            <a class="button" href="membercourses/membercourses.php">توزيع مواد الاعضاء</a>
        </div>
        <div class="glassmorphism">
            <a class="button" href="timetable.php">تسكين المواد</a>
        </div>
        <div class="glassmorphism">
            <a class="button" href="table_query.php"> عرض الجدول</a>
        </div>
        <div class="glassmorphism logout">
            <a class="button" href="logout.php"> تسجيل الخروج </a>
        </div>
    </div>
</body>
</html>

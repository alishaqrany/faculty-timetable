<?php
require_once("../db_config.php");
session_start();

// التحقق من وجود معرف الجلسة للمستخدم المسجل
if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

// التحقق من وجود معرف السكشن في العنوان
if (!isset($_GET['id'])) {
    header("Location: sections.php");
    exit();
}

$section_id = $_GET['id'];

// إجراء استعلام DELETE لحذف السكشن من قاعدة البيانات
$deleteQuery = "DELETE FROM sections WHERE section_id = '$section_id'";
$deleteResult = mysqli_query($conn, $deleteQuery);

if ($deleteResult) {
    $_SESSION['message'] = "تم حذف السكشن بنجاح!";
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "حدث خطأ أثناء حذف السكشن: " . mysqli_error($conn);
    $_SESSION['message_type'] = "error";
}

header("Location: sections.php");
exit();

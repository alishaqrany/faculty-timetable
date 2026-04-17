<?php
require_once("../db_config.php");
session_start();

// التحقق من وجود معرف الجلسة للمستخدم المسجل
if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

// التحقق من وجود معرف السكشن في العنوان
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    $_SESSION['message'] = "تعذر حذف السكشن: معرف غير صالح.";
    $_SESSION['message_type'] = "error";
    header("Location: sections.php");
    exit();
}

$section_id = (int)$_GET['id'];

// إجراء استعلام DELETE لحذف السكشن من قاعدة البيانات
$deleteQuery = "DELETE FROM sections WHERE section_id = '$section_id'";
$deleteResult = mysqli_query($conn, $deleteQuery);

if ($deleteResult) {
    $_SESSION['message'] = "تم حذف السكشن بنجاح!";
    $_SESSION['message_type'] = "success";
} else {
    error_log("Delete section failed: " . mysqli_error($conn));
    $_SESSION['message'] = "حدث خطأ أثناء حذف السكشن.";
    $_SESSION['message_type'] = "error";
}

header("Location: sections.php");
exit();

<?php
require_once("../db_config.php");
session_start();

if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    $_SESSION['message'] = "حدث خطأ أثناء حذف القسم: معرف غير صالح.";
    $_SESSION['message_type'] = "error";
    header("Location: departments.php");
    exit();
}

$department_id = (int)$_GET['id'];
$deleteQuery = "DELETE FROM departments WHERE department_id = $department_id";
$deleteResult = mysqli_query($conn, $deleteQuery);

if ($deleteResult) {
    $_SESSION['message'] = "تم حذف القسم بنجاح!";
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "حدث خطأ أثناء حذف القسم: " . mysqli_error($conn);
    $_SESSION['message_type'] = "error";
}

header("Location: departments.php");
exit();

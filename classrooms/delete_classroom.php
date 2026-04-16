<?php
require_once("../db_config.php");
session_start();

if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    $_SESSION['message'] = "حدث خطأ أثناء حذف القاعة الدراسية: معرف غير صالح.";
    $_SESSION['message_type'] = "error";
    header("Location: classrooms.php");
    exit();
}

$classroom_id = (int)$_GET['id'];
$deleteQuery = "DELETE FROM classrooms WHERE classroom_id = $classroom_id";
$deleteResult = mysqli_query($conn, $deleteQuery);

if ($deleteResult) {
    $_SESSION['message'] = "تم حذف القاعة الدراسية بنجاح!";
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "حدث خطأ أثناء حذف القاعة الدراسية: " . mysqli_error($conn);
    $_SESSION['message_type'] = "error";
}

header("Location: classrooms.php");
exit();

<?php
require_once("../db_config.php");
session_start();

if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: membercourses.php");
    exit();
}

$id = $_GET['id'];

if (!ctype_digit($id)) {
    $_SESSION['message'] = "تعذر حذف السجل: معرف غير صالح.";
    $_SESSION['message_type'] = "error";
    header("Location: membercourses.php");
    exit();
}

$id = (int)$id;

$deleteQuery = "DELETE FROM member_courses WHERE member_course_id = '$id'";

if ($conn->query($deleteQuery) === TRUE) {
    $_SESSION['message'] = "تم حذف السجل بنجاح!";
    $_SESSION['message_type'] = "success";
} else {
    error_log("Delete member course failed: " . $conn->error);
    $_SESSION['message'] = "حدث خطأ أثناء حذف السجل.";
    $_SESSION['message_type'] = "error";
}

header("Location: membercourses.php");
exit();
?>

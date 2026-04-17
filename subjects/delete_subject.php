<?php
require_once("../db_config.php");
session_start();

if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['subject_id']) || !ctype_digit($_GET['subject_id'])) {
    $_SESSION['message'] = "تعذر حذف المادة: معرف غير صالح.";
    $_SESSION['message_type'] = "error";
    header("Location: subjects.php");
    exit();
}

$subject_id = (int)$_GET['subject_id'];
$deleteQuery = "DELETE FROM subjects WHERE subject_id = $subject_id";
$deleteResult = mysqli_query($conn, $deleteQuery);

if ($deleteResult) {
    $_SESSION['message'] = "تم حذف المادة بنجاح!";
    $_SESSION['message_type'] = "success";
} else {
    error_log("Delete subject failed: " . mysqli_error($conn));
    $_SESSION['message'] = "حدث خطأ أثناء حذف المادة.";
    $_SESSION['message_type'] = "error";
}

header("Location: subjects.php");
exit();
?>

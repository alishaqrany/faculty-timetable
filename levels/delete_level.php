<?php
require_once("../db_config.php");
session_start();

if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    $_SESSION['message'] = "حدث خطأ أثناء حذف الفرقة الدراسية: معرف غير صالح.";
    $_SESSION['message_type'] = "error";
    header("Location: levels.php");
    exit();
}

$level_id = (int)$_GET['id'];
$deleteQuery = "DELETE FROM levels WHERE level_id = $level_id";
$deleteResult = mysqli_query($conn, $deleteQuery);

if ($deleteResult) {
    $_SESSION['message'] = "تم حذف الفرقة الدراسية بنجاح!";
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "حدث خطأ أثناء حذف الفرقة الدراسية: " . mysqli_error($conn);
    $_SESSION['message_type'] = "error";
}

header("Location: levels.php");
exit();

<?php
require_once("../db_config.php");
session_start();

if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

// التحقق من وجود معرف العضو في العنوان
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    $_SESSION['message'] = "تعذر حذف العضو: معرف غير صالح.";
    $_SESSION['message_type'] = "error";
    header("Location: faculty_members.php");
    exit();
}

$member_id = (int)$_GET['id'];


        // قم بتنفيذ استعلام الحذف
        $deleteQuery = "DELETE FROM faculty_members WHERE member_id = $member_id";
        $deleteResult = mysqli_query($conn, $deleteQuery);

        if ($deleteResult) {
            $_SESSION['message'] = "تم حذف العضو بنجاح!";
            $_SESSION['message_type'] = "success";
            header("Location: faculty_members.php");
            exit();

        } else {
            error_log("Delete member failed: " . mysqli_error($conn));
            $_SESSION['message'] = "حدث خطأ أثناء حذف العضو.";
            $_SESSION['message_type'] = "error";
            header("Location: faculty_members.php");
            exit();
        }
?>

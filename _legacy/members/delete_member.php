<?php
require_once("../db_config.php");
require_once("../core/csrf.php");
require_once("../core/flash.php");
session_start();

if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: faculty_members.php");
    exit();
}

if (!isset($_POST['csrf_token']) || !csrf_validate($_POST['csrf_token'])) {
    flash_redirect('faculty_members.php', 'تعذر حذف العضو: طلب غير صالح.', 'error');
}

if (!isset($_POST['id']) || !ctype_digit($_POST['id'])) {
    flash_redirect('faculty_members.php', 'تعذر حذف العضو: معرف غير صالح.', 'error');
}

$member_id = (int)$_POST['id'];


$deleteQuery = "DELETE FROM faculty_members WHERE member_id = ?";
$stmt = mysqli_prepare($conn, $deleteQuery);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $member_id);
    if (mysqli_stmt_execute($stmt)) {
        flash_redirect('faculty_members.php', 'تم حذف العضو بنجاح!', 'success');
    } else {
        error_log("Delete member failed: " . mysqli_stmt_error($stmt));
        flash_redirect('faculty_members.php', 'حدث خطأ أثناء حذف العضو.', 'error');
    }
    mysqli_stmt_close($stmt);
} else {
    error_log("Delete member prepare failed: " . mysqli_error($conn));
    flash_redirect('faculty_members.php', 'حدث خطأ أثناء حذف العضو.', 'error');
}

header("Location: faculty_members.php");
exit();
?>

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
    header("Location: classrooms.php");
    exit();
}

if (!isset($_POST['csrf_token']) || !csrf_validate($_POST['csrf_token'])) {
    flash_redirect('classrooms.php', 'تعذر حذف القاعة الدراسية: طلب غير صالح.', 'error');
}

if (!isset($_POST['id']) || !ctype_digit($_POST['id'])) {
    flash_redirect('classrooms.php', 'حدث خطأ أثناء حذف القاعة الدراسية: معرف غير صالح.', 'error');
}

$classroom_id = (int)$_POST['id'];
$deleteQuery = "DELETE FROM classrooms WHERE classroom_id = ?";
$stmt = mysqli_prepare($conn, $deleteQuery);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $classroom_id);
    if (mysqli_stmt_execute($stmt)) {
        flash_redirect('classrooms.php', 'تم حذف القاعة الدراسية بنجاح!', 'success');
    } else {
        error_log("Delete classroom failed: " . mysqli_stmt_error($stmt));
        flash_redirect('classrooms.php', 'حدث خطأ أثناء حذف القاعة الدراسية.', 'error');
    }
    mysqli_stmt_close($stmt);
} else {
    error_log("Delete classroom prepare failed: " . mysqli_error($conn));
    flash_redirect('classrooms.php', 'حدث خطأ أثناء حذف القاعة الدراسية.', 'error');
}

header("Location: classrooms.php");
exit();

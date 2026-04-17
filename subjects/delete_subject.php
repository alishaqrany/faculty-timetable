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
    header("Location: subjects.php");
    exit();
}

if (!isset($_POST['csrf_token']) || !csrf_validate($_POST['csrf_token'])) {
    flash_redirect('subjects.php', 'تعذر حذف المادة: طلب غير صالح.', 'error');
}

if (!isset($_POST['subject_id']) || !ctype_digit($_POST['subject_id'])) {
    flash_redirect('subjects.php', 'تعذر حذف المادة: معرف غير صالح.', 'error');
}

$subject_id = (int)$_POST['subject_id'];
$deleteQuery = "DELETE FROM subjects WHERE subject_id = ?";
$stmt = mysqli_prepare($conn, $deleteQuery);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $subject_id);
    if (mysqli_stmt_execute($stmt)) {
        flash_redirect('subjects.php', 'تم حذف المادة بنجاح!', 'success');
    } else {
        error_log("Delete subject failed: " . mysqli_stmt_error($stmt));
        flash_redirect('subjects.php', 'حدث خطأ أثناء حذف المادة.', 'error');
    }
    mysqli_stmt_close($stmt);
} else {
    error_log("Delete subject prepare failed: " . mysqli_error($conn));
    flash_redirect('subjects.php', 'حدث خطأ أثناء حذف المادة.', 'error');
}

header("Location: subjects.php");
exit();
?>

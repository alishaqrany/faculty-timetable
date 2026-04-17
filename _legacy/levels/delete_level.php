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
    header("Location: levels.php");
    exit();
}

if (!isset($_POST['csrf_token']) || !csrf_validate($_POST['csrf_token'])) {
    flash_redirect('levels.php', 'تعذر حذف الفرقة الدراسية: طلب غير صالح.', 'error');
}

if (!isset($_POST['id']) || !ctype_digit($_POST['id'])) {
    flash_redirect('levels.php', 'حدث خطأ أثناء حذف الفرقة الدراسية: معرف غير صالح.', 'error');
}

$level_id = (int)$_POST['id'];
$deleteQuery = "DELETE FROM levels WHERE level_id = ?";
$stmt = mysqli_prepare($conn, $deleteQuery);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $level_id);
    if (mysqli_stmt_execute($stmt)) {
        flash_redirect('levels.php', 'تم حذف الفرقة الدراسية بنجاح!', 'success');
    } else {
        error_log("Delete level failed: " . mysqli_stmt_error($stmt));
        flash_redirect('levels.php', 'حدث خطأ أثناء حذف الفرقة الدراسية.', 'error');
    }
    mysqli_stmt_close($stmt);
} else {
    error_log("Delete level prepare failed: " . mysqli_error($conn));
    flash_redirect('levels.php', 'حدث خطأ أثناء حذف الفرقة الدراسية.', 'error');
}

header("Location: levels.php");
exit();

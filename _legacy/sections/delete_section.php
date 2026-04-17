<?php
require_once("../db_config.php");
require_once("../core/csrf.php");
require_once("../core/flash.php");
session_start();

// التحقق من وجود معرف الجلسة للمستخدم المسجل
if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: sections.php");
    exit();
}

if (!isset($_POST['csrf_token']) || !csrf_validate($_POST['csrf_token'])) {
    flash_redirect('sections.php', 'تعذر حذف السكشن: طلب غير صالح.', 'error');
}

if (!isset($_POST['id']) || !ctype_digit($_POST['id'])) {
    flash_redirect('sections.php', 'تعذر حذف السكشن: معرف غير صالح.', 'error');
}

$section_id = (int)$_POST['id'];

$deleteQuery = "DELETE FROM sections WHERE section_id = ?";
$stmt = mysqli_prepare($conn, $deleteQuery);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $section_id);
    if (mysqli_stmt_execute($stmt)) {
        flash_redirect('sections.php', 'تم حذف السكشن بنجاح!', 'success');
    } else {
        error_log("Delete section failed: " . mysqli_stmt_error($stmt));
        flash_redirect('sections.php', 'حدث خطأ أثناء حذف السكشن.', 'error');
    }
    mysqli_stmt_close($stmt);
} else {
    error_log("Delete section prepare failed: " . mysqli_error($conn));
    flash_redirect('sections.php', 'حدث خطأ أثناء حذف السكشن.', 'error');
}

header("Location: sections.php");
exit();

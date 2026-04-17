<?php
require_once __DIR__ . '/../../core/bootstrap.php';
require_auth('../../login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash_redirect('list.php', 'طريقة الطلب غير مدعومة.', 'error');
}

if (!isset($_POST['csrf_token']) || !csrf_validate($_POST['csrf_token'])) {
    flash_redirect('list.php', 'تعذر حذف القسم: طلب غير صالح.', 'error');
}

if (!isset($_POST['id']) || !ctype_digit($_POST['id'])) {
    flash_redirect('list.php', 'حدث خطأ أثناء حذف القسم: معرف غير صالح.', 'error');
}

$department_id = (int)$_POST['id'];
$deleteQuery = "DELETE FROM departments WHERE department_id = ?";
$stmt = mysqli_prepare($conn, $deleteQuery);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $department_id);
    if (mysqli_stmt_execute($stmt)) {
        flash_redirect('list.php', 'تم حذف القسم بنجاح!', 'success');
    } else {
        error_log('Delete department failed: ' . mysqli_stmt_error($stmt));
        flash_redirect('list.php', 'حدث خطأ أثناء حذف القسم.', 'error');
    }
    mysqli_stmt_close($stmt);
} else {
    error_log('Delete department prepare failed: ' . mysqli_error($conn));
    flash_redirect('list.php', 'حدث خطأ أثناء حذف القسم.', 'error');
}

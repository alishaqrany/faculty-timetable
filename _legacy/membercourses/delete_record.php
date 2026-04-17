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
    header("Location: membercourses.php");
    exit();
}

if (!isset($_POST['csrf_token']) || !csrf_validate($_POST['csrf_token'])) {
    flash_redirect('membercourses.php', 'تعذر حذف السجل: طلب غير صالح.', 'error');
}

if (!isset($_POST['id'])) {
    header("Location: membercourses.php");
    exit();
}

$id = $_POST['id'];

if (!ctype_digit($id)) {
    flash_redirect('membercourses.php', 'تعذر حذف السجل: معرف غير صالح.', 'error');
}

$id = (int)$id;

$deleteQuery = "DELETE FROM member_courses WHERE member_course_id = ?";
$stmt = $conn->prepare($deleteQuery);

if ($stmt) {
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        flash_redirect('membercourses.php', 'تم حذف السجل بنجاح!', 'success');
    } else {
        error_log("Delete member course failed: " . $stmt->error);
        flash_redirect('membercourses.php', 'حدث خطأ أثناء حذف السجل.', 'error');
    }
    $stmt->close();
} else {
    error_log("Delete member course prepare failed: " . $conn->error);
    flash_redirect('membercourses.php', 'حدث خطأ أثناء حذف السجل.', 'error');
}

header("Location: membercourses.php");
exit();
?>

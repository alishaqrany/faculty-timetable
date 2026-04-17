<?php
require_once("db_config.php");
require_once("core/csrf.php");
require_once("core/flash.php");

session_start();

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: scheduling.php");
    exit();
}

if (!isset($_POST['csrf_token']) || !csrf_validate($_POST['csrf_token'])) {
    flash_redirect('scheduling.php', 'تعذر حذف السجل: طلب غير صالح.', 'error');
}

if (!isset($_POST['id']) || !ctype_digit($_POST['id'])) {
    flash_redirect('scheduling.php', 'تعذر حذف السجل: معرف غير صالح.', 'error');
}

$timetableId = (int)$_POST['id'];
$userId = (int)$_SESSION['member_id'];

$ownerQuery = "SELECT 1
               FROM timetable t
               INNER JOIN member_courses mc ON t.member_course_id = mc.member_course_id
               WHERE t.timetable_id = ? AND mc.member_id = ?
               LIMIT 1";
$ownerStmt = $conn->prepare($ownerQuery);

if (!$ownerStmt) {
    error_log("Delete scheduling owner check prepare failed: " . $conn->error);
    flash_redirect('scheduling.php', 'حدث خطأ أثناء التحقق من الصلاحيات.', 'error');
}

$ownerStmt->bind_param("ii", $timetableId, $userId);
$ownerStmt->execute();
$ownerResult = $ownerStmt->get_result();
$canDelete = $ownerResult && $ownerResult->num_rows > 0;
$ownerStmt->close();

if (!$canDelete) {
    flash_redirect('scheduling.php', 'غير مسموح بحذف هذا السجل.', 'error');
}

$deleteQuery = "DELETE FROM timetable WHERE timetable_id = ?";
$stmt = $conn->prepare($deleteQuery);

if ($stmt) {
    $stmt->bind_param("i", $timetableId);
    if ($stmt->execute()) {
        flash_redirect('scheduling.php', 'تم حذف السجل بنجاح!', 'success');
    } else {
        error_log("Delete scheduling failed: " . $stmt->error);
        flash_redirect('scheduling.php', 'حدث خطأ أثناء حذف السجل.', 'error');
    }
    $stmt->close();
} else {
    error_log("Delete scheduling prepare failed: " . $conn->error);
    flash_redirect('scheduling.php', 'حدث خطأ أثناء حذف السجل.', 'error');
}
?>

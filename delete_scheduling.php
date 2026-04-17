<?php
require_once("db_config.php");

session_start();

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header("Location: scheduling.php");
    exit();
}

$timetableId = (int)$_GET['id'];
$deleteQuery = "DELETE FROM timetable WHERE timetable_id = ?";
$stmt = $conn->prepare($deleteQuery);

if ($stmt) {
    $stmt->bind_param("i", $timetableId);
    $stmt->execute();
    $stmt->close();
}

header("Location: scheduling.php");
exit();
?>

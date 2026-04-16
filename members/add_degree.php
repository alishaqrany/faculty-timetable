<?php
require_once("../db_config.php");
session_start();

if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['degree_name'])) {
    $degree_name = mysqli_real_escape_string($conn, $_POST['degree_name']);

    $insertQuery = "INSERT INTO academic_degrees (degree_name) VALUES ('$degree_name')";
    $insertResult = mysqli_query($conn, $insertQuery);

    if ($insertResult) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'طلب غير صالح.']);
}
?>

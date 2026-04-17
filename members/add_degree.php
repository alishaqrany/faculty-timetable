<?php
require_once("../db_config.php");
session_start();

if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['degree_name'])) {
    $degree_name = trim($_POST['degree_name']);

    $insertQuery = "INSERT INTO academic_degrees (degree_name) VALUES (?)";
    $insertStmt = mysqli_prepare($conn, $insertQuery);
    if ($insertStmt) {
        mysqli_stmt_bind_param($insertStmt, "s", $degree_name);
    }
    $insertResult = $insertStmt && mysqli_stmt_execute($insertStmt);

    if ($insertResult) {
        echo json_encode(['success' => true]);
    } else {
        if ($insertStmt) {
            error_log("Add degree failed: " . mysqli_stmt_error($insertStmt));
        } else {
            error_log("Add degree prepare failed: " . mysqli_error($conn));
        }
        echo json_encode(['success' => false, 'message' => 'تعذر إضافة الدرجة العلمية.']);
    }

    if ($insertStmt) {
        mysqli_stmt_close($insertStmt);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'طلب غير صالح.']);
}
?>

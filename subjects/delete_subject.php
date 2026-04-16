<?php
require_once("../db_config.php");
session_start();

if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['subject_id'])) {
        $subject_id = $_GET['subject_id'];

        // قم بتنفيذ استعلام الحذف
        $deleteQuery = "DELETE FROM subjects WHERE subject_id = $subject_id";
        $deleteResult = mysqli_query($conn, $deleteQuery);

        if ($deleteResult) {
            echo "<p>تم حذف المادة بنجاح!</p>";
            header("Location: subjects.php");
            exit();

        } else {
            echo "<p>حدث خطأ أثناء حذف المادة: " . mysqli_error($conn) . "</p>";
        }
    }
}
?>

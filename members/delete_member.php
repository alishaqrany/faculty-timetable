<?php
require_once("../db_config.php");
session_start();

if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

// التحقق من وجود معرف العضو في العنوان
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$member_id = $_GET['id'];


        // قم بتنفيذ استعلام الحذف
        $deleteQuery = "DELETE FROM faculty_members WHERE member_id = $member_id";
        $deleteResult = mysqli_query($conn, $deleteQuery);

        if ($deleteResult) {
            echo "<p>تم حذف المادة بنجاح!</p>";
            header("Location: index.php");
            exit();

        } else {
            echo "<p>حدث خطأ أثناء حذف المادة: " . mysqli_error($conn) . "</p>";
        }
?>

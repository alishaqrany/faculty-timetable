<?php
session_start();

// حذف جلسة المستخدم
session_unset();
session_destroy();

// توجيه المستخدم إلى صفحة تسجيل الدخول
header("Location: login.php");
exit();
?>

<!-- <a href="logout.php">تسجيل الخروج</a> -->

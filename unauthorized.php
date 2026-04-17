<?php
session_start();

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!-- <!DOCTYPE html>
<html>
<head>
    <title>عذرًا، ليس دورك حاليًا</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <h2>عذرًا، ليس دورك حاليًا</h2>

    <?php
// التحقق من وجود معرف العضو المسجل
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php"); // إعادة توجيه المستخدم إلى صفحة تسجيل الدخول إذا لم يكن مسجل الدخول
    exit();
}

// معرف العضو الحالي المسجل
$userId = $_SESSION['member_id'];

// الحصول على بيانات العضو الحالي
$currentUserQuery = "SELECT fm.member_name, fm.ranking, fm.role 
                     FROM faculty_members fm 
                     INNER JOIN users u ON fm.member_id = u.member_id 
                     WHERE u.member_id = $userId AND u.registration_status = 1";
$currentUserResult = $conn->query($currentUserQuery);

if ($currentUserResult->num_rows > 0) {
    $currentUserRow = $currentUserResult->fetch_assoc();
    $currentUserName = $currentUserRow['member_name'];
    $currentRanking = $currentUserRow['ranking'];
    $currentRole = $currentUserRow['role'];

    echo "<p>اسم العضو: $currentUserName</p>";
    echo "<p>الدور في التسكين: $currentRole</p>";
    echo "<p>ترتيب العضو: $currentRanking</p>";

    // الحصول على بيانات العضو الذي يمكنه التسكين حاليًا باستثناء العضو admin
    $nextUserQuery = "SELECT fm.member_name, fm.ranking 
                      FROM faculty_members fm 
                      INNER JOIN users u ON fm.member_id = u.member_id 
                      WHERE u.registration_status = 1 AND fm.ranking > $currentRanking AND u.username != 'admin'
                      ORDER BY fm.ranking ASC LIMIT 1";
    $nextUserResult = $conn->query($nextUserQuery);

    if ($nextUserResult->num_rows > 0) {
        $nextUserRow = $nextUserResult->fetch_assoc();
        $nextUserName = $nextUserRow['member_name'];
        $nextUserRanking = $nextUserRow['ranking'];

        // عرض مقارنة الـ "ranking" بين العضو الحالي والعضو الذي يمكنه التسكين حاليًا
        $differenceInRanking = $nextUserRanking - $currentRanking;

        echo "<p>اسم العضو الذي يمكنه التسكين حاليًا: $nextUserName</p>";
        echo "<p>ترتيب العضو: $nextUserRanking</p>";
        echo "<p>الفارق في الترتيب بين العضوين: $differenceInRanking</p>";
    } else {
        echo "<p>عذرًا، لا يوجد أعضاء آخرين حاليًا يمكنهم التسكين باستثناء العضو admin</p>";
    }

    // التحقق إذا كان العضو الحالي قد اختار مواعيده بالفعل
    $chosenScheduleQuery = "SELECT * FROM timetable WHERE member_course_id IN (SELECT member_course_id FROM member_courses WHERE member_id = ?)";
    $chosenScheduleStmt = $conn->prepare($chosenScheduleQuery);
    if ($chosenScheduleStmt) {
        $chosenScheduleStmt->bind_param("i", $userId);
        $chosenScheduleStmt->execute();
        $chosenScheduleResult = $chosenScheduleStmt->get_result();
    } else {
        $chosenScheduleResult = false;
    }

    if ($chosenScheduleResult->num_rows > 0) {
        echo "<p>عذرًا، قد قمت بتسجيل مواعيدك بالفعل. يرجى التواصل مع المسئول عن النظام لإجراء التعديلات.</p>";
    }

    if ($chosenScheduleStmt) {
        $chosenScheduleStmt->close();
    }

} else {
    echo "<p>عذرًا، لا يوجد أعضاء حاليًا يمكنهم التسكين</p>";
}
?>


</body>
</html> -->

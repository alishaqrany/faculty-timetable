<?php
// تأكد من إضافة كود الاتصال بقاعدة البيانات هنا
require_once("db_config.php");

// التحقق من تسجيل الدخول
session_start();

// التحقق من وجود معرف الجلسة للمستخدم المسجل
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php"); // إعادة توجيه المستخدم إلى صفحة تسجيل الدخول إذا لم يكن مسجل الدخول
    exit();
}

// التحقق من قيمة حقل registration_status في جدول users
$userId = $_SESSION['member_id'];





// التحقق من الضغط على زر "تمرير الدور"
if (isset($_POST['pass_role'])) {
    // الحصول على قيمة الحقل ranking للعضو الحالي
    $userRankingQuery = "SELECT ranking FROM faculty_members WHERE member_id = $userId";
    $userRankingResult = $conn->query($userRankingQuery);

    if ($userRankingResult->num_rows > 0) {
        $userRankingRow = $userRankingResult->fetch_assoc();
        $userRanking = $userRankingRow['ranking'];
    
        // الحصول على معرف العضو التالي في الترتيب لتحديث حالة التسجيل له إلى 1
        $nextUserId = 0;
        $nextUserQuery = "SELECT member_id FROM faculty_members WHERE member_id IN (
            SELECT member_id FROM users WHERE registration_status = 0 AND member_id IN (
                SELECT member_id FROM faculty_members WHERE ranking = $userRanking + 1
            )
        ) ORDER BY ranking ASC LIMIT 1";        
        $nextUserResult = $conn->query($nextUserQuery);
        
        if ($nextUserResult->num_rows > 0) {
            $nextUserRow = $nextUserResult->fetch_assoc();
            $nextUserId = $nextUserRow['member_id'];
        }
    
        if ($nextUserId > 0) {
            // جعل قيمة الحقل registration_status للعضو التالي في الترتيب تساوي 1
            $updateNextUserStatusQuery = "UPDATE users SET registration_status = 1 WHERE member_id = $nextUserId";
            $conn->query($updateNextUserStatusQuery);
        
            // جعل قيمة الحقل registration_status للعضو الحالي تساوي 0
            $updateCurrentUserStatusQuery = "UPDATE users SET registration_status = 0 WHERE member_id = $userId";
            $conn->query($updateCurrentUserStatusQuery);
        }
    }
}


$checkRegistrationStatusQuery = "SELECT registration_status FROM users WHERE member_id = $userId";
$checkRegistrationStatusResult = $conn->query($checkRegistrationStatusQuery);

if ($checkRegistrationStatusResult) {
    $row = $checkRegistrationStatusResult->fetch_assoc();
    $registrationStatus = $row['registration_status'];

    // التحقق من قيمة الحقل للسماح بإدخال البيانات
    if ($registrationStatus != 1) {
        // إعادة توجيه المستخدم إلى صفحة غير مسموح له بالوصول إليها
        header("Location: unauthorized.php");
        exit();
    }
} else {
    echo "حدث خطأ أثناء استعلام قاعدة البيانات: " . $conn->error;
}

// استعلام للحصول على معلومات المستخدم المسجل
$memberId = $_SESSION['member_id'];

// استعلام لاسترداد البيانات المرتبطة من الجداول الأخرى
$departmentsQuery = "SELECT * FROM departments";
$departmentsResult = $conn->query($departmentsQuery);

$levelsQuery = "SELECT * FROM levels";
$levelsResult = $conn->query($levelsQuery);

$facultyMembersQuery = "SELECT * FROM faculty_members";
$facultyMembersResult = $conn->query($facultyMembersQuery);

$subjectsQuery = "SELECT * FROM subjects";
$subjectsResult = $conn->query($subjectsQuery);

$sectionsQuery = "SELECT * FROM sections";
$sectionsResult = $conn->query($sectionsQuery);

$classroomsQuery = "SELECT * FROM classrooms";
$classroomsResult = $conn->query($classroomsQuery);

$sessionsQuery = "SELECT * FROM sessions";
$sessionsResult = $conn->query($sessionsQuery);


// التحقق من الضغط على زر "تمرير الدور"

// إدخال البيانات في جدول "timetable" عند تقديم النموذج
if (isset($_POST['submit'])) {
    $member_course_id = $_POST["member_course_id"];
    $classroom_id = $_POST["classroom_id"];
    $session_id = $_POST["session_id"];

    // التحقق من عدم تكرار قيم الفترة والقاعة معًا في الجدول
    $duplicateQuery = "SELECT *
                FROM timetable t
                INNER JOIN member_courses mc ON t.member_course_id = mc.member_course_id
                INNER JOIN subjects s ON mc.subject_id = s.subject_id
                INNER JOIN sections sec ON mc.section_id = sec.section_id
                WHERE (t.classroom_id = $classroom_id AND t.session_id = $session_id)
                OR (t.session_id = $session_id AND s.department_id = (SELECT subject.department_id FROM subjects subject WHERE subject.subject_id = mc.subject_id) AND s.level_id = (SELECT subject.level_id FROM subjects subject WHERE subject.subject_id = mc.subject_id))";
    $duplicateResult = $conn->query($duplicateQuery);

    if ($duplicateResult->num_rows > 0) {
        echo "<p style='color: red;'>خطأ: تم اختيار الفترة والفرقة أو الفترة والقسم والمستوى مسبقًا</p>";
    } else {
        $insertQuery = "INSERT INTO timetable (member_course_id, classroom_id, session_id) VALUES ($member_course_id, $classroom_id, $session_id)";

        if ($conn->query($insertQuery) === TRUE) {
            echo "تمت إضافة البيانات بنجاح";
        } else {
            echo "حدث خطأ أثناء إضافة البيانات: " . $conn->error;
        }
    }
}

// استعلام لجلب البيانات المرتبطة من الجداول الأخرى
$recordsQuery = "
    SELECT fm.member_name, s.subject_name, sec.section_name, se.session_name, c.classroom_name, lv.level_name, d.department_name
    FROM timetable t
    INNER JOIN member_courses mc ON t.member_course_id = mc.member_course_id
    INNER JOIN faculty_members fm ON mc.member_id = fm.member_id
    INNER JOIN subjects s ON mc.subject_id = s.subject_id
    INNER JOIN sections sec ON mc.section_id = sec.section_id
    INNER JOIN sessions se ON t.session_id = se.session_id
    INNER JOIN levels lv ON s.level_id = lv.level_id
    INNER JOIN classrooms c ON t.classroom_id = c.classroom_id
    INNER JOIN departments d ON s.department_id = d.department_id
";

$recordsResult = $conn->query($recordsQuery);

?>

<!DOCTYPE html>
<html>
<head>
    <title>إدخال بيانات الجدول الزمني</title>
    <link rel="stylesheet" type="text/css" href="style.css">

</head>
<body>

    <?php include 'navbar.php'; ?>

    <h2>إدخال بيانات الجدول الزمني</h2>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <label for="member_course_id">المادة:</label>
        <select name="member_course_id" id="member_course_id">
            <?php
            $facultyMemberId = $_SESSION['member_id']; // استرداد معرف عضو هيئة التدريس المسجل

            $memberCoursesQuery = "SELECT mc.member_course_id, s.subject_name 
                                    FROM member_courses mc
                                    INNER JOIN subjects s ON mc.subject_id = s.subject_id
                                    WHERE mc.member_id = $facultyMemberId";

            $memberCoursesResult = $conn->query($memberCoursesQuery);

            if ($memberCoursesResult->num_rows > 0) {
                while ($row = $memberCoursesResult->fetch_assoc()) {
                    echo "<option value='" . $row["member_course_id"] . "'>" . $row["subject_name"] . "</option>";
                }
            }
            ?>
        </select><br><br>

        <label for="classroom_id">القاعة:</label>
        <select name="classroom_id" id="classroom_id">
            <?php
            if ($classroomsResult->num_rows > 0) {
                while ($row = $classroomsResult->fetch_assoc()) {
                    echo "<option value='" . $row["classroom_id"] . "'>" . $row["classroom_name"] . "</option>";
                }
            }
            ?>
        </select><br><br>

        <label for="session_id">الفترة:</label>
        <select name="session_id" id="session_id">
            <?php
            if ($sessionsResult->num_rows > 0) {
                while ($row = $sessionsResult->fetch_assoc()) {
                    echo "<option value='" . $row["session_id"] . "'>" . $row["session_name"] . "</option>";
                }
            }
            ?>
        </select><br><br>

        <input type="submit" value="إضافة" name="submit">
    </form>
    
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="submit" value="تمرير الدور" name="pass_role" style="background-color: orange">
</form>

    <a style="background-color: red; color:white; font-size:20px" href="logout.php">تسجيل الخروج</a>

    <table border='1' cellpadding='10'>
        <tr>
            <th>اسم العضو</th>
            <th>اسم القسم</th>
            <th>اسم الفرقة</th>
            <th>اسم السكشن</th>
            <th>اسم المادة</th>
            <th>اسم الفترة</th>
            <th>اسم القاعة</th>
        </tr>
        <?php
        if ($recordsResult) {
            while ($row = $recordsResult->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['member_name'] . "</td>";
                echo "<td>" . $row['department_name'] . "</td>";
                echo "<td>" . $row['level_name'] . "</td>";
                echo "<td>" . $row['section_name'] . "</td>";
                echo "<td>" . $row['subject_name'] . "</td>";
                echo "<td>" . $row['session_name'] . "</td>";
                echo "<td>" . $row['classroom_name'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>لا توجد سجلات محفوظة</td></tr>";
        }
        ?>
    </table>

</body>
</html>

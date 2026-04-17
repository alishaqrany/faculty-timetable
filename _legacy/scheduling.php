<?php
// الاتصال بقاعدة البيانات
require_once("db_config.php");
require_once("core/csrf.php");
require_once("core/flash.php");

// التحقق من تسجيل الدخول
session_start();

// التحقق من وجود معرف الجلسة للمستخدم المسجل
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php"); // إعادة توجيه المستخدم إلى صفحة تسجيل الدخول إذا لم يكن مسجل الدخول
    exit();
}

$userId = $_SESSION['member_id'];
$userId = (int)$userId;
list($message, $message_type) = flash_consume();

// التحقق من الضغط على زر "تمرير الدور"
if (isset($_POST['pass_role'])) {
    if (!isset($_POST['csrf_token']) || !csrf_validate($_POST['csrf_token'])) {
        flash_redirect('scheduling.php', 'طلب غير صالح.', 'error');
    } else {
    // الحصول على قيمة الحقل ranking للعضو الحالي
    $userRankingQuery = "SELECT ranking FROM faculty_members WHERE member_id = ?";
    $userRankingStmt = $conn->prepare($userRankingQuery);
    if ($userRankingStmt) {
        $userRankingStmt->bind_param("i", $userId);
        $userRankingStmt->execute();
        $userRankingResult = $userRankingStmt->get_result();
    } else {
        $userRankingResult = false;
    }

    if ($userRankingResult && $userRankingResult->num_rows > 0) {
        $userRankingRow = $userRankingResult->fetch_assoc();
        $userRanking = $userRankingRow['ranking'];
    
        // الحصول على معرف العضو التالي في الترتيب لتحديث حالة التسجيل له إلى 1
        $nextRank = (int)$userRanking + 1;
        $nextUserQuery = "SELECT member_id FROM faculty_members WHERE ranking = ? LIMIT 1";
        $nextUserStmt = $conn->prepare($nextUserQuery);
        if ($nextUserStmt) {
            $nextUserStmt->bind_param("i", $nextRank);
            $nextUserStmt->execute();
            $nextUserResult = $nextUserStmt->get_result();
        } else {
            $nextUserResult = false;
        }
        
        if ($nextUserResult && $nextUserResult->num_rows > 0) {
            $nextUserRow = $nextUserResult->fetch_assoc();
            $nextUserId = $nextUserRow['member_id'];
            $nextUserId = (int)$nextUserId;

            // جعل قيمة الحقل registration_status للعضو التالي في الترتيب تساوي 1
            $updateNextUserStatusQuery = "UPDATE users SET registration_status = 1 WHERE member_id = ?";
            $updateNextStmt = $conn->prepare($updateNextUserStatusQuery);
            if ($updateNextStmt) {
                $updateNextStmt->bind_param("i", $nextUserId);
                $updateNextStmt->execute();
                $updateNextStmt->close();
            }
        
            // جعل قيمة الحقل registration_status للعضو الحالي تساوي 0
            $updateCurrentUserStatusQuery = "UPDATE users SET registration_status = 0 WHERE member_id = ?";
            $updateCurrentStmt = $conn->prepare($updateCurrentUserStatusQuery);
            if ($updateCurrentStmt) {
                $updateCurrentStmt->bind_param("i", $userId);
                $updateCurrentStmt->execute();
                $updateCurrentStmt->close();
            }

            flash_redirect('scheduling.php', 'تم تمرير الدور بنجاح.', 'success');
        } else {
            flash_redirect('scheduling.php', 'لا يوجد عضو تالٍ لتمرير الدور.', 'error');
        }

        if ($nextUserStmt) {
            $nextUserStmt->close();
        }
    }

    if ($userRankingStmt) {
        $userRankingStmt->close();
    }

    flash_redirect('scheduling.php', 'تعذر تمرير الدور.', 'error');
    }
}

// التحقق من حالة التسجيل
$checkRegistrationStatusQuery = "SELECT registration_status FROM users WHERE member_id = ?";
$checkRegistrationStatusStmt = $conn->prepare($checkRegistrationStatusQuery);
if ($checkRegistrationStatusStmt) {
    $checkRegistrationStatusStmt->bind_param("i", $userId);
    $checkRegistrationStatusStmt->execute();
    $checkRegistrationStatusResult = $checkRegistrationStatusStmt->get_result();
} else {
    $checkRegistrationStatusResult = false;
}

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
    error_log("Check registration status failed: " . $conn->error);
    flash_redirect('unauthorized.php', 'حدث خطأ أثناء التحقق من الصلاحيات.', 'error');
}

if ($checkRegistrationStatusStmt) {
    $checkRegistrationStatusStmt->close();
}

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

// إدخال البيانات في جدول "timetable" عند تقديم النموذج
if (isset($_POST['submit'])) {
    if (!isset($_POST['csrf_token']) || !csrf_validate($_POST['csrf_token'])) {
        flash_redirect('scheduling.php', 'طلب غير صالح.', 'error');
    } else {
    $member_course_id = $_POST["member_course_id"];
    $classroom_id = $_POST["classroom_id"];
    $session_id = $_POST["session_id"];

    if (!ctype_digit((string)$member_course_id) || !ctype_digit((string)$classroom_id) || !ctype_digit((string)$session_id)) {
        flash_redirect('scheduling.php', 'خطأ: بيانات غير صالحة.', 'error');
    } else {
        $member_course_id = (int)$member_course_id;
        $classroom_id = (int)$classroom_id;
        $session_id = (int)$session_id;

    $ownerCheckQuery = "SELECT 1 FROM member_courses WHERE member_course_id = ? AND member_id = ? LIMIT 1";
    $ownerCheckStmt = $conn->prepare($ownerCheckQuery);
    if (!$ownerCheckStmt) {
        error_log("Owner check prepare failed: " . $conn->error);
        flash_redirect('scheduling.php', 'حدث خطأ أثناء التحقق من صلاحيات المادة.', 'error');
    }

    $ownerCheckStmt->bind_param("ii", $member_course_id, $userId);
    $ownerCheckStmt->execute();
    $ownerCheckResult = $ownerCheckStmt->get_result();
    $hasOwnership = $ownerCheckResult && $ownerCheckResult->num_rows > 0;
    $ownerCheckStmt->close();

    if (!$hasOwnership) {
        flash_redirect('scheduling.php', 'غير مسموح بإضافة جدول لمادة لا تخصك.', 'error');
    }

    // التحقق من عدم تكرار قيم الفترة والقاعة معًا في الجدول
    $duplicateQuery = "SELECT *
                FROM timetable t
                INNER JOIN member_courses mc ON t.member_course_id = mc.member_course_id
                INNER JOIN subjects s ON mc.subject_id = s.subject_id
                INNER JOIN sections sec ON mc.section_id = sec.section_id
                WHERE (t.classroom_id = ? AND t.session_id = ?)
                OR (t.session_id = ? AND s.department_id = (SELECT subject.department_id FROM subjects subject WHERE subject.subject_id = mc.subject_id) AND s.level_id = (SELECT subject.level_id FROM subjects subject WHERE subject.subject_id = mc.subject_id))";
    $duplicateStmt = $conn->prepare($duplicateQuery);
    if ($duplicateStmt) {
        $duplicateStmt->bind_param("iii", $classroom_id, $session_id, $session_id);
        $duplicateStmt->execute();
        $duplicateResult = $duplicateStmt->get_result();
    } else {
        $duplicateResult = false;
    }

    if ($duplicateResult && $duplicateResult->num_rows > 0) {
        flash_redirect('scheduling.php', 'خطأ: تم اختيار الفترة والفرقة أو الفترة والقسم والمستوى مسبقًا.', 'error');
    } else {
        $insertQuery = "INSERT INTO timetable (member_course_id, classroom_id, session_id) VALUES (?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);

        if ($insertStmt) {
            $insertStmt->bind_param("iii", $member_course_id, $classroom_id, $session_id);
        }

        if ($insertStmt && $insertStmt->execute()) {
            flash_redirect('scheduling.php', 'تمت إضافة البيانات بنجاح.', 'success');
        } else {
            if ($insertStmt) {
                error_log("Insert timetable row failed: " . $insertStmt->error);
            } else {
                error_log("Insert timetable prepare failed: " . $conn->error);
            }
            flash_redirect('scheduling.php', 'حدث خطأ أثناء إضافة البيانات.', 'error');
        }

        if ($insertStmt) {
            $insertStmt->close();
        }
    }

    if ($duplicateStmt) {
        $duplicateStmt->close();
    }
    }
    }
}

// استعلام لجلب البيانات المرتبطة من الجداول الأخرى
$recordsQuery = "
    SELECT t.timetable_id, fm.member_name, s.subject_name, sec.section_name, se.session_name, c.classroom_name, lv.level_name, d.department_name, mc.member_id AS owner_member_id
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

    <?php if ($message): ?>
        <p style="color: <?php echo $message_type === 'error' ? 'red' : 'green'; ?>;"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <h2>إدخال بيانات الجدول الزمني</h2>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
        <label for="member_course_id">المادة:</label>
        <select name="member_course_id" id="member_course_id">
            <?php
            $facultyMemberId = (int)$_SESSION['member_id']; // استرداد معرف عضو هيئة التدريس المسجل

            $memberCoursesQuery = "SELECT mc.member_course_id, s.subject_name 
                                    FROM member_courses mc
                                    INNER JOIN subjects s ON mc.subject_id = s.subject_id
                                    WHERE mc.member_id = ?";

            $memberCoursesStmt = $conn->prepare($memberCoursesQuery);
            if ($memberCoursesStmt) {
                $memberCoursesStmt->bind_param("i", $facultyMemberId);
                $memberCoursesStmt->execute();
                $memberCoursesResult = $memberCoursesStmt->get_result();
            } else {
                $memberCoursesResult = false;
            }

            if ($memberCoursesResult && $memberCoursesResult->num_rows > 0) {
                while ($row = $memberCoursesResult->fetch_assoc()) {
                    echo "<option value='" . $row["member_course_id"] . "'>" . $row["subject_name"] . "</option>";
                }
            }

            if ($memberCoursesStmt) {
                $memberCoursesStmt->close();
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
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
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
            <th>التعديلات</th>
        </tr>
        <?php
        if ($recordsResult) {
            $csrfToken = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
            while ($row = $recordsResult->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['member_name'] . "</td>";
                echo "<td>" . $row['department_name'] . "</td>";
                echo "<td>" . $row['level_name'] . "</td>";
                echo "<td>" . $row['section_name'] . "</td>";
                echo "<td>" . $row['subject_name'] . "</td>";
                echo "<td>" . $row['session_name'] . "</td>";
                echo "<td>" . $row['classroom_name'] . "</td>";
                echo "<td>";
                if ((int)$row['owner_member_id'] === $userId) {
                    echo "<a href='edit_scheduling.php?id=" . $row['timetable_id'] . "'>تعديل</a> | ";
                    echo "<form method='POST' action='delete_scheduling.php' style='display:inline;' onsubmit='return confirm(\"هل أنت متأكد أنك تريد حذف هذا السجل؟\");'><input type='hidden' name='csrf_token' value='{$csrfToken}'><input type='hidden' name='id' value='" . $row['timetable_id'] . "'><button type='submit'>حذف</button></form>";
                } else {
                    echo "غير متاح";
                }
                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='8'>لا توجد سجلات محفوظة</td></tr>";
        }
        ?>
    </table>

</body>
</html>

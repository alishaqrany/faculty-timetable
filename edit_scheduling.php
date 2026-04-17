<?php
require_once("db_config.php");
require_once("core/csrf.php");
require_once("core/flash.php");

session_start();

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit();
}

list($message, $message_type) = flash_consume();

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header("Location: scheduling.php");
    exit();
}

$timetableId = (int)$_GET['id'];
$userId = $_SESSION['member_id'];

$query = "SELECT t.timetable_id, t.member_course_id, t.classroom_id, t.session_id
          FROM timetable t
          INNER JOIN member_courses mc ON t.member_course_id = mc.member_course_id
          WHERE t.timetable_id = ? AND mc.member_id = ?";
$stmt = $conn->prepare($query);

if ($stmt) {
    $stmt->bind_param("ii", $timetableId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = false;
}

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    if ($stmt) {
        error_log("Edit scheduling load failed: " . $stmt->error);
    } else {
        error_log("Edit scheduling prepare failed: " . $conn->error);
    }
    flash_redirect('scheduling.php', 'تعذر تحميل السجل المطلوب أو لا تملك صلاحية تعديله.', 'error');
}

if ($stmt) {
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !csrf_validate($_POST['csrf_token'])) {
        flash_redirect('edit_scheduling.php?id=' . $timetableId, 'طلب غير صالح.', 'error');
    }

    $member_course_id = $_POST["member_course_id"];
    $classroom_id = $_POST["classroom_id"];
    $session_id = $_POST["session_id"];

    if (!ctype_digit((string)$member_course_id) || !ctype_digit((string)$classroom_id) || !ctype_digit((string)$session_id)) {
        flash_redirect('edit_scheduling.php?id=' . $timetableId, 'خطأ: البيانات المدخلة غير صالحة.', 'error');
    } else {
        $member_course_id = (int)$member_course_id;
        $classroom_id = (int)$classroom_id;
        $session_id = (int)$session_id;

        $ownerCheckQuery = "SELECT 1 FROM member_courses WHERE member_course_id = ? AND member_id = ? LIMIT 1";
        $ownerCheckStmt = $conn->prepare($ownerCheckQuery);
        if (!$ownerCheckStmt) {
            error_log("Edit scheduling owner check prepare failed: " . $conn->error);
            flash_redirect('edit_scheduling.php?id=' . $timetableId, 'حدث خطأ أثناء التحقق من الصلاحيات.', 'error');
        }

        $ownerCheckStmt->bind_param("ii", $member_course_id, $userId);
        $ownerCheckStmt->execute();
        $ownerCheckResult = $ownerCheckStmt->get_result();
        $ownerValid = $ownerCheckResult && $ownerCheckResult->num_rows > 0;
        $ownerCheckStmt->close();

        if (!$ownerValid) {
            flash_redirect('edit_scheduling.php?id=' . $timetableId, 'غير مسموح باختيار مادة لا تخصك.', 'error');
        }

        $duplicateQuery = "SELECT *
                FROM timetable t
                INNER JOIN member_courses mc ON t.member_course_id = mc.member_course_id
                INNER JOIN subjects s ON mc.subject_id = s.subject_id
                INNER JOIN sections sec ON mc.section_id = sec.section_id
                WHERE (t.classroom_id = ? AND t.session_id = ? AND t.timetable_id != ?)
                OR (t.session_id = ? AND s.department_id = (SELECT subject.department_id FROM subjects subject WHERE subject.subject_id = mc.subject_id) AND s.level_id = (SELECT subject.level_id FROM subjects subject WHERE subject.subject_id = mc.subject_id) AND t.timetable_id != ?)";
        $duplicateStmt = $conn->prepare($duplicateQuery);

        if (!$duplicateStmt) {
            error_log("Edit scheduling duplicate prepare failed: " . $conn->error);
            flash_redirect('edit_scheduling.php?id=' . $timetableId, 'حدث خطأ أثناء التحقق من التعارض.', 'error');
        } else {
            $duplicateStmt->bind_param("iiiii", $classroom_id, $session_id, $timetableId, $session_id, $timetableId);
            $duplicateStmt->execute();
            $duplicateResult = $duplicateStmt->get_result();

            if ($duplicateResult && $duplicateResult->num_rows > 0) {
                flash_redirect('edit_scheduling.php?id=' . $timetableId, 'خطأ: تم اختيار الفترة والفرقة أو الفترة والقسم والمستوى مسبقًا.', 'error');
            } else {
                $updateQuery = "UPDATE timetable SET member_course_id = ?, classroom_id = ?, session_id = ? WHERE timetable_id = ?";
                $updateStmt = $conn->prepare($updateQuery);

                if ($updateStmt) {
                    $updateStmt->bind_param("iiii", $member_course_id, $classroom_id, $session_id, $timetableId);
                    if ($updateStmt->execute()) {
                        flash_redirect('scheduling.php', 'تم تحديث السجل بنجاح.', 'success');
                    }

                    error_log("Edit scheduling update failed: " . $updateStmt->error);
                    flash_redirect('edit_scheduling.php?id=' . $timetableId, 'حدث خطأ أثناء تحديث البيانات.', 'error');
                    $updateStmt->close();
                } else {
                    error_log("Edit scheduling update prepare failed: " . $conn->error);
                    flash_redirect('edit_scheduling.php?id=' . $timetableId, 'حدث خطأ أثناء تحديث البيانات.', 'error');
                }
            }

            $duplicateStmt->close();
        }
    }
}

$userId = (int)$userId;
$memberCoursesQuery = "SELECT mc.member_course_id, s.subject_name 
                       FROM member_courses mc
                       INNER JOIN subjects s ON mc.subject_id = s.subject_id
                       WHERE mc.member_id = ?";
$memberCoursesStmt = $conn->prepare($memberCoursesQuery);

if ($memberCoursesStmt) {
    $memberCoursesStmt->bind_param("i", $userId);
    $memberCoursesStmt->execute();
    $memberCoursesResult = $memberCoursesStmt->get_result();
} else {
    $memberCoursesResult = false;
    error_log("Edit scheduling member courses prepare failed: " . $conn->error);
}

$classroomsQuery = "SELECT * FROM classrooms";
$classroomsResult = $conn->query($classroomsQuery);

$sessionsQuery = "SELECT * FROM sessions";
$sessionsResult = $conn->query($sessionsQuery);
?>

<!DOCTYPE html>
<html>
<head>
    <title>تعديل بيانات الجدول الزمني</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <h2>تعديل بيانات الجدول الزمني</h2>
    <?php if ($message): ?>
        <p style="color: <?php echo $message_type === 'error' ? 'red' : 'green'; ?>;"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
    <form method="post" action="">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
        <label for="member_course_id">المادة:</label>
        <select name="member_course_id" id="member_course_id">
            <?php
            if ($memberCoursesResult->num_rows > 0) {
                while ($courseRow = $memberCoursesResult->fetch_assoc()) {
                    $selected = ($courseRow["member_course_id"] == $row["member_course_id"]) ? "selected" : "";
                    echo "<option value='" . $courseRow["member_course_id"] . "' $selected>" . $courseRow["subject_name"] . "</option>";
                }
            }
            ?>
        </select><br><br>

        <label for="classroom_id">القاعة:</label>
        <select name="classroom_id" id="classroom_id">
            <?php
            if ($classroomsResult->num_rows > 0) {
                while ($classroomRow = $classroomsResult->fetch_assoc()) {
                    $selected = ($classroomRow["classroom_id"] == $row["classroom_id"]) ? "selected" : "";
                    echo "<option value='" . $classroomRow["classroom_id"] . "' $selected>" . $classroomRow["classroom_name"] . "</option>";
                }
            }
            ?>
        </select><br><br>

        <label for="session_id">الفترة:</label>
        <select name="session_id" id="session_id">
            <?php
            if ($sessionsResult->num_rows > 0) {
                while ($sessionRow = $sessionsResult->fetch_assoc()) {
                    $selected = ($sessionRow["session_id"] == $row["session_id"]) ? "selected" : "";
                    echo "<option value='" . $sessionRow["session_id"] . "' $selected>" . $sessionRow["session_name"] . "</option>";
                }
            }
            ?>
        </select><br><br>

        <input type="submit" value="تعديل" name="submit">
    </form>

    <a style="background-color: red; color:white; font-size:20px" href="scheduling.php">العودة إلى الجدول الزمني</a>

</body>
</html>

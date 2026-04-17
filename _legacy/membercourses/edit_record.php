<?php
require_once("../db_config.php");
session_start();

// التحقق من وجود معرف الجلسة للمستخدم المسجل
if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

// التحقق من وجود معرف السجل في العنوان
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    $_SESSION['message'] = "تعذر فتح صفحة التعديل: معرف غير صالح.";
    $_SESSION['message_type'] = "error";
    header("Location: membercourses.php");
    exit();
}

$member_course_id = (int)$_GET['id'];

// جلب بيانات السجل من قاعدة البيانات
$query = "SELECT member_id, subject_id, section_id FROM member_courses WHERE member_course_id = ?";
$stmt = mysqli_prepare($conn, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $member_course_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = false;
}

if ($result && mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $member_id = $row['member_id'];
    $subject_id = $row['subject_id'];
    $section_id = $row['section_id'];
} else {
    if ($stmt) {
        error_log("Edit member course load failed: " . mysqli_stmt_error($stmt));
    } else {
        error_log("Edit member course prepare failed: " . mysqli_error($conn));
    }
    $_SESSION['message'] = "تعذر تحميل بيانات السجل.";
    $_SESSION['message_type'] = "error";
    header("Location: membercourses.php");
    exit();
}

if ($stmt) {
    mysqli_stmt_close($stmt);
}

// التحقق من إرسال النموذج وتحديث البيانات في قاعدة البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id = $_POST['member_id'];
    $subject_id = $_POST['subject_id'];
    $section_id = $_POST['section_id'];

    if (!ctype_digit((string)$member_id) || !ctype_digit((string)$subject_id) || !ctype_digit((string)$section_id)) {
        $_SESSION['message'] = "تعذر تحديث السجل: بيانات غير صالحة.";
        $_SESSION['message_type'] = "error";
        header("Location: membercourses.php");
        exit();
    }

    $member_id = (int)$member_id;
    $subject_id = (int)$subject_id;
    $section_id = (int)$section_id;

    $updateQuery = "UPDATE member_courses SET member_id = ?, subject_id = ?, section_id = ? WHERE member_course_id = ?";
    $updateStmt = mysqli_prepare($conn, $updateQuery);

    if ($updateStmt) {
        mysqli_stmt_bind_param($updateStmt, "iiii", $member_id, $subject_id, $section_id, $member_course_id);
    }

    if ($updateStmt && mysqli_stmt_execute($updateStmt)) {
        $_SESSION['message'] = "تم تحديث السجل بنجاح!";
        $_SESSION['message_type'] = "success";
    } else {
        if ($updateStmt) {
            error_log("Edit member course update failed: " . mysqli_stmt_error($updateStmt));
        } else {
            error_log("Edit member course update prepare failed: " . mysqli_error($conn));
        }
        $_SESSION['message'] = "حدث خطأ أثناء تحديث السجل.";
        $_SESSION['message_type'] = "error";
    }

    if ($updateStmt) {
        mysqli_stmt_close($updateStmt);
    }

    header("Location: membercourses.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>تعديل السجل</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <h1>تعديل السجل</h1>
    <form method="POST" action="">
        <div class="form-group">
            <label for="member_id">اختر عضو هيئة التدريس:</label>
            <select name="member_id" id="member_id" class="form-control" required>
                <?php
                $membersQuery = "SELECT * FROM faculty_members";
                $membersResult = mysqli_query($conn, $membersQuery);

                if (mysqli_num_rows($membersResult) > 0) {
                    while ($row = mysqli_fetch_assoc($membersResult)) {
                        $selected = ($row['member_id'] == $member_id) ? 'selected' : '';
                        echo "<option value='{$row['member_id']}' $selected>{$row['member_name']}</option>";
                    }
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="subject_id">اختر المادة:</label>
            <select name="subject_id" id="subject_id" class="form-control" required>
                <?php
                $subjectsQuery = "SELECT * FROM subjects";
                $subjectsResult = mysqli_query($conn, $subjectsQuery);

                if (mysqli_num_rows($subjectsResult) > 0) {
                    while ($row = mysqli_fetch_assoc($subjectsResult)) {
                        $selected = ($row['subject_id'] == $subject_id) ? 'selected' : '';
                        echo "<option value='{$row['subject_id']}' $selected>{$row['subject_name']}</option>";
                    }
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="section_id">اختر السكاشن:</label>
            <select name="section_id" id="section_id" class="form-control" required>
                <?php
                $sectionsQuery = "SELECT * FROM sections";
                $sectionsResult = mysqli_query($conn, $sectionsQuery);

                if (mysqli_num_rows($sectionsResult) > 0) {
                    while ($row = mysqli_fetch_assoc($sectionsResult)) {
                        $selected = ($row['section_id'] == $section_id) ? 'selected' : '';
                        echo "<option value='{$row['section_id']}' $selected>{$row['section_name']}</option>";
                    }
                }
                ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">تحديث السجل</button>
    </form>

</body>
</html>

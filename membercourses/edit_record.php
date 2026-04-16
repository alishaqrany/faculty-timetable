<?php
require_once("../db_config.php");
session_start();

// التحقق من وجود معرف الجلسة للمستخدم المسجل
if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

// التحقق من وجود معرف السجل في العنوان
if (!isset($_GET['id'])) {
    header("Location: membercourses.php");
    exit();
}

$member_course_id = $_GET['id'];

// جلب بيانات السجل من قاعدة البيانات
$query = "SELECT * FROM member_courses WHERE member_course_id = '$member_course_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $member_id = $row['member_id'];
    $subject_id = $row['subject_id'];
    $section_id = $row['section_id'];
} else {
    header("Location: membercourses.php");
    exit();
}

// التحقق من إرسال النموذج وتحديث البيانات في قاعدة البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id = mysqli_real_escape_string($conn, $_POST['member_id']);
    $subject_id = mysqli_real_escape_string($conn, $_POST['subject_id']);
    $section_id = mysqli_real_escape_string($conn, $_POST['section_id']);

    // تحديث بيانات السجل
    $updateQuery = "
    UPDATE member_courses SET
    member_id = '$member_id',
    subject_id = '$subject_id',
    section_id = '$section_id'
    WHERE member_course_id = '$member_course_id'
    ";

    if (mysqli_query($conn, $updateQuery)) {
        $_SESSION['message'] = "تم تحديث السجل بنجاح!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "حدث خطأ أثناء تحديث السجل: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
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

<?php
require_once("../db_config.php");
session_start();

// التحقق من وجود معرف الجلسة للمستخدم المسجل
if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

// التحقق من وجود معرف السكشن في العنوان
if (!isset($_GET['id'])) {
    header("Location: sections.php");
    exit();
}

$section_id = $_GET['id'];

// جلب بيانات السكشن من قاعدة البيانات
$query = "SELECT * FROM sections WHERE section_id = '$section_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $section_name = $row['section_name'];
    $department_id = $row['department_id'];
    $level_id = $row['level_id'];
} else {
    header("Location: sections.php");
    exit();
}

// التحقق من إرسال النموذج وتحديث البيانات في قاعدة البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section_name = mysqli_real_escape_string($conn, $_POST['section_name']);
    $department_id = mysqli_real_escape_string($conn, $_POST['department_id']);
    $level_id = mysqli_real_escape_string($conn, $_POST['level_id']);

    // تحديث بيانات السكشن
    $updateQuery = "
    UPDATE sections SET
    section_name = '$section_name',
    department_id = '$department_id',
    level_id = '$level_id'
    WHERE section_id = '$section_id'
    ";

    $updateResult = mysqli_query($conn, $updateQuery);

    if ($updateResult) {
        $_SESSION['message'] = "تم تحديث بيانات السكشن بنجاح!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "حدث خطأ أثناء تحديث بيانات السكشن: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }

    header("Location: sections.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>تعديل بيانات السكشن</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <h1>تعديل بيانات السكشن</h1>
    <form method="POST" action="">
        <div class="form-group">
            <label for="section_name">اسم السكشن:</label>
            <input type="text" name="section_name" id="section_name" class="form-control" value="<?php echo htmlspecialchars($section_name); ?>" required>
        </div>

        <div class="form-group">
            <label for="department_id">القسم:</label>
            <select name="department_id" id="department_id" class="form-control" required>
                <?php
                $departmentQuery = "SELECT department_id, department_name FROM departments";
                $departmentResult = mysqli_query($conn, $departmentQuery);
                if (mysqli_num_rows($departmentResult) > 0) {
                    while ($departmentRow = mysqli_fetch_assoc($departmentResult)) {
                        $selected = ($departmentRow['department_id'] == $department_id) ? 'selected' : '';
                        echo "<option value='{$departmentRow['department_id']}' $selected>{$departmentRow['department_name']}</option>";
                    }
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="level_id">المستوى:</label>
            <select name="level_id" id="level_id" class="form-control" required>
                <?php
                $levelQuery = "SELECT level_id, level_name FROM levels";
                $levelResult = mysqli_query($conn, $levelQuery);
                if (mysqli_num_rows($levelResult) > 0) {
                    while ($levelRow = mysqli_fetch_assoc($levelResult)) {
                        $selected = ($levelRow['level_id'] == $level_id) ? 'selected' : '';
                        echo "<option value='{$levelRow['level_id']}' $selected>{$levelRow['level_name']}</option>";
                    }
                }
                ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">تحديث بيانات السكشن</button>
    </form>

</body>
</html>

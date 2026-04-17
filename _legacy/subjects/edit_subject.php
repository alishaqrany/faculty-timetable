<?php
require_once("../db_config.php");
session_start();

if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['subject_id']) || !ctype_digit($_GET['subject_id'])) {
    $_SESSION['message'] = "تعذر فتح صفحة التعديل: معرف غير صالح.";
    $_SESSION['message_type'] = "error";
    header("Location: subjects.php");
    exit();
}

$subject_id = (int)$_GET['subject_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $posted_subject_id = $_POST['subject_id'];
    $subject_name = trim($_POST['subject_name']);
    $department_id = $_POST['department_id'];
    $level_id = $_POST['level_id'];
    $hours = $_POST['hours'];

    if (!ctype_digit((string)$posted_subject_id) || (int)$posted_subject_id !== $subject_id || $subject_name === '' || !ctype_digit((string)$department_id) || !ctype_digit((string)$level_id) || !ctype_digit((string)$hours)) {
        $_SESSION['message'] = "تعذر تحديث بيانات المادة: بيانات غير صالحة.";
        $_SESSION['message_type'] = "error";
        header("Location: subjects.php");
        exit();
    }

    $department_id = (int)$department_id;
    $level_id = (int)$level_id;
    $hours = (int)$hours;

    $updateQuery = "UPDATE subjects SET subject_name = ?, department_id = ?, level_id = ?, hours = ? WHERE subject_id = ?";
    $updateStmt = mysqli_prepare($conn, $updateQuery);

    if ($updateStmt) {
        mysqli_stmt_bind_param($updateStmt, "siiii", $subject_name, $department_id, $level_id, $hours, $subject_id);
    }

    if ($updateStmt && mysqli_stmt_execute($updateStmt)) {
        $_SESSION['message'] = "تم تحديث بيانات المادة بنجاح!";
        $_SESSION['message_type'] = "success";
    } else {
        if ($updateStmt) {
            error_log("Edit subject update failed: " . mysqli_stmt_error($updateStmt));
        } else {
            error_log("Edit subject update prepare failed: " . mysqli_error($conn));
        }
        $_SESSION['message'] = "حدث خطأ أثناء تحديث بيانات المادة.";
        $_SESSION['message_type'] = "error";
    }

    if ($updateStmt) {
        mysqli_stmt_close($updateStmt);
    }

    header("Location: subjects.php");
    exit();
}

$selectQuery = "SELECT subject_name, department_id, level_id, hours FROM subjects WHERE subject_id = ?";
$selectStmt = mysqli_prepare($conn, $selectQuery);

if ($selectStmt) {
    mysqli_stmt_bind_param($selectStmt, "i", $subject_id);
    mysqli_stmt_execute($selectStmt);
    $selectResult = mysqli_stmt_get_result($selectStmt);
} else {
    $selectResult = false;
}

if ($selectResult && mysqli_num_rows($selectResult) === 1) {
    $row = mysqli_fetch_assoc($selectResult);
    $subject_name = $row['subject_name'];
    $department_id = $row['department_id'];
    $level_id = $row['level_id'];
    $hours = $row['hours'];
} else {
    if ($selectStmt) {
        error_log("Edit subject load failed: " . mysqli_stmt_error($selectStmt));
    } else {
        error_log("Edit subject prepare failed: " . mysqli_error($conn));
    }
    $_SESSION['message'] = "تعذر تحميل بيانات المادة.";
    $_SESSION['message_type'] = "error";
    header("Location: subjects.php");
    exit();
}

if ($selectStmt) {
    mysqli_stmt_close($selectStmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>تعديل المادة</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 20px;
        }

        label {
            display: inline-block;
            width: 120px;
            text-align: left;
        }

        input[type="text"], select {
            width: 200px;
            padding: 5px;
        }

        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>تعديل المادة</h1>
    <form method="POST" action="">
        <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
        <div>
            <label>اسم المادة:</label>
            <input type="text" name="subject_name" value="<?php echo $subject_name; ?>">
        </div>
        <div>
            <label>القسم:</label>
            <select name="department_id">
                <?php
                // استعلام لاسترداد الأقسام
                $departmentQuery = "SELECT * FROM departments";
                $departmentResult = mysqli_query($conn, $departmentQuery);

                if ($departmentResult) {
                    while ($department = mysqli_fetch_assoc($departmentResult)) {
                        $selected = ($department['department_id'] == $department_id) ? 'selected' : '';
                        echo "<option value='{$department['department_id']}' $selected>{$department['department_name']}</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div>
            <label>الفرقة (المستوى):</label>
            <select name="level_id">
                <?php
                // استعلام لاسترداد الفرق
                $levelQuery = "SELECT * FROM levels";
                $levelResult = mysqli_query($conn, $levelQuery);

                if ($levelResult) {
                    while ($level = mysqli_fetch_assoc($levelResult)) {
                        $selected = ($level['level_id'] == $level_id) ? 'selected' : '';
                        echo "<option value='{$level['level_id']}' $selected>{$level['level_name']}</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div>
            <label>عدد الساعات:</label>
            <input type="text" name="hours" value="<?php echo $hours; ?>">
        </div>
        <div>
            <button type="submit" name="submit">حفظ التغييرات</button>
        </div>
    </form>
</body>
</html>

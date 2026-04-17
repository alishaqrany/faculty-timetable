<?php
require_once("../db_config.php");
session_start();

// التحقق من وجود معرف الجلسة للمستخدم المسجل
if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

// التحقق من وجود معرف الفرقة الدراسية في العنوان
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    $_SESSION['message'] = "تعذر فتح صفحة التعديل: معرف غير صالح.";
    $_SESSION['message_type'] = "error";
    header("Location: levels.php");
    exit();
}

$level_id = (int)$_GET['id'];

// جلب بيانات الفرقة الدراسية من قاعدة البيانات
$query = "SELECT level_name FROM levels WHERE level_id = ?";
$stmt = mysqli_prepare($conn, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $level_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = false;
}

if ($result && mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $level_name = $row['level_name'];
} else {
    if ($stmt) {
        error_log("Edit level load failed: " . mysqli_stmt_error($stmt));
    } else {
        error_log("Edit level prepare failed: " . mysqli_error($conn));
    }
    $_SESSION['message'] = "تعذر تحميل بيانات الفرقة الدراسية.";
    $_SESSION['message_type'] = "error";
    header("Location: levels.php");
    exit();
}

if ($stmt) {
    mysqli_stmt_close($stmt);
}

// التحقق من إرسال النموذج وتحديث البيانات في قاعدة البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $level_name = trim($_POST['level_name']);

    if ($level_name === '') {
        $_SESSION['message'] = "اسم الفرقة الدراسية مطلوب.";
        $_SESSION['message_type'] = "error";
        header("Location: levels.php");
        exit();
    }

    $updateQuery = "UPDATE levels SET level_name = ? WHERE level_id = ?";
    $updateStmt = mysqli_prepare($conn, $updateQuery);

    if ($updateStmt) {
        mysqli_stmt_bind_param($updateStmt, "si", $level_name, $level_id);
    }

    if ($updateStmt && mysqli_stmt_execute($updateStmt)) {
        $_SESSION['message'] = "تم تحديث بيانات الفرقة الدراسية بنجاح!";
        $_SESSION['message_type'] = "success";
    } else {
        if ($updateStmt) {
            error_log("Edit level update failed: " . mysqli_stmt_error($updateStmt));
        } else {
            error_log("Edit level update prepare failed: " . mysqli_error($conn));
        }
        $_SESSION['message'] = "حدث خطأ أثناء تحديث بيانات الفرقة الدراسية.";
        $_SESSION['message_type'] = "error";
    }

    if ($updateStmt) {
        mysqli_stmt_close($updateStmt);
    }

    header("Location: levels.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>تعديل بيانات الفرقة الدراسية</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <h1>تعديل بيانات الفرقة الدراسية</h1>
    <form method="POST" action="">
        <div class="form-group">
            <label for="level_name">اسم الفرقة الدراسية:</label>
            <input type="text" name="level_name" id="level_name" class="form-control" value="<?php echo $level_name; ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">تحديث</button>
    </form>

</body>
</html>

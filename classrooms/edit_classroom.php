<?php
require_once("../db_config.php");
session_start();

// التحقق من وجود معرف الجلسة للمستخدم المسجل
if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

// التحقق من وجود معرف القاعة في العنوان
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    $_SESSION['message'] = "تعذر فتح صفحة التعديل: معرف غير صالح.";
    $_SESSION['message_type'] = "error";
    header("Location: classrooms.php");
    exit();
}

$classroom_id = (int)$_GET['id'];

// جلب بيانات القاعة من قاعدة البيانات
$query = "SELECT classroom_name FROM classrooms WHERE classroom_id = ?";
$stmt = mysqli_prepare($conn, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $classroom_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = false;
}

if ($result && mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $classroom_name = $row['classroom_name'];
} else {
    if ($stmt) {
        error_log("Edit classroom load failed: " . mysqli_stmt_error($stmt));
    } else {
        error_log("Edit classroom prepare failed: " . mysqli_error($conn));
    }
    $_SESSION['message'] = "تعذر تحميل بيانات القاعة الدراسية.";
    $_SESSION['message_type'] = "error";
    header("Location: classrooms.php");
    exit();
}

if ($stmt) {
    mysqli_stmt_close($stmt);
}

// التحقق من إرسال النموذج وتحديث البيانات في قاعدة البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classroom_name = trim($_POST['classroom_name']);

    if ($classroom_name === '') {
        $_SESSION['message'] = "اسم القاعة الدراسية مطلوب.";
        $_SESSION['message_type'] = "error";
        header("Location: classrooms.php");
        exit();
    }

    $updateQuery = "UPDATE classrooms SET classroom_name = ? WHERE classroom_id = ?";
    $updateStmt = mysqli_prepare($conn, $updateQuery);

    if ($updateStmt) {
        mysqli_stmt_bind_param($updateStmt, "si", $classroom_name, $classroom_id);
    }

    if ($updateStmt && mysqli_stmt_execute($updateStmt)) {
        $_SESSION['message'] = "تم تحديث بيانات القاعة الدراسية بنجاح!";
        $_SESSION['message_type'] = "success";
    } else {
        if ($updateStmt) {
            error_log("Edit classroom update failed: " . mysqli_stmt_error($updateStmt));
        } else {
            error_log("Edit classroom update prepare failed: " . mysqli_error($conn));
        }
        $_SESSION['message'] = "حدث خطأ أثناء تحديث بيانات القاعة الدراسية.";
        $_SESSION['message_type'] = "error";
    }

    if ($updateStmt) {
        mysqli_stmt_close($updateStmt);
    }

    header("Location: classrooms.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>تعديل بيانات القاعة الدراسية</title>
    <link rel="stylesheet" type="text/css" href="../style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <h1>تعديل بيانات القاعة الدراسية</h1>
    <form method="POST" action="">
        <div>
            <label for="classroom_name">اسم القاعة الدراسية:</label>
            <input type="text" name="classroom_name" id="classroom_name" value="<?php echo $classroom_name; ?>" required>
        </div>

        <button type="submit">تحديث</button>
    </form>

</body>
</html>

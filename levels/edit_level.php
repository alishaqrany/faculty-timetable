<?php
require_once("../db_config.php");
session_start();

// التحقق من وجود معرف الجلسة للمستخدم المسجل
if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

// التحقق من وجود معرف الفرقة الدراسية في العنوان
if (!isset($_GET['id'])) {
    header("Location: levels.php");
    exit();
}

$level_id = $_GET['id'];

// جلب بيانات الفرقة الدراسية من قاعدة البيانات
$query = "SELECT * FROM levels WHERE level_id = '$level_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $level_name = $row['level_name'];
} else {
    header("Location: levels.php");
    exit();
}

// التحقق من إرسال النموذج وتحديث البيانات في قاعدة البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $level_name = mysqli_real_escape_string($conn, $_POST['level_name']);

    // تحديث بيانات الفرقة الدراسية
    $updateQuery = "
    UPDATE levels SET
    level_name = '$level_name'
    WHERE level_id = '$level_id'
    ";

    $updateResult = mysqli_query($conn, $updateQuery);

    if ($updateResult) {
        $_SESSION['message'] = "تم تحديث بيانات الفرقة الدراسية بنجاح!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "حدث خطأ أثناء تحديث بيانات الفرقة الدراسية: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
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

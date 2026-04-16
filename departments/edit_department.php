<?php
require_once("../db_config.php");
session_start();

// التحقق من وجود معرف الجلسة للمستخدم المسجل
if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

// التحقق من وجود معرف القسم في العنوان
if (!isset($_GET['id'])) {
    header("Location: departments.php");
    exit();
}

$department_id = $_GET['id'];

// جلب بيانات القسم من قاعدة البيانات
$query = "SELECT * FROM departments WHERE department_id = '$department_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $department_name = $row['department_name'];
} else {
    header("Location: departments.php");
    exit();
}

// التحقق من إرسال النموذج وتحديث البيانات في قاعدة البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department_name = mysqli_real_escape_string($conn, $_POST['department_name']);

    // تحديث بيانات القسم
    $updateQuery = "
    UPDATE departments SET
    department_name = '$department_name'
    WHERE department_id = '$department_id'
    ";

    $updateResult = mysqli_query($conn, $updateQuery);

    if ($updateResult) {
        $_SESSION['message'] = "تم تحديث بيانات القسم بنجاح!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "حدث خطأ أثناء تحديث بيانات القسم: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }

    header("Location: departments.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>تعديل بيانات القسم</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <h1>تعديل بيانات القسم</h1>
    <form method="POST" action="">
        <div class="form-group">
            <label for="department_name">اسم القسم:</label>
            <input type="text" name="department_name" id="department_name" class="form-control" value="<?php echo $department_name; ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">تحديث</button>
    </form>

</body>
</html>

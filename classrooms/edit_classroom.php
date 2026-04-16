<?php
require_once("../db_config.php");
session_start();

// التحقق من وجود معرف الجلسة للمستخدم المسجل
if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

// التحقق من وجود معرف القاعة في العنوان
if (!isset($_GET['id'])) {
    header("Location: classrooms.php");
    exit();
}

$classroom_id = $_GET['id'];

// جلب بيانات القاعة من قاعدة البيانات
$query = "SELECT * FROM classrooms WHERE classroom_id = '$classroom_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $classroom_name = $row['classroom_name'];
} else {
    header("Location: classrooms.php");
    exit();
}

// التحقق من إرسال النموذج وتحديث البيانات في قاعدة البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classroom_name = mysqli_real_escape_string($conn, $_POST['classroom_name']);

    // تحديث بيانات القاعة الدراسية
    $updateQuery = "
    UPDATE classrooms SET
    classroom_name = '$classroom_name'
    WHERE classroom_id = '$classroom_id'
    ";

    $updateResult = mysqli_query($conn, $updateQuery);

    if ($updateResult) {
        $_SESSION['message'] = "تم تحديث بيانات القاعة الدراسية بنجاح!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "حدث خطأ أثناء تحديث بيانات القاعة الدراسية: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
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

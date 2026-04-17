<?php
require_once("../db_config.php");
session_start();

// التحقق من وجود معرف الجلسة للمستخدم المسجل
if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

$message = "";
$message_type = "";

// التحقق من وجود معرف العضو في العنوان
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$member_id = (int)$_GET['id'];

// جلب بيانات العضو من قاعدة البيانات
$stmt = mysqli_prepare($conn, "SELECT fm.*, u.username, u.password FROM faculty_members fm LEFT JOIN users u ON fm.member_id = u.member_id WHERE fm.member_id = ?");
$result = false;
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $member_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}

if ($result && mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $member_name = $row['member_name'];
    $academic_degree = $row['academic_degree'];
    $join_date = $row['join_date'];
    $ranking = $row['ranking'];
    $role = $row['role'];
    $username = $row['username'];
    $hashed_password = $row['password'];
} else {
    header("Location: index.php");
    exit();
}

if ($stmt) {
    mysqli_stmt_close($stmt);
}

// التحقق من إرسال النموذج وتحديث البيانات في قاعدة البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_name = trim($_POST['member_name']);
    $academic_degree = trim($_POST['academic_degree']);
    $join_date = trim($_POST['join_date']);
    $ranking_raw = trim((string)($_POST['ranking'] ?? ''));
    $role = trim($_POST['role']);
    $username = trim($_POST['username']);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $hashed_password;

    $allowed_degrees = ["استاذ", "استاذ مساعد", "مدرس", "معيد"];
    $allowed_roles = ["استاذ المادة", "معاون"];

    $valid_join_date = ($join_date === '') || (preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $join_date) && checkdate((int)substr($join_date, 5, 2), (int)substr($join_date, 8, 2), (int)substr($join_date, 0, 4)));
    $valid_ranking = ($ranking_raw === '') || ctype_digit($ranking_raw);

    if ($member_name === '' || $username === '' || !in_array($academic_degree, $allowed_degrees, true) || !in_array($role, $allowed_roles, true) || !$valid_join_date || !$valid_ranking) {
        $_SESSION['message'] = "حدث خطأ أثناء تحديث البيانات: بيانات غير صالحة.";
        $_SESSION['message_type'] = "error";
        header("Location: faculty_members.php");
        exit();
    }

    $ranking = ($ranking_raw === '') ? 0 : (int)$ranking_raw;

    // تحديث بيانات العضو في جدول faculty_members
    $updateStmt = mysqli_prepare($conn, "UPDATE faculty_members SET member_name = ?, academic_degree = ?, join_date = ?, ranking = ?, role = ? WHERE member_id = ?");
    $updateResult = false;
    if ($updateStmt) {
        mysqli_stmt_bind_param($updateStmt, "sssisi", $member_name, $academic_degree, $join_date, $ranking, $role, $member_id);
        $updateResult = mysqli_stmt_execute($updateStmt);
        mysqli_stmt_close($updateStmt);
    }

    // تحديث بيانات المستخدم في جدول users
    $updateUserStmt = mysqli_prepare($conn, "UPDATE users SET username = ?, password = ? WHERE member_id = ?");
    $updateUserResult = false;
    if ($updateUserStmt) {
        mysqli_stmt_bind_param($updateUserStmt, "ssi", $username, $password, $member_id);
        $updateUserResult = mysqli_stmt_execute($updateUserStmt);
        mysqli_stmt_close($updateUserStmt);
    }

    if ($updateResult && $updateUserResult) {
        $_SESSION['message'] = "تم تحديث بيانات العضو بنجاح!";
        $_SESSION['message_type'] = "success";
    } else {
        error_log("Edit member failed: " . mysqli_error($conn));
        $_SESSION['message'] = "حدث خطأ أثناء تحديث البيانات.";
        $_SESSION['message_type'] = "error";
    }

    header("Location: faculty_members.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>تعديل بيانات عضو هيئة التدريس</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <h1>تعديل بيانات عضو هيئة التدريس</h1>
    <form method="POST" action="" class="form-container">
        <div class="form-group">
            <label for="member_name">اسم العضو:</label>
            <input type="text" name="member_name" id="member_name" class="form-control" value="<?php echo $member_name; ?>" required>
        </div>
        <div class="form-group">
            <label for="academic_degree">الدرجة العلمية:</label>
            <select name="academic_degree" id="academic_degree" class="form-control" required>
                <option value="استاذ" <?php if ($academic_degree == "استاذ") echo "selected"; ?>>استاذ</option>
                <option value="استاذ مساعد" <?php if ($academic_degree == "استاذ مساعد") echo "selected"; ?>>استاذ مساعد</option>
                <option value="مدرس" <?php if ($academic_degree == "مدرس") echo "selected"; ?>>مدرس</option>
                <option value="معيد" <?php if ($academic_degree == "معيد") echo "selected"; ?>>معيد</option>
            </select>
        </div>
        <div class="form-group">
            <label for="join_date">تاريخ الانضمام:</label>
            <input type="date" name="join_date" id="join_date" class="form-control" value="<?php echo $join_date; ?>">
        </div>
        <div class="form-group">
            <label for="ranking">الترتيب حسب الأقدمية:</label>
            <input type="number" name="ranking" id="ranking" class="form-control" value="<?php echo $ranking; ?>">
        </div>
        <div class="form-group">
            <label for="role">الدور:</label>
            <select name="role" id="role" class="form-control">
                <option value="استاذ المادة" <?php if ($role == "استاذ المادة") echo "selected"; ?>>استاذ المادة</option>
                <option value="معاون" <?php if ($role == "معاون") echo "selected"; ?>>معاون</option>
            </select>
        </div>
        <div class="form-group">
            <label for="username">اسم المستخدم:</label>
            <input type="text" name="username" id="username" class="form-control" value="<?php echo $username; ?>" required>
        </div>
        <div class="form-group">
            <label for="password">كلمة المرور (اتركها فارغة إذا كنت لا تريد تغييرها):</label>
            <input type="password" name="password" id="password" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">تحديث</button>
    </form>

    <?php if ($message): ?>
    <div class="alert mt-3 <?php echo $message_type === 'error' ? 'alert-danger' : 'alert-success'; ?>">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

</body>
</html>

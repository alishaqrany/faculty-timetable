<?php
require_once __DIR__ . '/../../core/bootstrap.php';
require_auth('../../login.php');

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    flash_redirect('list.php', 'معرف القسم غير صالح.', 'error');
}

$department_id = (int)$_GET['id'];
$query = "SELECT department_name FROM departments WHERE department_id = ?";
$stmt = mysqli_prepare($conn, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $department_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = false;
}

if ($result && mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $department_name = $row['department_name'];
} else {
    flash_redirect('list.php', 'القسم غير موجود أو لا يمكن الوصول إليه.', 'error');
}

if ($stmt) {
    mysqli_stmt_close($stmt);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department_name = trim($_POST['department_name']);

    $updateQuery = "UPDATE departments SET department_name = ? WHERE department_id = ?";
    $updateStmt = mysqli_prepare($conn, $updateQuery);

    if ($updateStmt) {
        mysqli_stmt_bind_param($updateStmt, 'si', $department_name, $department_id);
    }

    if ($updateStmt && mysqli_stmt_execute($updateStmt)) {
        $redirectMessage = 'تم تحديث بيانات القسم بنجاح!';
        $redirectType = 'success';
    } else {
        if ($updateStmt) {
            error_log('Update department failed: ' . mysqli_stmt_error($updateStmt));
        } else {
            error_log('Update department prepare failed: ' . mysqli_error($conn));
        }
        $redirectMessage = 'حدث خطأ أثناء تحديث بيانات القسم.';
        $redirectType = 'error';
    }

    if ($updateStmt) {
        mysqli_stmt_close($updateStmt);
    }

    flash_redirect('list.php', $redirectMessage, $redirectType);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>تعديل بيانات القسم</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../../style.css">
</head>
<body>

    <?php $navPrefix = '../../'; include APP_ROOT . '/views/shared/navbar.php'; ?>

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

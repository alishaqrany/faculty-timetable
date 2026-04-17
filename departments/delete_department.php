<?php
require_once("../core/bootstrap.php");
require_auth('../login.php');

$target = "../modules/departments/delete.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !csrf_validate($_POST['csrf_token'])) {
        flash_redirect($target, 'تعذر حذف القسم: طلب غير صالح.', 'error');
    }

    if (!isset($_POST['id']) || !ctype_digit((string)$_POST['id'])) {
        flash_redirect($target, 'حدث خطأ أثناء حذف القسم: معرف غير صالح.', 'error');
    }

    $_POST['id'] = (int)$_POST['id'];
    require __DIR__ . '/../modules/departments/delete.php';
    exit();
}

if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
    flash_redirect('../modules/departments/list.php', 'حدث خطأ أثناء حذف القسم: معرف غير صالح.', 'error');
}

$legacyId = (int)$_GET['id'];
$token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>جاري تحويل الطلب</title>
</head>
<body>
    <form id="legacy-delete-form" method="POST" action="<?php echo htmlspecialchars($target, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
        <input type="hidden" name="id" value="<?php echo $legacyId; ?>">
        <noscript>
            <button type="submit">متابعة الحذف</button>
        </noscript>
    </form>
    <script>
        document.getElementById('legacy-delete-form').submit();
    </script>
</body>
</html>

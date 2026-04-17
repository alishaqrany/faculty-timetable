<?php
header("Location: sections.php");
exit();

// ملف توافق لمسار قديم؛ يبقى المحتوى التالي غير مستخدم.
require_once("db_config.php");
session_start();

// التحقق من وجود معرف الجلسة للمستخدم المسجل
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit();
}

// التحقق من وجود معرف السكشن في العنوان
if (!isset($_GET['id'])) {
    header("Location: view_sections.php");
    exit();
}

$section_id = $_GET['id'];

$message = "";
$message_type = "";

// التحقق من إرسال النموذج وتحديث البيانات إذا كان ذلك مطلوبًا
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section_name = mysqli_real_escape_string($conn, $_POST['section_name']);
    $department_id = mysqli_real_escape_string($conn, $_POST['department_id']);
    $level_id = mysqli_real_escape_string($conn, $_POST['level_id']);

    // إجراء استعلام UPDATE لتحديث بيانات السكشن في قاعدة البيانات
    $updateQuery = "
    UPDATE sections
    SET section_name = '$section_name', department_id = '$department_id', level_id = '$level_id'
    WHERE section_id = '$section_id'
    ";

    $updateResult = mysqli_query($conn, $updateQuery);

    if ($updateResult) {
        $message = "تم تحديث بيانات السكشن بنجاح!";
        $message_type = "success";
    } else {
        $message = "حدث خطأ أثناء تحديث بيانات السكشن: " . mysqli_error($conn);
        $message_type = "error";
    }
}

// استعلام SELECT لاسترداد بيانات السكشن الحالية
$selectQuery = "
SELECT section_name, department_id, level_id
FROM sections
WHERE section_id = '$section_id'
";
$selectResult = mysqli_query($conn, $selectQuery);
$sectionData = mysqli_fetch_assoc($selectResult);

if (!$sectionData) {
    header("Location: view_sections.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>تعديل بيانات السكشن</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .alert {
            position: fixed;
            top: 80px;
            right: 20px;
            padding: 20px;
            background-color: #4CAF50;
            color: white;
            opacity: 1;
            transition: opacity 0.6s;
            border-radius: 5px;
            z-index: 1000;
        }

        .alert.error {
            background-color: #f44336;
        }
    </style>
    <script>
        function showAlert(message, type) {
            const alertBox = document.createElement('div');
            alertBox.className = 'alert';
            if (type === 'error') {
                alertBox.classList.add('error');
            }
            alertBox.textContent = message;

            document.body.appendChild(alertBox);

            setTimeout(() => {
                alertBox.style.opacity = '0';
                setTimeout(() => {
                    alertBox.remove();
                }, 600);
            }, 3000);
        }

        <?php if ($message): ?>
        window.onload = function() {
            showAlert("<?php echo $message; ?>", "<?php echo $message_type; ?>");
        }
        <?php endif; ?>
    </script>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <h1>تعديل بيانات السكشن</h1>
    <form method="POST" action="" class="form-container">
        <div class="form-group">
            <label for="section_name">اسم السكشن:</label>
            <input type="text" name="section_name" id="section_name" class="form-control" value="<?php echo htmlspecialchars($sectionData['section_name']); ?>" required>
        </div>

        <div class="form-group">
            <label for="department_id">القسم:</label>
            <select name="department_id" id="department_id" class="form-control" required>
                <?php
                $departmentQuery = "SELECT department_id, department_name FROM departments";
                $departmentResult = mysqli_query($conn, $departmentQuery);
                if (mysqli_num_rows($departmentResult) > 0) {
                    while ($departmentRow = mysqli_fetch_assoc($departmentResult)) {
                        $selected = ($departmentRow['department_id'] == $sectionData['department_id']) ? 'selected' : '';
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
                        $selected = ($levelRow['level_id'] == $sectionData['level_id']) ? 'selected' : '';
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

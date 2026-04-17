<?php
require_once("../db_config.php");
require_once("../core/csrf.php");
require_once("../core/flash.php");

// التحقق من تسجيل الدخول
session_start();

// التحقق من وجود معرف الجلسة للمستخدم المسجل
if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php"); // إعادة توجيه المستخدم إلى صفحة تسجيل الدخول إذا لم يكن مسجل الدخول
    exit();
}

list($message, $message_type) = flash_consume();

$tableName = "subjects";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        // إضافة المادة
        $subject_name = trim($_POST['subject_name']);
        $department_id = $_POST['department_id'];
        $level_id = $_POST['level_id'];
        $hours = $_POST['hours'];

        if ($subject_name === '' || !ctype_digit((string)$department_id) || !ctype_digit((string)$level_id) || !ctype_digit((string)$hours)) {
            $insertResult = false;
            $insertStmt = null;
        } else {
            $department_id = (int)$department_id;
            $level_id = (int)$level_id;
            $hours = (int)$hours;
            $insertQuery = "INSERT INTO $tableName (subject_name, department_id, level_id, hours) VALUES (?, ?, ?, ?)";
            $insertStmt = mysqli_prepare($conn, $insertQuery);
            if ($insertStmt) {
                mysqli_stmt_bind_param($insertStmt, "siii", $subject_name, $department_id, $level_id, $hours);
            }
            $insertResult = $insertStmt && mysqli_stmt_execute($insertStmt);
        }

        if ($insertResult) {
            flash_redirect('subjects.php', 'تمت إضافة المادة بنجاح!', 'success');
        } else {
            if (isset($insertStmt) && $insertStmt) {
                error_log("Insert subject failed: " . mysqli_stmt_error($insertStmt));
            } else {
                error_log("Insert subject prepare failed: " . mysqli_error($conn));
            }
            flash_redirect('subjects.php', 'حدث خطأ أثناء إضافة المادة.', 'error');
        }

        if (isset($insertStmt) && $insertStmt) {
            mysqli_stmt_close($insertStmt);
        }
    } elseif (isset($_POST['delete'])) {
        // حذف الجدول بالكامل
        $deleteQuery = "DROP TABLE $tableName";
        $deleteResult = mysqli_query($conn, $deleteQuery);
        if ($deleteResult) {
            flash_redirect('subjects.php', 'تم حذف الجدول بنجاح!', 'success');
        } else {
            error_log("Drop subjects table failed: " . mysqli_error($conn));
            flash_redirect('subjects.php', 'حدث خطأ أثناء حذف الجدول.', 'error');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>نموذج البيانات</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <link rel="stylesheet" type="text/css" href="../style.css">
    <style>
        .alert {
            position: fixed;
            top: 80px; /* Adjusted to be just below the navbar */
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

<?php
include dirname(__FILE__) . '/navbar.php';
?>

    <h1>إدارة المواد</h1>

    <form method="post" class="mb-3">
        <div class="form-group">
            <label for="subject_name">اسم المادة:</label>
            <input type="text" name="subject_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="department_id">القسم:</label>
            <select name="department_id" class="form-control">
                <?php
                $departmentQuery = "SELECT * FROM departments";
                $departmentResult = mysqli_query($conn, $departmentQuery);
                if ($departmentResult && mysqli_num_rows($departmentResult) > 0) {
                    while ($row = mysqli_fetch_assoc($departmentResult)) {
                        $department_id = $row['department_id'];
                        $department_name = $row['department_name'];
                        echo "<option value='$department_id'>$department_name</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="level_id">المستوى:</label>
            <select name="level_id" class="form-control">
                <?php
                $levelQuery = "SELECT * FROM levels";
                $levelResult = mysqli_query($conn, $levelQuery);
                if ($levelResult && mysqli_num_rows($levelResult) > 0) {
                    while ($row = mysqli_fetch_assoc($levelResult)) {
                        $level_id = $row['level_id'];
                        $level_name = $row['level_name'];
                        echo "<option value='$level_id'>$level_name</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="hours">عدد الساعات:</label>
            <input type="number" name="hours" class="form-control" required>
        </div>
        <button type="submit" name="add" class="btn btn-primary">إضافة المادة</button>
    </form>

    <h2 class="mt-5">عرض المواد</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>رقم المادة</th>
                <th>اسم المادة</th>
                <th>رقم القسم</th>
                <th>القسم</th>
                <th>رقم الفرقة</th>
                <th>الفرقة</th>
                <th>عدد الساعات</th>
                <th>تعديل</th>
                <th>حذف</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // اختر الجدول إذا كان موجودًا
            $selectTableQuery = "SHOW TABLES LIKE '$tableName'";
            $selectTableResult = mysqli_query($conn, $selectTableQuery);
            if (mysqli_num_rows($selectTableResult) > 0) {
                // الجدول موجود، قم بتنفيذ الاستعلام
                $selectQuery = "SELECT subjects.subject_id, subjects.subject_name, subjects.hours, departments.department_id, departments.department_name, levels.level_id, levels.level_name
                FROM $tableName
                JOIN departments ON subjects.department_id = departments.department_id
                JOIN levels ON subjects.level_id = levels.level_id";

                $selectResult = mysqli_query($conn, $selectQuery);
                if ($selectResult) {
                    $csrfToken = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
                    while ($row = mysqli_fetch_assoc($selectResult)) {
                        echo "<tr>";
                        echo "<td>{$row['subject_id']}</td>";
                        echo "<td>{$row['subject_name']}</td>";
                        echo "<td>{$row['department_id']}</td>";
                        echo "<td>{$row['department_name']}</td>";
                        echo "<td>{$row['level_id']}</td>";
                        echo "<td>{$row['level_name']}</td>";
                        echo "<td>{$row['hours']}</td>";
                        echo "<td><a href='edit_subject.php?subject_id={$row['subject_id']}'>تعديل</a></td>";
                        echo "<td><form method='POST' action='delete_subject.php' style='display:inline;' onsubmit='return confirm(\"هل أنت متأكد من حذف هذه المادة؟\")'><input type='hidden' name='csrf_token' value='{$csrfToken}'><input type='hidden' name='subject_id' value='{$row['subject_id']}'><button type='submit'>حذف</button></form></td>";
                        echo "</tr>";
                    }
                } else {
                    error_log("Select subjects table failed: " . mysqli_error($conn));
                    echo "<tr><td colspan='9'>حدث خطأ أثناء استعلام قاعدة البيانات.</td></tr>";
                }
            } else {
                // الجدول غير موجود
                echo "<tr><td colspan='9'>الجدول غير موجود!</td></tr>";
            }
            ?>
        </tbody>
    </table>

</body>
</html>

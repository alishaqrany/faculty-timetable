<?php
require_once("../db_config.php");

// التحقق من تسجيل الدخول
session_start();

// التحقق من وجود معرف الجلسة للمستخدم المسجل
if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

$message = "";
$message_type = "";

// جلب الرسالة من الجلسة، إذا كانت موجودة
$message = isset($_SESSION['message']) ? $_SESSION['message'] : "";
$message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : "";

// مسح الرسالة من الجلسة بعد عرضها
unset($_SESSION['message']);
unset($_SESSION['message_type']);



// التحقق من إرسال النموذج وإدخال البيانات في قاعدة البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section_name = mysqli_real_escape_string($conn, $_POST['section_name']);
    $department_id = mysqli_real_escape_string($conn, $_POST['department_id']);
    $level_id = mysqli_real_escape_string($conn, $_POST['level_id']);

    // إجراء استعلام INSERT لإدخال البيانات في جدول السكاشن
    $insertQuery = "
    INSERT INTO sections (section_name, department_id, level_id)
    VALUES ('$section_name', '$department_id', '$level_id')
    ";

    $insertResult = mysqli_query($conn, $insertQuery);

    if ($insertResult) {
        $message = "تم إدخال السكشن بنجاح!";
        $message_type = "success";
    } else {
        $message = "حدث خطأ أثناء إدخال السكشن: " . mysqli_error($conn);
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>إضافة سكشن جديد</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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

    <?php include 'navbar.php'; ?>

    <h1>إضافة سكشن جديد</h1>
    <form method="POST" action="" class="form-container">
        <div class="form-group">
            <label for="section_name">اسم السكشن:</label>
            <input type="text" name="section_name" id="section_name" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="department_id">القسم:</label>
            <select name="department_id" id="department_id" class="form-control" required>
                <?php
                $departmentQuery = "SELECT department_id, department_name FROM departments";
                $departmentResult = mysqli_query($conn, $departmentQuery);
                if (mysqli_num_rows($departmentResult) > 0) {
                    while ($departmentRow = mysqli_fetch_assoc($departmentResult)) {
                        $department_id = $departmentRow['department_id'];
                        $department_name = $departmentRow['department_name'];
                        echo "<option value='$department_id'>$department_name</option>";
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
                        $level_id = $levelRow['level_id'];
                        $level_name = $levelRow['level_name'];
                        echo "<option value='$level_id'>$level_name</option>";
                    }
                }
                ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">إضافة السكشن</button>
    </form>

    <h2 class="mt-5">عرض السكاشن</h2>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>اسم السكشن</th>
                <th>اسم القسم</th>
                <th>اسم المستوى</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // استعلام لاسترداد البيانات من جدول السكاشن
            $selectQuery = "
            SELECT sections.section_id, sections.section_name, departments.department_name, levels.level_name
            FROM sections
            INNER JOIN departments ON sections.department_id = departments.department_id
            INNER JOIN levels ON sections.level_id = levels.level_id
            ";
            $result = mysqli_query($conn, $selectQuery);

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . $row['section_name'] . "</td>";
                    echo "<td>" . $row['department_name'] . "</td>";
                    echo "<td>" . $row['level_name'] . "</td>";
                    echo "<td>
                            <a href='edit_section.php?id={$row['section_id']}' class='btn btn-warning'>تعديل</a>
                            <a href='delete_section.php?id={$row['section_id']}' class='btn btn-danger' onclick='return confirm(\"هل أنت متأكد من حذف هذا السكشن؟\")'>حذف</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>لا توجد سجلات محفوظة</td></tr>";
            }
            ?>
        </tbody>
    </table>

</body>
</html>

<?php
require_once("../db_config.php");
require_once("../core/csrf.php");
require_once("../core/flash.php");

// التحقق من تسجيل الدخول
session_start();

// التحقق من وجود معرف الجلسة للمستخدم المسجل
if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

list($message, $message_type) = flash_consume();


// التحقق من إرسال النموذج وإدخال البيانات في قاعدة البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classroom_name = trim($_POST['classroom_name']);

    if ($classroom_name === '') {
        flash_redirect('classrooms.php', 'يرجى إدخال اسم القاعة الدراسية.', 'error');
    }

    $insertQuery = "INSERT INTO classrooms (classroom_name) VALUES (?)";
    $insertStmt = mysqli_prepare($conn, $insertQuery);
    if ($insertStmt) {
        mysqli_stmt_bind_param($insertStmt, "s", $classroom_name);
    }

    $insertResult = $insertStmt && mysqli_stmt_execute($insertStmt);

    if ($insertResult) {
        flash_redirect('classrooms.php', 'تم إدخال القاعة الدراسية بنجاح!', 'success');
    } else {
        if ($insertStmt) {
            error_log("Insert classroom failed: " . mysqli_stmt_error($insertStmt));
        } else {
            error_log("Insert classroom prepare failed: " . mysqli_error($conn));
        }
        flash_redirect('classrooms.php', 'حدث خطأ أثناء إدخال القاعة الدراسية.', 'error');
    }

    if ($insertStmt) {
        mysqli_stmt_close($insertStmt);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>إضافة قاعة دراسية جديدة</title>
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

    <h1>إضافة قاعة دراسية جديدة</h1>
    <form method="POST" action="">
        <div>
            <label for="classroom_name">اسم القاعة الدراسية:</label>
            <input type="text" name="classroom_name" id="classroom_name" required>
        </div>

        <button type="submit">إضافة القاعة الدراسية</button>
    </form>

    <h2>قائمة القاعات الدراسية</h2>
    <table>
        <thead>
            <tr>
                <th>معرف القاعة</th>
                <th>اسم القاعة الدراسية</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php
                // استعلام SELECT للحصول على جميع القاعات الدراسية
                $selectQuery = "SELECT * FROM classrooms";
                $selectResult = mysqli_query($conn, $selectQuery);

                if ($selectResult && mysqli_num_rows($selectResult) > 0) {
                    $csrfToken = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
                    while ($row = mysqli_fetch_assoc($selectResult)) {
                        echo "<tr>";
                        echo "<td>{$row['classroom_id']}</td>";
                        echo "<td>{$row['classroom_name']}</td>";
                        echo "<td>
                                <a href='edit_classroom.php?id={$row['classroom_id']}' class='btn btn-warning'>تعديل</a>
                                <form method='POST' action='delete_classroom.php' style='display:inline;' onsubmit='return confirm(\"هل أنت متأكد من حذف هذه القاعة الدراسية؟\")'>
                                    <input type='hidden' name='csrf_token' value='{$csrfToken}'>
                                    <input type='hidden' name='id' value='{$row['classroom_id']}'>
                                    <button type='submit' class='btn btn-danger'>حذف</button>
                                </form>
                            </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>لا توجد قاعات دراسية مسجلة</td></tr>";
                }
            ?>
        </tbody>
    </table>
</body>
</html>

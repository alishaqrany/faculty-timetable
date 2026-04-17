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
    $level_name = trim($_POST['level_name']);

    if ($level_name === '') {
        flash_redirect('levels.php', 'يرجى إدخال اسم الفرقة الدراسية.', 'error');
    }

    $insertQuery = "INSERT INTO levels (level_name) VALUES (?)";
    $insertStmt = mysqli_prepare($conn, $insertQuery);
    if ($insertStmt) {
        mysqli_stmt_bind_param($insertStmt, "s", $level_name);
    }

    $insertResult = $insertStmt && mysqli_stmt_execute($insertStmt);

    if ($insertResult) {
        flash_redirect('levels.php', 'تم إدخال الفرقة الدراسية بنجاح!', 'success');
    } else {
        if ($insertStmt) {
            error_log("Insert level failed: " . mysqli_stmt_error($insertStmt));
        } else {
            error_log("Insert level prepare failed: " . mysqli_error($conn));
        }
        flash_redirect('levels.php', 'حدث خطأ أثناء إدخال الفرقة الدراسية.', 'error');
    }

    if ($insertStmt) {
        mysqli_stmt_close($insertStmt);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>إضافة فرق دراسية جديدة</title>
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

    <h1>إضافة فرق دراسية جديدة</h1>
    <form method="POST" action="" class="form-container">
        <div class="form-group">
            <label for="level_name">اسم الفرقة الدراسية:</label>
            <input type="text" name="level_name" id="level_name" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">إضافة الفرقة الدراسية</button>
    </form>

    <h2 class="mt-5">قائمة الفرق الدراسية</h2>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>معرف الفرقة الدراسية</th>
                <th>اسم الفرقة الدراسية</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php
                // استعلام SELECT لاسترجاع الفرق الدراسية
                $selectQuery = "SELECT * FROM levels";
                $result = mysqli_query($conn, $selectQuery);

                if ($result && mysqli_num_rows($result) > 0) {
                    $csrfToken = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . $row['level_id'] . "</td>";
                        echo "<td>" . $row['level_name'] . "</td>";
                        echo "<td>
                                <a href='edit_level.php?id={$row['level_id']}' class='btn btn-warning'>تعديل</a>
                                <form method='POST' action='delete_level.php' style='display:inline;' onsubmit='return confirm(\"هل أنت متأكد من حذف هذه الفرقة الدراسية؟\")'>
                                    <input type='hidden' name='csrf_token' value='{$csrfToken}'>
                                    <input type='hidden' name='id' value='{$row['level_id']}'>
                                    <button type='submit' class='btn btn-danger'>حذف</button>
                                </form>
                            </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>لا توجد فرق دراسية مسجلة</td></tr>";
                }
            ?>
        </tbody>
    </table>

</body>
</html>

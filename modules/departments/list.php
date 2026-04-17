<?php
require_once __DIR__ . '/../../core/bootstrap.php';
require_auth('../../login.php');

list($message, $message_type) = flash_consume();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department_name = trim($_POST['department_name']);

    $insertQuery = "INSERT INTO departments (department_name) VALUES (?)";
    $insertStmt = mysqli_prepare($conn, $insertQuery);

    if ($insertStmt) {
        mysqli_stmt_bind_param($insertStmt, 's', $department_name);
    }

    if ($insertStmt && mysqli_stmt_execute($insertStmt)) {
        $redirectMessage = 'تم إدخال بيانات القسم بنجاح!';
        $redirectType = 'success';
    } else {
        if ($insertStmt) {
            error_log('Insert department failed: ' . mysqli_stmt_error($insertStmt));
        } else {
            error_log('Insert department prepare failed: ' . mysqli_error($conn));
        }
        $redirectMessage = 'حدث خطأ أثناء إدخال بيانات القسم.';
        $redirectType = 'error';
    }

    if ($insertStmt) {
        mysqli_stmt_close($insertStmt);
    }

    flash_redirect('list.php', $redirectMessage, $redirectType);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>إضافة قسم جديد</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../../style.css">
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

    <?php $navPrefix = '../../'; include APP_ROOT . '/views/shared/navbar.php'; ?>

    <h1>إضافة قسم جديد</h1>
    <form method="POST" action="" class="form-container">
        <div class="form-group">
            <label for="department_name">اسم القسم:</label>
            <input type="text" name="department_name" id="department_name" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">إضافة القسم</button>
    </form>

    <h2 class="mt-5">قائمة الأقسام</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>معرف القسم</th>
                <th>اسم القسم</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $selectQuery = "SELECT * FROM departments";
                $selectResult = mysqli_query($conn, $selectQuery);

                if ($selectResult && mysqli_num_rows($selectResult) > 0) {
                    $csrfToken = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
                    while ($row = mysqli_fetch_assoc($selectResult)) {
                        echo "<tr>";
                        echo "<td>{$row['department_id']}</td>";
                        echo "<td>{$row['department_name']}</td>";
                        echo "<td>
                                <a href='edit.php?id={$row['department_id']}' class='btn btn-warning'>تعديل</a>
                                <form method='POST' action='delete.php' style='display:inline;' onsubmit='return confirm(\"هل أنت متأكد من حذف هذا القسم؟\")'>
                                    <input type='hidden' name='csrf_token' value='{$csrfToken}'>
                                    <input type='hidden' name='id' value='{$row['department_id']}'>
                                    <button type='submit' class='btn btn-danger'>حذف</button>
                                </form>
                            </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>لا توجد أقسام مسجلة</td></tr>";
                }
            ?>
        </tbody>
    </table>

</body>
</html>

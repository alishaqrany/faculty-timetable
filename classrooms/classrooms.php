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
    $classroom_name = mysqli_real_escape_string($conn, $_POST['classroom_name']);

    // إجراء استعلام INSERT لإدخال البيانات في جدول القاعات الدراسية
    $insertQuery = "
    INSERT INTO classrooms (classroom_name)
    VALUES ('$classroom_name')
    ";

    $insertResult = mysqli_query($conn, $insertQuery);

    if ($insertResult) {
        $message = "تم إدخال القاعة الدراسية بنجاح!";
        $message_type = "success";
    } else {
        $message = "حدث خطأ أثناء إدخال القاعة الدراسية: " . mysqli_error($conn);
        $message_type = "error";
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
                    while ($row = mysqli_fetch_assoc($selectResult)) {
                        echo "<tr>";
                        echo "<td>{$row['classroom_id']}</td>";
                        echo "<td>{$row['classroom_name']}</td>";
                        echo "<td>
                                <a href='edit_classroom.php?id={$row['classroom_id']}' class='btn btn-warning'>تعديل</a>
                                <a href='delete_classroom.php?id={$row['classroom_id']}' class='btn btn-danger' onclick='return confirm(\"هل أنت متأكد من حذف هذه القاعة الدراسية؟\")'>حذف</a>
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

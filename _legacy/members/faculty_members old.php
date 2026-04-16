<?php
require_once("../db_config.php");

// التحقق من تسجيل الدخول
session_start();

// التحقق من وجود معرف الجلسة للمستخدم المسجل
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php"); // إعادة توجيه المستخدم إلى صفحة تسجيل الدخول إذا لم يكن مسجل الدخول
    exit();
}


$message = "";
$message_type = "";
$username = "";
$password = "";

// جلب الرسالة من الجلسة، إذا كانت موجودة
$message = isset($_SESSION['message']) ? $_SESSION['message'] : "";
$message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : "";

// مسح الرسالة من الجلسة بعد عرضها
unset($_SESSION['message']);
unset($_SESSION['message_type']);


// التحقق من إرسال النموذج وإدخال البيانات في قاعدة البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_name = mysqli_real_escape_string($conn, $_POST['member_name']);
    $academic_degree = mysqli_real_escape_string($conn, $_POST['academic_degree']);
    $join_date = mysqli_real_escape_string($conn, $_POST['join_date']);
    $ranking = mysqli_real_escape_string($conn, $_POST['ranking']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // إجراء استعلام INSERT لإدخال البيانات في جدول faculty_members
    $insertQuery = "
    INSERT INTO faculty_members (member_name, academic_degree, join_date, ranking, role)
    VALUES ('$member_name', '$academic_degree', '$join_date', '$ranking', '$role')
    ";

    $insertResult = mysqli_query($conn, $insertQuery);

    // بعد عملية الإدخال في جدول faculty_members
    // إضافة بيانات تسجيل الدخول الخاصة بعضو الهيئة في جدول users
    if ($insertResult) {
        // إنشاء اسم مستخدم عشوائي مختصر
        $username = 'user_' . substr(uniqid(), -6);

        // إنشاء كلمة مرور عشوائية
        $password = bin2hex(random_bytes(3));

        // تشفير كلمة المرور باستخدام password_hash
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // إدخال بيانات المستخدم في جدول users
        $insertUserQuery = "
        INSERT INTO users (username, password, member_id)
        VALUES ('$username', '$hashed_password', LAST_INSERT_ID())
        ";

        $insertUserResult = mysqli_query($conn, $insertUserQuery);

        if ($insertUserResult) {
            $message = "تم إدخال البيانات وإنشاء حساب المستخدم بنجاح!";
            $message_type = "success";
        } else {
            $message = "حدث خطأ أثناء إنشاء حساب المستخدم: " . mysqli_error($conn);
            $message_type = "error";
        }
    } else {
        $message = "حدث خطأ أثناء إدخال البيانات: " . mysqli_error($conn);
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>إضافة أعضاء هيئة التدريس</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

        <h1 class="mb-4">إضافة أعضاء هيئة التدريس</h1>
        <form method="POST" action="" class="form-container">
            <div class="form-group">
                <label for="member_name">الإسم :</label>
                <input type="text" name="member_name" id="member_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="academic_degree">الدرجة العلمية:</label>
                <div class="input-group">
                    <select name="academic_degree" id="academic_degree" class="form-control" required>
                        <option value="أستاذ">أستاذ</option>
                        <option value="أستاذ مساعد">أستاذ مساعد</option>
                        <option value="مدرس">مدرس</option>
                        <option value="مدرس مساعد">مدرس مساعد</option>
                        <option value="معيد">معيد</option>
                    </select>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-secondary" id="addDoctorate">إضافة درجة الدكتور</button>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="join_date">تاريخ التعيين:</label>
                <input type="date" name="join_date" id="join_date" class="form-control">
            </div>
            <div class="form-group">
                <label for="ranking">الترتيب حسب الأقدمية:</label>
                <input type="number" name="ranking" id="ranking" class="form-control">
            </div>
            <div class="form-group">
                <label for="role">الدور:</label>
                <select name="role" id="role" class="form-control">
                    <option value="استاذ المادة">استاذ المادة</option>
                    <option value="معاون">معاون</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">إرسال</button>
        </form>

        <?php if ($message): ?>
        <div class="alert mt-3 <?php echo $message_type === 'error' ? 'alert-danger' : 'alert-success'; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <?php if ($username && $password): ?>
        <div class="mt-3">
            <p>اسم المستخدم: <strong><?php echo $username; ?></strong></p>
            <p>كلمة المرور: <strong><?php echo $password; ?></strong></p>
        </div>
        <?php endif; ?>

        <h2 class="mt-5">قائمة أعضاء هيئة التدريس</h2>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>رقم العضو</th>
                    <th>اسم العضو</th>
                    <th>اسم المستخدم</th>
                    <th>الدرجة العلمية</th>
                    <th>تاريخ الانضمام</th>
                    <th>الترتيب حسب الأقدمية</th>
                    <th>الدور</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $selectQuery = "SELECT fm.*, u.username FROM faculty_members fm LEFT JOIN users u ON fm.member_id = u.member_id";
                    $result = mysqli_query($conn, $selectQuery);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $row['member_id'] . "</td>";
                            echo "<td>" . $row['member_name'] . "</td>";
                            echo "<td>" . $row['username'] . "</td>";
                            echo "<td>" . $row['academic_degree'] . "</td>";
                            echo "<td>" . $row['join_date'] . "</td>";
                            echo "<td>" . $row['ranking'] . "</td>";
                            echo "<td>" . $row['role'] . "</td>";
                            echo "<td><a href='edit_member.php?id=" . $row['member_id'] . "' class='btn btn-warning'>تعديل</a> <a href='delete_member.php?id=" . $row['member_id'] . "' class='btn btn-danger' onclick='return confirm(\"هل أنت متأكد من حذف هذا العضو؟\")'>حذف</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8'>لا يوجد أعضاء موجودين حاليًا.</td></tr>";
                    }
                ?>
            </tbody>
        </table>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function() {
        $('#addDoctorate').click(function() {
            var newDegree = prompt("يرجى إدخال درجة الدكتور الجديدة:");
            if (newDegree) {
                $('#academic_degree').append('<option value="' + newDegree + '">' + newDegree + '</option>');
                $('#academic_degree').val(newDegree);
            }
        });
    });
    </script>

</body>
</html>

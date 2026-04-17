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

$message = "";
$message_type = "";
$username = "";
$password = "";

list($message, $message_type) = flash_consume();

if (isset($_SESSION['generated_username'], $_SESSION['generated_password'])) {
    $username = (string)$_SESSION['generated_username'];
    $password = (string)$_SESSION['generated_password'];
    unset($_SESSION['generated_username'], $_SESSION['generated_password']);
}

// التحقق من إرسال النموذج وإدخال البيانات في قاعدة البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_name = trim($_POST['member_name']);
    $academic_degree = trim($_POST['academic_degree']);
    $join_date = trim($_POST['join_date']);
    $ranking = trim((string)$_POST['ranking']);
    $role = trim($_POST['role']);

    if ($member_name === '' || $academic_degree === '' || $role === '' || ($ranking !== '' && !ctype_digit($ranking))) {
        $insertResult = false;
        $insertStmt = null;
    } else {
        $rankingValue = ($ranking === '') ? null : (int)$ranking;
        $insertQuery = "INSERT INTO faculty_members (member_name, academic_degree, join_date, ranking, role) VALUES (?, ?, ?, ?, ?)";
        $insertStmt = mysqli_prepare($conn, $insertQuery);
        if ($insertStmt) {
            mysqli_stmt_bind_param($insertStmt, "sssis", $member_name, $academic_degree, $join_date, $rankingValue, $role);
        }
        $insertResult = $insertStmt && mysqli_stmt_execute($insertStmt);
    }

    // بعد عملية الإدخال في جدول faculty_members
    if ($insertResult) {
        // إنشاء اسم مستخدم عشوائي مختصر
        $username = 'user_' . substr(uniqid(), -6);

        // إنشاء كلمة مرور عشوائية
        $password = bin2hex(random_bytes(3));

        // تشفير كلمة المرور باستخدام password_hash
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // إدخال بيانات المستخدم في جدول users
        $newMemberId = mysqli_insert_id($conn);
        $insertUserQuery = "INSERT INTO users (username, password, member_id) VALUES (?, ?, ?)";
        $insertUserStmt = mysqli_prepare($conn, $insertUserQuery);
        if ($insertUserStmt) {
            mysqli_stmt_bind_param($insertUserStmt, "ssi", $username, $hashed_password, $newMemberId);
        }

        $insertUserResult = $insertUserStmt && mysqli_stmt_execute($insertUserStmt);

        if ($insertUserResult) {
            $_SESSION['generated_username'] = $username;
            $_SESSION['generated_password'] = $password;
            flash_redirect('faculty_members.php', 'تم إدخال البيانات وإنشاء حساب المستخدم بنجاح!', 'success');
        } else {
            if ($insertUserStmt) {
                error_log("Create user for member failed: " . mysqli_stmt_error($insertUserStmt));
            } else {
                error_log("Create user for member prepare failed: " . mysqli_error($conn));
            }
            flash_redirect('faculty_members.php', 'حدث خطأ أثناء إنشاء حساب المستخدم.', 'error');
        }

        if ($insertUserStmt) {
            mysqli_stmt_close($insertUserStmt);
        }
    } else {
        if (isset($insertStmt) && $insertStmt) {
            error_log("Insert faculty member failed: " . mysqli_stmt_error($insertStmt));
        } else {
            error_log("Insert faculty member prepare failed: " . mysqli_error($conn));
        }
        flash_redirect('faculty_members.php', 'حدث خطأ أثناء إدخال البيانات.', 'error');
    }

    if (isset($insertStmt) && $insertStmt) {
        mysqli_stmt_close($insertStmt);
    }
}

// جلب الدرجات العلمية من قاعدة البيانات
$degreesQuery = "SELECT * FROM academic_degrees";
$degreesResult = mysqli_query($conn, $degreesQuery);

?>

<!DOCTYPE html>
<html>
<head>
    <title>إضافة أعضاء هيئة التدريس</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../style.css">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
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
                    <?php
                    while ($degree = mysqli_fetch_assoc($degreesResult)) {
                        echo "<option value='{$degree['degree_name']}'>{$degree['degree_name']}</option>";
                    }
                    ?>
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
                    $csrfToken = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . $row['member_id'] . "</td>";
                        echo "<td>" . $row['member_name'] . "</td>";
                        echo "<td>" . $row['username'] . "</td>";
                        echo "<td>" . $row['academic_degree'] . "</td>";
                        echo "<td>" . $row['join_date'] . "</td>";
                        echo "<td>" . $row['ranking'] . "</td>";
                        echo "<td>" . $row['role'] . "</td>";
                        echo "<td><a href='edit_member.php?id=" . $row['member_id'] . "' class='btn btn-warning'>تعديل</a> <form method='POST' action='delete_member.php' style='display:inline;' onsubmit='return confirm(\"هل أنت متأكد من حذف هذا العضو؟\")'><input type='hidden' name='csrf_token' value='{$csrfToken}'><input type='hidden' name='id' value='" . $row['member_id'] . "'><button type='submit' class='btn btn-danger'>حذف</button></form></td>";
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
                $.post('add_degree.php', { degree_name: newDegree }, function(response) {
                    if (response.success) {
                        $('#academic_degree').append('<option value="' + newDegree + '">' + newDegree + '</option>');
                        $('#academic_degree').val(newDegree);
                    } else {
                        alert("حدث خطأ أثناء إضافة الدرجة العلمية: " + response.message);
                    }
                }, 'json');
            }
        });
    });
    </script>

</body>
</html>

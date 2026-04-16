<?php
require_once("../db_config.php");

// التحقق من تسجيل الدخول
session_start();

// التحقق من وجود معرف الجلسة للمستخدم المسجل
if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php"); // إعادة توجيه المستخدم إلى صفحة تسجيل الدخول إذا لم يكن مسجل الدخول
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


// التحقق من إرسال النموذج وحفظ البيانات إذا تم الإرسال
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id = $_POST['member_id'];
    $subject_id = $_POST['subject_id'];
    $section_id = $_POST['section_id'];

    // إدخال البيانات في جدول الاختيارات
    $insertQuery = "
    INSERT INTO member_courses (member_id, subject_id, section_id)
    VALUES ('$member_id', '$subject_id', '$section_id')
    ";

    if ($conn->query($insertQuery) === TRUE) {
        $message = "تم حفظ الاختيارات بنجاح!";
        $message_type = "success";
    } else {
        $message = "حدث خطأ أثناء حفظ الاختيارات: " . $conn->error;
        $message_type = "error";
    }
}

// التحقق من إرسال طلب الحذف
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $deleteQuery = "DELETE FROM member_courses WHERE member_course_id = '$delete_id'";

    if ($conn->query($deleteQuery) === TRUE) {
        $message = "تم حذف السجل بنجاح!";
        $message_type = "success";
    } else {
        $message = "حدث خطأ أثناء حذف السجل: " . $conn->error;
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>اختيار المواد والسكاشن</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../style.css">
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

    <h1>اختيار المواد والسكاشن</h1>

    <form method="POST" action="" >
        <div class="form-group">
            <label for="member_id">اختر عضو هيئة التدريس:</label>
            <select name="member_id" id="member_id" class="form-control" required>
                <?php
                // استعلام لاسترداد قائمة أعضاء هيئة التدريس
                $membersQuery = "SELECT * FROM faculty_members";
                $membersResult = $conn->query($membersQuery);

                if ($membersResult->num_rows > 0) {
                    while ($row = $membersResult->fetch_assoc()) {
                        echo "<option value='" . $row['member_id'] . "'>" . $row['member_name'] . "</option>";
                    }
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="subject_id">اختر المادة:</label>
            <select name="subject_id" id="subject_id" class="form-control" required>
                <?php
                // استعلام لاسترداد قائمة المواد
                $subjectsQuery = "SELECT * FROM subjects";
                $subjectsResult = $conn->query($subjectsQuery);

                if ($subjectsResult->num_rows > 0) {
                    while ($row = $subjectsResult->fetch_assoc()) {
                        echo "<option value='" . $row['subject_id'] . "'>" . $row['subject_name'] . "</option>";
                    }
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="section_id">اختر السكاشن:</label>
            <select name="section_id" id="section_id" class="form-control" required>
                <?php
                // استعلام لاسترداد قائمة السكاشن
                $sectionsQuery = "SELECT * FROM sections";
                $sectionsResult = $conn->query($sectionsQuery);

                if ($sectionsResult->num_rows > 0) {
                    while ($row = $sectionsResult->fetch_assoc()) {
                        echo "<option value='" . $row['section_id'] . "'>" . $row['section_name'] . "</option>";
                    }
                }
                ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">حفظ الاختيارات</button>
    </form>

    <h2 class="mt-5">السجلات المحفوظة:</h2>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>اسم العضو</th>
                <th>اسم المادة</th>
                <th>اسم السكاشن</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // استعلام لاسترداد السجلات المحفوظة
            $recordsQuery = "SELECT member_courses.member_course_id, faculty_members.member_name, subjects.subject_name, sections.section_name
                             FROM member_courses
                             INNER JOIN faculty_members ON member_courses.member_id = faculty_members.member_id
                             INNER JOIN subjects ON member_courses.subject_id = subjects.subject_id
                             INNER JOIN sections ON member_courses.section_id = sections.section_id";
            $recordsResult = $conn->query($recordsQuery);

            if ($recordsResult->num_rows > 0) {
                while ($row = $recordsResult->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['member_name'] . "</td>";
                    echo "<td>" . $row['subject_name'] . "</td>";
                    echo "<td>" . $row['section_name'] . "</td>";
                    echo "<td>
                            <a href='edit_record.php?id=" . $row['member_course_id'] . "' class='btn btn-warning'>تعديل</a>
                            <a href='?delete_id=" . $row['member_course_id'] . "' class='btn btn-danger' onclick='return confirm(\"هل أنت متأكد أنك تريد الحذف؟\");'>حذف</a>
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

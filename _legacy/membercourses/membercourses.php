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


// التحقق من إرسال النموذج وحفظ البيانات إذا تم الإرسال
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id = $_POST['member_id'];
    $subject_id = $_POST['subject_id'];
    $section_id = $_POST['section_id'];

    if (!ctype_digit((string)$member_id) || !ctype_digit((string)$subject_id) || !ctype_digit((string)$section_id)) {
        flash_redirect('membercourses.php', 'حدث خطأ أثناء حفظ الاختيارات: بيانات غير صالحة.', 'error');
    } else {
        $member_id = (int)$member_id;
        $subject_id = (int)$subject_id;
        $section_id = (int)$section_id;

        $insertQuery = "INSERT INTO member_courses (member_id, subject_id, section_id) VALUES (?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        if ($insertStmt) {
            $insertStmt->bind_param("iii", $member_id, $subject_id, $section_id);
        }

        if ($insertStmt && $insertStmt->execute()) {
            flash_redirect('membercourses.php', 'تم حفظ الاختيارات بنجاح!', 'success');
        } else {
            if ($insertStmt) {
                error_log("Insert member course failed: " . $insertStmt->error);
            } else {
                error_log("Insert member course prepare failed: " . $conn->error);
            }
            flash_redirect('membercourses.php', 'حدث خطأ أثناء حفظ الاختيارات.', 'error');
        }

        if ($insertStmt) {
            $insertStmt->close();
        }
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
                $csrfToken = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
                while ($row = $recordsResult->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['member_name'] . "</td>";
                    echo "<td>" . $row['subject_name'] . "</td>";
                    echo "<td>" . $row['section_name'] . "</td>";
                    echo "<td>
                            <a href='edit_record.php?id=" . $row['member_course_id'] . "' class='btn btn-warning'>تعديل</a>
                            <form method='POST' action='delete_record.php' style='display:inline;' onsubmit='return confirm(\"هل أنت متأكد أنك تريد الحذف؟\");'>
                                <input type='hidden' name='csrf_token' value='{$csrfToken}'>
                                <input type='hidden' name='id' value='" . $row['member_course_id'] . "'>
                                <button type='submit' class='btn btn-danger'>حذف</button>
                            </form>
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

<?php
$target = "../../subjects/edit_subject.php";
if (isset($_GET['subject_id'])) {
    $target .= "?subject_id=" . urlencode($_GET['subject_id']);
}
header("Location: " . $target, true, 302);
exit();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit'])) {
        // تحديث السجل

        // التحقق من وجود قيم في النموذج
        if (isset($_POST['subject_id']) && isset($_POST['subject_name']) && isset($_POST['department_id']) && isset($_POST['level_id']) && isset($_POST['hours'])) {
            $subject_id = $_POST['subject_id'];
            $subject_name = $_POST['subject_name'];
            $department_id = $_POST['department_id'];
            $level_id = $_POST['level_id'];
            $hours = $_POST['hours'];

            // استعلام التحديث
            $updateQuery = "
            UPDATE $tableName
            SET subject_name = '$subject_name', department_id = '$department_id', level_id = '$level_id', hours = '$hours'
            WHERE subject_id = '$subject_id'
            ";

            $updateResult = mysqli_query($conn, $updateQuery);
            if ($updateResult) {
                header("Location: subjects.php");
                exit();
            } else {
                echo "<p>حدث خطأ أثناء تحديث السجل: " . mysqli_error($conn) . "</p>";
            }
        }
    } elseif (isset($_POST['delete'])) {
        // حذف السجل

        // التحقق من وجود قيم في النموذج
        if (isset($_POST['subject_id'])) {
            $subject_id = $_POST['subject_id'];

            // استعلام الحذف
            $deleteQuery = "
            DELETE FROM $tableName
            WHERE subject_id = '$subject_id'
            ";

            $deleteResult = mysqli_query($conn, $deleteQuery);
            if ($deleteResult) {
                header("Location: subjects.php");
                exit();
            } else {
                echo "<p>حدث خطأ أثناء حذف السجل: " . mysqli_error($conn) . "</p>";
            }
        }
    }
}

// تحقق من وجود قيمة subject_id في رابط الصفحة
if (isset($_GET['subject_id'])) {
    $subject_id = $_GET['subject_id'];

    // استعلام الاستعراض
    $selectQuery = "
    SELECT subjects.*, departments.department_name, levels.level_name
    FROM $tableName
    INNER JOIN departments ON subjects.department_id = departments.department_id
    INNER JOIN levels ON subjects.level_id = levels.level_id
    WHERE subject_id = '$subject_id'
    ";

    $selectResult = mysqli_query($conn, $selectQuery);
    if ($selectResult && mysqli_num_rows($selectResult) > 0) {
        $row = mysqli_fetch_assoc($selectResult);
        $subject_name = $row['subject_name'];
        $department_id = $row['department_id'];
        $department_name = $row['department_name'];
        $level_id = $row['level_id'];
        $level_name = $row['level_name'];
        $hours = $row['hours'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>تعديل المواد</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 20px;
        }

        form {
            display: inline-block;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 10px;
        }

        input[type="text"], select {
            width: 250px;
            padding: 5px;
            font-size: 16px;
        }

        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

    </style>
</head>
<body>
    <h1>تعديل المواد</h1>
    <form method="post">
        <label for="subject_name">اسم المادة:</label>
        <input type="text" id="subject_name" name="subject_name" value="<?php echo $subject_name; ?>" required>
        <br>
        <label for="department_id">القسم:</label>
        <select id="department_id" name="department_id" required>
            <option value="<?php echo $department_id; ?>" selected><?php echo $department_name; ?></option>
            <?php
                // استعلام الاقسام
                $departmentsQuery = "SELECT * FROM departments";
                $departmentsResult = mysqli_query($conn, $departmentsQuery);
                if ($departmentsResult && mysqli_num_rows($departmentsResult) > 0) {
                    while ($department = mysqli_fetch_assoc($departmentsResult)) {
                        if ($department['department_id'] != $department_id) {
                            echo "<option value='{$department['department_id']}'>{$department['department_name']}</option>";
                        }
                    }
                }
            ?>
        </select>
        <br>
        <label for="level_id">المستوى:</label>
        <select id="level_id" name="level_id" required>
            <option value="<?php echo $level_id; ?>" selected><?php echo $level_name; ?></option>
            <?php
                // استعلام المستويات
                $levelsQuery = "SELECT * FROM levels";
                $levelsResult = mysqli_query($conn, $levelsQuery);
                if ($levelsResult && mysqli_num_rows($levelsResult) > 0) {
                    while ($level = mysqli_fetch_assoc($levelsResult)) {
                        if ($level['level_id'] != $level_id) {
                            echo "<option value='{$level['level_id']}'>{$level['level_name']}</option>";
                        }
                    }
                }
            ?>
        </select>
        <br>
        <label for="hours">عدد الساعات:</label>
        <input type="text" id="hours" name="hours" value="<?php echo $hours; ?>" required>
        <br>
        <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
        <button type="submit" name="edit">تعديل</button>
        <button type="submit" name="delete">حذف</button>
    </form>
</body>
</html>

<?php
    } else {
        echo "<p>لا يمكن العثور على المادة المحددة</p>";
    }
} else {
    echo "<p>لا يوجد معرف للمادة المحددة</p>";
}
?>

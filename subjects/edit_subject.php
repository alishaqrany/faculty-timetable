<?php
require_once("../db_config.php");
session_start();

if (!isset($_SESSION['member_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['subject_id'])) {
        $subject_id = $_GET['subject_id'];

        // استعلام لاسترداد بيانات المادة المحددة
        $selectQuery = "SELECT * FROM subjects WHERE subject_id = $subject_id";
        $selectResult = mysqli_query($conn, $selectQuery);

        if ($selectResult) {
            $row = mysqli_fetch_assoc($selectResult);
            $subject_name = $row['subject_name'];
            $department_id = $row['department_id'];
            $level_id = $row['level_id'];
            $hours = $row['hours'];
        } else {
            echo "<p>حدث خطأ أثناء استرداد بيانات المادة: " . mysqli_error($conn) . "</p>";
            exit;
        }
    } else {
        echo "<p>لم يتم تحديد معرّف المادة.</p>";
        exit;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit'])) {
        $subject_id = $_POST['subject_id'];
        $subject_name = $_POST['subject_name'];
        $department_id = $_POST['department_id'];
        $level_id = $_POST['level_id'];
        $hours = $_POST['hours'];

        // استعلام لتحديث بيانات المادة
        $updateQuery = "UPDATE subjects SET subject_name = '$subject_name', department_id = $department_id, level_id = $level_id, hours = $hours WHERE subject_id = $subject_id";
        $updateResult = mysqli_query($conn, $updateQuery);

        if ($updateResult) {
            echo "<p>تم تحديث بيانات المادة بنجاح!</p>";
            header("Location: subjects.php");
            exit();

        } else {
            echo "<p>حدث خطأ أثناء تحديث بيانات المادة: " . mysqli_error($conn) . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>تعديل المادة</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 20px;
        }

        label {
            display: inline-block;
            width: 120px;
            text-align: left;
        }

        input[type="text"], select {
            width: 200px;
            padding: 5px;
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
    <h1>تعديل المادة</h1>
    <form method="POST" action="">
        <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
        <div>
            <label>اسم المادة:</label>
            <input type="text" name="subject_name" value="<?php echo $subject_name; ?>">
        </div>
        <div>
            <label>القسم:</label>
            <select name="department_id">
                <?php
                // استعلام لاسترداد الأقسام
                $departmentQuery = "SELECT * FROM departments";
                $departmentResult = mysqli_query($conn, $departmentQuery);

                if ($departmentResult) {
                    while ($department = mysqli_fetch_assoc($departmentResult)) {
                        $selected = ($department['department_id'] == $department_id) ? 'selected' : '';
                        echo "<option value='{$department['department_id']}' $selected>{$department['department_name']}</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div>
            <label>الفرقة (المستوى):</label>
            <select name="level_id">
                <?php
                // استعلام لاسترداد الفرق
                $levelQuery = "SELECT * FROM levels";
                $levelResult = mysqli_query($conn, $levelQuery);

                if ($levelResult) {
                    while ($level = mysqli_fetch_assoc($levelResult)) {
                        $selected = ($level['level_id'] == $level_id) ? 'selected' : '';
                        echo "<option value='{$level['level_id']}' $selected>{$level['level_name']}</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div>
            <label>عدد الساعات:</label>
            <input type="text" name="hours" value="<?php echo $hours; ?>">
        </div>
        <div>
            <button type="submit" name="submit">حفظ التغييرات</button>
        </div>
    </form>
</body>
</html>

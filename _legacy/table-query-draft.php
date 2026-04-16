<?php
session_start();

if (!isset($_SESSION['member_id'])) {
  header("Location: login.php");
  exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>استعلام الجدول الزمني</title>
  <style>
    body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 20px;
        }

        label {
            display: block;
            margin-bottom: 10px;
        }

        select {
            width: 200px;
            margin-bottom: 10px;
        }

        table {
            margin-top: 20px;
            border-collapse: collapse;
            width: 100%;
        }

        table th, table td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #f2f2f2;
        }

  </style>
</head>
<body>
  <h1>استعلام الجدول الزمني</h1>

  <form method="post" action="">
    <label for="department">القسم :</label>
    <select id="department" name="department">
        <?php
        require_once("db_config.php");

            // استعلام SQL لاسترداد بيانات الاقسام من جدول departments
            $departmentQuery = "SELECT * FROM departments";
            // تنفيذ الاستعلام
            $departmentResult = mysqli_query($conn, $departmentQuery);
            // عرض خيارات القسم في القائمة المنسدلة
            while ($row = mysqli_fetch_assoc($departmentResult)) {
                echo '<option value="' . $row['department_id'] . '">' . $row['department_name'] . '</option>';
            }
        ?>
    </select>

    <label for="level">الفرقة:</label>
    <select id="level" name="level">
      <?php
        // استعلام SQL لاسترداد بيانات الفرق من جدول levels
        $levelQuery = "SELECT * FROM levels";

        // تنفيذ الاستعلام
        $levelResult = mysqli_query($conn, $levelQuery);

        // عرض خيارات الفرق في القائمة المنسدلة
        while ($row = mysqli_fetch_assoc($levelResult)) {
          echo '<option value="' . $row['level_id'] . '">' . $row['level_name'] . '</option>';
        }
      ?>
    </select>

    <input type="submit" value="عرض الجدول الزمني">
  </form>

  <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      // استعلام SQL لاسترداد الجدول الزمني بناءً على الفرقة والشعبة المحددة
      $timetableQuery = "SELECT subjects.subject_name, faculty_members.member_name, classrooms.classroom_name, sessions.day, sessions.session_name
      FROM timetable
      INNER JOIN member_courses ON timetable.member_course_id = member_courses.member_course_id
      INNER JOIN subjects ON member_courses.subject_id = subjects.subject_id
      INNER JOIN faculty_members ON member_courses.member_id = faculty_members.member_id
      INNER JOIN classrooms ON timetable.classroom_id = classrooms.classroom_id
      INNER JOIN sessions ON timetable.session_id = sessions.session_id
      WHERE subjects.department_id = " . $_POST['department'] . " AND subjects.level_id = " . $_POST['level'];

      // تنفيذ الاستعلام
      $timetableResult = mysqli_query($conn, $timetableQuery);

      // التحقق من وجود بيانات
      if (mysqli_num_rows($timetableResult) > 0) {
        echo '<h2>جدول الفرقة والشعبة المحددة:</h2>';
        echo '<table>';
        echo '<tr><th>المادة</th><th>عضو هيئة التدريس</th><th>القاعة</th><th>اليوم</th><th>الجلسة</th></tr>';

        // عرض البيانات في الجدول
        while ($row = mysqli_fetch_assoc($timetableResult)) {
          echo '<tr>';
          echo '<td>' . $row['subject_name'] . '</td>';
          echo '<td>' . $row['member_name'] . '</td>';
          echo '<td>' . $row['classroom_name'] . '</td>';
          echo '<td>' . $row['day'] . '</td>';
          echo '<td>' . $row['session_name'] . '</td>';
          echo '</tr>';
        }

        echo '</table>';
      } else {
        echo '<p>لا يوجد بيانات متاحة للفرقة والشعبة المحددة.</p>';
      }

    }
  ?>

------------------------------
</body>
</html>

///// last working code
<!DOCTYPE html>
<html>
<head>
  <title>استعلام الجدول الزمني</title>
  <style>
    /* أنماط CSS للتنسيق */
    table {
      border-collapse: collapse;
    }
    th, td {
      border: 1px solid black;
      padding: 8px;
    }
  </style>
</head>
<body>
<h1>استعلام الجدول الزمني</h1>

<form method="post" action="">
  <label for="department">القسم :</label>
  <select id="department" name="department">
      <?php
      require_once("db_config.php");

          // استعلام SQL لاسترداد بيانات الاقسام من جدول departments
          $departmentQuery = "SELECT * FROM departments";
          // تنفيذ الاستعلام
          $departmentResult = mysqli_query($conn, $departmentQuery);
          // عرض خيارات القسم في القائمة المنسدلة
          while ($row = mysqli_fetch_assoc($departmentResult)) {
              echo '<option value="' . $row['department_id'] . '">' . $row['department_name'] . '</option>';
          }
      ?>
  </select>

  <label for="level">الفرقة:</label>
  <select id="level" name="level">
    <?php
      // استعلام SQL لاسترداد بيانات الفرق من جدول levels
      $levelQuery = "SELECT * FROM levels";

      // تنفيذ الاستعلام
      $levelResult = mysqli_query($conn, $levelQuery);

      // عرض خيارات الفرق في القائمة المنسدلة
      while ($row = mysqli_fetch_assoc($levelResult)) {
        echo '<option value="' . $row['level_id'] . '">' . $row['level_name'] . '</option>';
      }
    ?>
  </select>

  <input type="submit" value="عرض الجدول الزمني">
</form>

  <?php
          require_once("db_config.php");

    // الاستعلام وعرض الجدول الزمني
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      // استعلام SQL لاسترداد الجدول الزمني بناءً على الفرقة والشعبة المحددة
// استعلام SQL لاسترداد الجدول الزمني بناءً على الفرقة والشعبة المحددة
// استعلام SQL لاسترداد الجدول الزمني بناءً على الفرقة والشعبة المحددة
$timetableQuery = "SELECT subjects.subject_name, faculty_members.member_name, classrooms.classroom_name, sessions.day, sessions.session_name
                   FROM timetable
                   INNER JOIN member_courses ON timetable.member_course_id = member_courses.member_course_id
                   INNER JOIN subjects ON member_courses.subject_id = subjects.subject_id
                   INNER JOIN faculty_members ON member_courses.member_id = faculty_members.member_id
                   INNER JOIN classrooms ON timetable.classroom_id = classrooms.classroom_id
                   INNER JOIN sessions ON timetable.session_id = sessions.session_id
                   WHERE subjects.department_id = " . $_POST['department'] . " AND subjects.level_id = " . $_POST['level'];

      // تنفيذ الاستعلام والحصول على النتائج
      $timetableResult = mysqli_query($conn, $timetableQuery);

      // التحقق من وجود بيانات
      if (mysqli_num_rows($timetableResult) > 0) {
        // إنشاء جدول الجدول الزمني
        echo '<h2>جدول الفرقة والشعبة المحددة:</h2>';
        echo '<table>';
        echo '<tr><th>اليوم</th><th>الفترة</th><th>a1</th><th>a2</th><th>a3</th><th>a4</th><th>a5</th></tr>';

        // عرض البيانات في الجدول
        while ($row = mysqli_fetch_assoc($timetableResult)) {
          echo '<tr>';
          echo '<td rowspan="5">' . $row['day'] . '</td>';
          echo '<td>' . $row['session_name'] . '</td>';
          echo '<td>' . $row['subject_name'] . '/' . $row['member_name'] . '/' . $row['classroom_name'] . '</td>';
          echo '</tr>';
          for ($i = 2; $i <= 5; $i++) {
            echo '<tr>';
            echo '<td>' . $row['session_name'] . '</td>';
            echo '<td></td>'; // خلية فارغة
            echo '</tr>';
          }
        }

        echo '</table>';
      } else {
        echo '<p>لا يوجد بيانات متاحة للفرقة والشعبة المحددة.</p>';
      }

    }
  ?>

</body>
</html>

=============================================
last working quiry
=============================================

SELECT 
    s.day AS Day,
    CONCAT(s.start_time, ' - ', s.end_time) AS Period,
    IFNULL(CONCAT(sub.subject_name, ' / ', fm.member_name, ' / ', c.classroom_name), 'لا يوجد') AS Course_Details
FROM
    sessions s
    LEFT JOIN timetable t ON s.session_id = t.session_id
    LEFT JOIN member_courses mc ON t.member_course_id = mc.member_course_id
    LEFT JOIN subjects sub ON mc.subject_id = sub.subject_id
    LEFT JOIN faculty_members fm ON mc.member_id = fm.member_id
    LEFT JOIN classrooms c ON t.classroom_id = c.classroom_id
ORDER BY
    FIELD(s.day, 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس');
    
===================================================

=============>>>>>>>>>>  last updates <<<<<<<<<===============









<!-- 
<!DOCTYPE html>
<html>
<head>
  <title>استعلام الجدول الزمني</title>
  <style>
    /* أنماط CSS للتنسيق */
    table {
      border-collapse: collapse;
    }
    th, td {
      border: 1px solid black;
      padding: 8px;
    }
  </style>
</head>
<body>
<h1>استعلام الجدول الزمني</h1>

<form method="post" action="">
  <label for="department">القسم :</label>
  <select id="department" name="department">
  <?php
      require_once("db_config.php");

          // استعلام SQL لاسترداد بيانات الاقسام من جدول departments
          $departmentQuery = "SELECT * FROM departments";
          // تنفيذ الاستعلام
          $departmentResult = mysqli_query($conn, $departmentQuery);
          // عرض خيارات القسم في القائمة المنسدلة
          while ($row = mysqli_fetch_assoc($departmentResult)) {
              echo '<option value="' . $row['department_id'] . '">' . $row['department_name'] . '</option>';
          }
      ?>
  </select>

  <label for="level">الفرقة:</label>
  <select id="level" name="level">
    <?php
      // استعلام SQL لاسترداد بيانات الفرق من جدول levels
      $levelQuery = "SELECT * FROM levels";

      // تنفيذ الاستعلام
      $levelResult = mysqli_query($conn, $levelQuery);

      // عرض خيارات الفرق في القائمة المنسدلة
      while ($row = mysqli_fetch_assoc($levelResult)) {
        echo '<option value="' . $row['level_id'] . '">' . $row['level_name'] . '</option>';
      }
    ?>
  </select>

  <input type="submit" value="عرض الجدول الزمني">
</form>

<?php
// استعلام SQL لاسترداد جميع الفترات من جدول sessions
$allSessionsQuery = "SELECT day, session_name, start_time, end_time FROM sessions ORDER BY day, start_time";
$allSessionsResult = mysqli_query($conn, $allSessionsQuery);

// التحقق من وجود بيانات
if (mysqli_num_rows($allSessionsResult) > 0) {
  // إنشاء جدول الجدول الزمني
  echo '<h2>جدول الجدول الزمني:</h2>';
  echo '<table>';
  echo '<tr><th>اليوم</th><th colspan="5">الفترة</th></tr>';
  echo '<tr><td></td><td>الفترة الأولى</td><td>الفترة الثانية</td><td>الفترة الثالثة</td><td>الفترة الرابعة</td><td>الفترة الخامسة</td></tr>';

  // عرض البيانات في الجدول
  $currentDay = null;
  while ($sessionRow = mysqli_fetch_assoc($allSessionsResult)) {
    $day = $sessionRow['day'];
    $sessionName = $sessionRow['session_name'];
    $startTime = $sessionRow['start_time'];
    $endTime = $sessionRow['end_time'];

    if ($currentDay !== $day) {
      echo '<tr>';
      echo '<td rowspan="5">' . $day . '</td>';
      $currentDay = $day;
    }

    echo '<td>' . $startTime . ' - ' . $endTime . '</td>';

    // استعلام SQL لاسترداد جميع المواد وأعضاء هيئة التدريس وقاعات التدريس لهذه الفترة والفرقة والقسم المحددة
    $timetableQuery = "SELECT subjects.subject_name, faculty_members.member_name, classrooms.classroom_name
                       FROM timetable
                       INNER JOIN member_courses ON timetable.member_course_id = member_courses.member_course_id
                       INNER JOIN subjects ON member_courses.subject_id = subjects.subject_id
                       INNER JOIN faculty_members ON member_courses.member_id = faculty_members.member_id
                       INNER JOIN classrooms ON timetable.classroom_id = classrooms.classroom_id
                       INNER JOIN sessions ON timetable.session_id = sessions.session_id
                       WHERE sessions.day = '$day' AND sessions.session_name = '$sessionName'
                         AND subjects.department_id = " . $_POST['department'] . " AND subjects.level_id = " . $_POST['level'] . "
                       ORDER BY sessions.day, sessions.start_time"; // ترتيب الجدول الزمني حسب اليوم والفترة

    // تنفيذ الاستعلام والحصول على النتائج
    $timetableResult = mysqli_query($conn, $timetableQuery);

    // عرض الفترات المرتبطة باليوم والفترة الحالية في الجدول
    $filledCells = array();
    while ($row = mysqli_fetch_assoc($timetableResult)) {
      $filledCells[] = $row['subject_name'] . '/' . $row['member_name'] . '/' . $row['classroom_name'];
    }

    for ($i = 0; $i < 5; $i++) {
      echo '<td>';
      if (isset($filledCells[$i])) {
        echo $filledCells[$i];
      } else {
        echo 'لا يوجد';
      }
      echo '</td>';
    }
    echo '</tr>';
  }

  echo '</table>';
} else {
  echo '<p>لا يوجد بيانات متاحة في جدول الجلسات.</p>';
}
?>

</body>
</html> -->




<!DOCTYPE html>
<html>
<head>
  <title>استعلام الجدول الزمني</title>
  <style>
    /* أنماط CSS للتنسيق */
    table {
      border-collapse: collapse;
    }
    th, td {
      border: 1px solid black;
      padding: 8px;
    }
  </style>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<h1>استعلام الجدول الزمني</h1>

<form method="post" action="">
  <label for="department">القسم :</label>
  <select id="department" name="department">
      <?php
      require_once("db_config.php");

          // استعلام SQL لاسترداد بيانات الاقسام من جدول departments
          $departmentQuery = "SELECT * FROM departments";
          // تنفيذ الاستعلام
          $departmentResult = mysqli_query($conn, $departmentQuery);
          // عرض خيارات القسم في القائمة المنسدلة
          while ($row = mysqli_fetch_assoc($departmentResult)) {
              echo '<option value="' . $row['department_id'] . '">' . $row['department_name'] . '</option>';
          }
      ?>
  </select>

  <label for="level">الفرقة:</label>
  <select id="level" name="level">
    <?php
      // استعلام SQL لاسترداد بيانات الفرق من جدول levels
      $levelQuery = "SELECT * FROM levels";

      // تنفيذ الاستعلام
      $levelResult = mysqli_query($conn, $levelQuery);

      // عرض خيارات الفرق في القائمة المنسدلة
      while ($row = mysqli_fetch_assoc($levelResult)) {
        echo '<option value="' . $row['level_id'] . '">' . $row['level_name'] . '</option>';
      }
    ?>
  </select>

  <input type="submit" value="عرض الجدول الزمني">
</form>


<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // استعلام SQL لاسترداد جميع الفترات من جدول sessions
  $allSessionsQuery = "SELECT day, session_name, start_time, end_time FROM sessions ORDER BY day, start_time";
  $allSessionsResult = mysqli_query($conn, $allSessionsQuery);

  // التحقق من وجود بيانات
  if (mysqli_num_rows($allSessionsResult) > 0) {
    // إنشاء جدول الجدول الزمني
    echo '<h2>جدول الجدول الزمني:</h2>';
    echo '<table>';
    echo '<tr><th>اليوم</th><th>الفترة</th><th>a1</th><th>a2</th><th>a3</th><th>a4</th><th>a5</th></tr>';

    // عرض البيانات في الجدول
    while ($sessionRow = mysqli_fetch_assoc($allSessionsResult)) {
      $day = $sessionRow['day'];
      $sessionName = $sessionRow['session_name'];
      $startTime = $sessionRow['start_time'];
      $endTime = $sessionRow['end_time'];

      echo '<tr>';
      echo '<td>' . $day . '</td>';
      echo '<td>' . $startTime . ' - ' . $endTime . '</td>';

      // استعلام SQL لاسترداد جميع المواد وأعضاء هيئة التدريس وقاعات التدريس لهذه الفترة والفرقة والقسم المحددة
      $timetableQuery = "SELECT sections.section_id, subjects.subject_name, faculty_members.member_name, classrooms.classroom_name
                         FROM timetable
                         INNER JOIN member_courses ON timetable.member_course_id = member_courses.member_course_id
                         INNER JOIN subjects ON member_courses.subject_id = subjects.subject_id
                         INNER JOIN faculty_members ON member_courses.member_id = faculty_members.member_id
                         INNER JOIN classrooms ON timetable.classroom_id = classrooms.classroom_id
                         INNER JOIN sessions ON timetable.session_id = sessions.session_id
                         INNER JOIN sections ON member_courses.section_id = sections.section_id
                         WHERE sessions.day = '$day' AND sessions.session_name = '$sessionName'
                           AND subjects.department_id = " . $_POST['department'] . " AND subjects.level_id = " . $_POST['level'] . "
                         ORDER BY sessions.day, sessions.start_time, sections.section_id"; // ترتيب الجدول الزمني حسب اليوم والفترة

      // تنفيذ الاستعلام والحصول على النتائج
      $timetableResult = mysqli_query($conn, $timetableQuery);

      // مصفوفة لتخزين بيانات الفترات المرتبطة باليوم والفترة الحالية في الجدول
      $sectionData = array_fill(1, 5, 'لا يوجد');

      // وضع بيانات الفترة "filledCells" تحت كل قسم في الجدول
      while ($row = mysqli_fetch_assoc($timetableResult)) {
        $sectionData[$row['section_id']] = $row['subject_name'] . '/' . $row['member_name'] . '/' . $row['classroom_name'];
      }

      // عرض البيانات في الجدول
      for ($i = 1; $i <= 5; $i++) {
        echo '<td>' . $sectionData[$i] . '</td>';
      }

      echo '</tr>';
    }

    echo '</table>';
  } else {
    echo '<p>لا يوجد بيانات متاحة في جدول الجلسات.</p>';
  }
}
?>

</body>
</html>



==============================================================================
==============================================================================
==============================================================================
==============================================================================



=================>>>>>>>>\\\\  last working ////// <<<<<<<<<<<==============




<!DOCTYPE html>
<html>
<head>
  <title>استعلام الجدول الزمني</title>
  <style>
    /* أنماط CSS للتنسيق */
    table {
      border-collapse: collapse;
    }
    th, td {
      border: 1px solid black;
      padding: 8px;
    }
  </style>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<h1>استعلام الجدول الزمني</h1>

<form method="post" action="">
  <label for="department">القسم :</label>
  <select id="department" name="department">
      <?php
      require_once("db_config.php");

          // استعلام SQL لاسترداد بيانات الاقسام من جدول departments
          $departmentQuery = "SELECT * FROM departments";
          // تنفيذ الاستعلام
          $departmentResult = mysqli_query($conn, $departmentQuery);
          // عرض خيارات القسم في القائمة المنسدلة
          while ($row = mysqli_fetch_assoc($departmentResult)) {
              echo '<option value="' . $row['department_id'] . '">' . $row['department_name'] . '</option>';
          }
      ?>
  </select>

  <label for="level">الفرقة:</label>
  <select id="level" name="level">
    <?php
      // استعلام SQL لاسترداد بيانات الفرق من جدول levels
      $levelQuery = "SELECT * FROM levels";

      // تنفيذ الاستعلام
      $levelResult = mysqli_query($conn, $levelQuery);

      // عرض خيارات الفرق في القائمة المنسدلة
      while ($row = mysqli_fetch_assoc($levelResult)) {
        echo '<option value="' . $row['level_id'] . '">' . $row['level_name'] . '</option>';
      }
    ?>
  </select>

  <input type="submit" value="عرض الجدول الزمني">
</form>


<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // استعلام SQL لاسترداد جميع الفترات من جدول sessions
  $allSessionsQuery = "SELECT day, session_name, start_time, end_time FROM sessions ORDER BY day, start_time";
  $allSessionsResult = mysqli_query($conn, $allSessionsQuery);

  // التحقق من وجود بيانات
  if (mysqli_num_rows($allSessionsResult) > 0) {
    // إنشاء جدول الجدول الزمني
    echo '<h2>جدول الجدول الزمني:</h2>';
    echo '<table>';
    echo '<tr><th>اليوم</th><th>الفترة</th><th>a1</th><th>a2</th><th>a3</th><th>a4</th><th>a5</th></tr>';

    // عرض البيانات في الجدول
    while ($sessionRow = mysqli_fetch_assoc($allSessionsResult)) {
      $day = $sessionRow['day'];
      $sessionName = $sessionRow['session_name'];
      $startTime = $sessionRow['start_time'];
      $endTime = $sessionRow['end_time'];

      echo '<tr>';
      echo '<td>' . $day . '</td>';
      echo '<td>' . $startTime . ' - ' . $endTime . '</td>';

      // استعلام SQL لاسترداد جميع المواد وأعضاء هيئة التدريس وقاعات التدريس لهذه الفترة والفرقة والقسم المحددة
      $timetableQuery = "SELECT sections.section_id, subjects.subject_name, faculty_members.member_name, classrooms.classroom_name
                         FROM timetable
                         INNER JOIN member_courses ON timetable.member_course_id = member_courses.member_course_id
                         INNER JOIN subjects ON member_courses.subject_id = subjects.subject_id
                         INNER JOIN faculty_members ON member_courses.member_id = faculty_members.member_id
                         INNER JOIN classrooms ON timetable.classroom_id = classrooms.classroom_id
                         INNER JOIN sessions ON timetable.session_id = sessions.session_id
                         INNER JOIN sections ON member_courses.section_id = sections.section_id
                         WHERE sessions.day = '$day' AND sessions.session_name = '$sessionName'
                           AND subjects.department_id = " . $_POST['department'] . " AND subjects.level_id = " . $_POST['level'] . "
                         ORDER BY sessions.day, sessions.start_time, sections.section_id"; // ترتيب الجدول الزمني حسب اليوم والفترة

      // تنفيذ الاستعلام والحصول على النتائج
      $timetableResult = mysqli_query($conn, $timetableQuery);

      // مصفوفة لتخزين بيانات الفترات المرتبطة باليوم والفترة الحالية في الجدول
      $sectionData = array_fill(1, 5, 'لا يوجد');

      // وضع بيانات الفترة "filledCells" تحت كل قسم في الجدول
      while ($row = mysqli_fetch_assoc($timetableResult)) {
        $sectionData[$row['section_id']] = $row['subject_name'] . '/' . $row['member_name'] . '/' . $row['classroom_name'];
      }

      // عرض البيانات في الجدول
      for ($i = 1; $i <= 5; $i++) {
        echo '<td>' . $sectionData[$i] . '</td>';
      }

      echo '</tr>';
    }

    echo '</table>';
  } else {
    echo '<p>لا يوجد بيانات متاحة في جدول الجلسات.</p>';
  }
}
?>

</body>
</html>


==================================================================
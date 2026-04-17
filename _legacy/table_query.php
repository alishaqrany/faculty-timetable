
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

  <?php include 'navbar.php'; ?>
  
  <h1>عرض الجدول </h1>

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

  <input type="submit" value="عرض الجدول ">
</form>

<!-- <?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // استعلام SQL لاسترداد جميع الفترات من جدول sessions
  $allSessionsQuery = "SELECT day, session_name, start_time, end_time FROM sessions ORDER BY FIELD(day, 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'), start_time";
  $allSessionsResult = mysqli_query($conn, $allSessionsQuery);

  // التحقق من وجود بيانات
  if (mysqli_num_rows($allSessionsResult) > 0) {
    // إنشاء جدول الجدول 
    echo '<h2>جدول الجدول :</h2>';
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
                         ORDER BY sessions.start_time, sections.section_id"; // ترتيب الجدول الزمني حسب اليوم والفترة

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
-->







<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // استعلام SQL لاسترداد جميع الفترات من جدول sessions
  $allSessionsQuery = "SELECT day, session_name, start_time, end_time FROM sessions ORDER BY FIELD(day, 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس'), start_time";
  $allSessionsResult = mysqli_query($conn, $allSessionsQuery);

  // التحقق من وجود بيانات
  if (mysqli_num_rows($allSessionsResult) > 0) {
    // إنشاء جدول الجدول الزمني
    echo '<h2>جدول الجدول الزمني:</h2>';
    echo '<table>';
    echo '<tr><th>اليوم</th><th>الفترة</th>';

    // استعلام SQL لاسترداد أسماء الـ Sections
    $sectionsQuery = "SELECT * FROM sections";
    $sectionsResult = mysqli_query($conn, $sectionsQuery);

    // عرض عناوين الأعمدة للـ Sections في الجدول الزمني
    while ($sectionRow = mysqli_fetch_assoc($sectionsResult)) {
      echo '<th>' . $sectionRow['section_name'] . '</th>';
    }

    echo '</tr>';

    // عرض البيانات في الجدول
    while ($sessionRow = mysqli_fetch_assoc($allSessionsResult)) {
      $day = $sessionRow['day'];
      $sessionName = $sessionRow['session_name'];
      $startTime = $sessionRow['start_time'];
      $endTime = $sessionRow['end_time'];

      echo '<tr>';
      echo '<td>' . $day . '</td>';
      echo '<td>' . $startTime . ' - ' . $endTime . '</td>';

      // استعلام SQL لاسترداد بيانات الـ Sections للفترة واليوم الحاليين
      $sectionsDataQuery = "SELECT sections.section_name, subjects.subject_name, faculty_members.member_name, classrooms.classroom_name
                           FROM timetable
                           INNER JOIN member_courses ON timetable.member_course_id = member_courses.member_course_id
                           INNER JOIN subjects ON member_courses.subject_id = subjects.subject_id
                           INNER JOIN faculty_members ON member_courses.member_id = faculty_members.member_id
                           INNER JOIN classrooms ON timetable.classroom_id = classrooms.classroom_id
                           INNER JOIN sessions ON timetable.session_id = sessions.session_id
                           INNER JOIN sections ON member_courses.section_id = sections.section_id
                           WHERE sessions.day = '$day' AND sessions.session_name = '$sessionName'
                             AND subjects.department_id = " . $_POST['department'] . " AND subjects.level_id = " . $_POST['level'] . "
                           ORDER BY sessions.start_time, sections.section_id"; // ترتيب الجدول الزمني حسب اليوم والفترة

      // تنفيذ الاستعلام والحصول على النتائج
      $sectionsDataResult = mysqli_query($conn, $sectionsDataQuery);

      // مصفوفة لتخزين بيانات الـ Sections المرتبطة بالفترة واليوم الحاليين في الجدول
      $sectionsData = array();

      // وضع بيانات الفترة "sectionsData" تحت كل Section في الجدول
      while ($sectionData = mysqli_fetch_assoc($sectionsDataResult)) {
        $sectionName = $sectionData['section_name'];
        $sectionsData[$sectionName] = $sectionData['subject_name'] . '/' . $sectionData['member_name'] . '/' . $sectionData['classroom_name'];
      }

      // عرض البيانات في الجدول
      $sectionsResult = mysqli_query($conn, $sectionsQuery);
      while ($sectionRow = mysqli_fetch_assoc($sectionsResult)) {
        $sectionName = $sectionRow['section_name'];
        echo '<td>';
        if (isset($sectionsData[$sectionName])) {
          echo $sectionsData[$sectionName];
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
}
?>





</body>
</html>
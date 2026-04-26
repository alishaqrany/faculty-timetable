<?php
$target = "../../subjects/edit_subject.php";
if (isset($_GET['subject_id'])) {
    $target .= "?subject_id=" . urlencode($_GET['subject_id']);
}
header("Location: " . $target, true, 302);
exit();

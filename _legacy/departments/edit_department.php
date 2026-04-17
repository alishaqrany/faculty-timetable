<?php
$target = "../modules/departments/edit.php";
if (isset($_GET['id'])) {
    $target .= "?id=" . urlencode($_GET['id']);
}
header("Location: " . $target);
exit();

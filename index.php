<?php

$target = 'public/';

if (!empty($_SERVER['QUERY_STRING'])) {
    $target .= '?' . $_SERVER['QUERY_STRING'];
}

if (!headers_sent()) {
    header('Location: ' . $target, true, 302);
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="0;url=public/">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فتح النظام</title>
</head>
<body>
    <p><a href="public/">فتح النظام</a></p>
</body>
</html>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($__page_title ?? 'تسجيل الدخول') ?> | <?= e(config('app.name', 'نظام الجداول')) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { font-family: 'Tajawal', sans-serif !important; }
        .login-page { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .login-box { width: 400px; }
        .login-logo b { color: #fff; font-weight: 700; }
        .card { border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,.2); }
    </style>
</head>
<body class="hold-transition login-page" dir="rtl">

<div class="login-box">
    <div class="login-logo">
        <b><?= e(config('app.name', 'نظام الجداول')) ?></b>
    </div>

    <?php $this->yield('content'); ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

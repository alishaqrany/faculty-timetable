<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($__page_title ?? 'الرئيسية') ?> | <?= e(config('app.name', 'نظام الجداول')) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Changa:wght@500;700&family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="<?= asset('assets/favicon.svg') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/custom.css') ?>">
    <?php $this->yield('styles'); ?>
</head>
<body class="public-home-page" dir="rtl">
    <?php $this->yield('content'); ?>
</body>
</html>
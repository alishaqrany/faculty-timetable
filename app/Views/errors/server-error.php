<?php
$this->layout('layouts.public');
$__page_title = 'خطأ داخلي';

$this->include('errors.card', [
    'tone' => 'rose',
    'statusCode' => (int) ($statusCode ?? 500),
    'badgeIcon' => 'fa-triangle-exclamation',
    'pageHeading' => 'حدث خطأ داخلي غير متوقع',
    'heroText' => 'تعذر إكمال الطلب حالياً، وتم إخفاء التفاصيل التقنية عن واجهة المستخدم للحفاظ على تجربة أكثر وضوحاً وأماناً.',
    'panelTitle' => 'كيف تتابع الآن؟',
    'panelText' => 'يمكنك إعادة المحاولة، وإذا استمرت المشكلة فراجع السجلات على الخادم باستخدام مرجع الخطأ الظاهر أدناه أو تواصل مع مسؤول النظام.',
    'bullets' => [
        'أعد تحميل الصفحة أو كرر العملية بعد لحظات.',
        'إذا كان الخطأ مرتبطاً بإجراء إداري، جرّبه من مسار آخر داخل النظام.',
        'استخدم مرجع الخطأ لمطابقته مع السجل الفني على الخادم عند المراجعة.',
    ],
    'actions' => [
        [
            'url' => $retryUrl ?? url('/'),
            'label' => 'إعادة المحاولة',
            'icon' => 'fa-rotate-right',
            'variant' => 'primary',
        ],
        [
            'url' => $homeUrl ?? url('/'),
            'label' => 'العودة للرئيسية',
            'icon' => 'fa-house',
            'variant' => 'secondary',
        ],
    ],
    'note' => 'تم تسجيل تفاصيل الخطأ داخلياً على الخادم لمتابعتها دون عرض التفاصيل الحساسة للمستخدم.',
    'errorReference' => $errorReference ?? null,
]);
?>
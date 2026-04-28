<?php
$this->layout('layouts.public');
$__page_title = 'خطأ داخلي';
?>

<?php $this->section('styles'); ?>
<style>
    .server-error-wrap {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }

    .server-error-card {
        width: min(100%, 760px);
        padding: 2.5rem 2rem;
        background: rgba(255, 255, 255, 0.94);
        border-radius: 30px;
        border: 1px solid rgba(25, 52, 61, 0.10);
        box-shadow: 0 24px 60px rgba(43, 31, 16, 0.10);
        text-align: center;
    }

    .server-error-icon {
        width: 5rem;
        height: 5rem;
        margin: 0 auto 1rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(217, 119, 6, 0.10);
        color: #d97706;
        font-size: 1.8rem;
    }

    .server-error-card h1 {
        font-family: 'Changa', sans-serif;
        margin-bottom: 0.8rem;
    }

    .server-error-card p {
        color: #627475;
        line-height: 1.9;
        margin-bottom: 0;
    }

    .server-error-actions {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 0.8rem;
        margin-top: 1.6rem;
    }

    .server-error-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        padding: 0.9rem 1.3rem;
        border-radius: 999px;
        font-weight: 700;
        text-decoration: none !important;
    }

    .server-error-btn-primary {
        color: #fff !important;
        background: linear-gradient(135deg, #155e75, #0f766e);
    }

    .server-error-btn-secondary {
        color: #19343d !important;
        background: #f8f4ee;
        border: 1px solid rgba(25, 52, 61, 0.12);
    }

    @media (max-width: 575.98px) {
        .server-error-card {
            padding: 1.5rem 1.25rem;
        }

        .server-error-actions {
            flex-direction: column;
        }
    }
</style>
<?php $this->endSection(); ?>

<div class="server-error-wrap">
    <div class="server-error-card">
        <div class="server-error-icon">
            <i class="fas fa-triangle-exclamation"></i>
        </div>
        <h1>حدث خطأ داخلي غير متوقع</h1>
        <p>تعذر إكمال الطلب حالياً، وتم إخفاء التفاصيل التقنية عن واجهة المستخدم. يمكن إعادة المحاولة، وإذا استمرت المشكلة فراجِع السجلات على الخادم أو تواصل مع مسؤول النظام.</p>

        <div class="server-error-actions">
            <a href="<?= e($retryUrl ?? url('/')) ?>" class="server-error-btn server-error-btn-primary">
                <i class="fas fa-rotate-right"></i>
                <span>إعادة المحاولة</span>
            </a>
            <a href="<?= e($homeUrl ?? url('/')) ?>" class="server-error-btn server-error-btn-secondary">
                <i class="fas fa-house"></i>
                <span>العودة للرئيسية</span>
            </a>
        </div>
    </div>
</div>
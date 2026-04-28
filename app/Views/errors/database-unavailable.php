<?php
$this->layout('layouts.public');
$__page_title = 'تعذر تشغيل النظام';
?>

<?php $this->section('styles'); ?>
<style>
    .system-error-wrap {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }

    .system-error-card {
        width: min(100%, 900px);
        background: rgba(255, 255, 255, 0.94);
        border: 1px solid rgba(25, 52, 61, 0.10);
        border-radius: 32px;
        box-shadow: 0 28px 70px rgba(43, 31, 16, 0.12);
        overflow: hidden;
    }

    .system-error-grid {
        display: grid;
        grid-template-columns: minmax(0, 0.95fr) minmax(0, 1.05fr);
    }

    .system-error-side {
        padding: 2.4rem 2rem;
        background:
            radial-gradient(circle at top right, rgba(15, 118, 110, 0.18), transparent 35%),
            linear-gradient(165deg, #184149 0%, #0f766e 100%);
        color: #fff;
    }

    .system-error-code {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.5rem 0.95rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        font-weight: 700;
        margin-bottom: 1.1rem;
    }

    .system-error-side h1 {
        font-family: 'Changa', sans-serif;
        font-size: clamp(2rem, 4vw, 3rem);
        line-height: 1.15;
        margin-bottom: 1rem;
    }

    .system-error-side p {
        margin: 0;
        line-height: 1.9;
        color: rgba(255, 255, 255, 0.88);
    }

    .system-error-body {
        padding: 2.4rem 2rem;
    }

    .system-error-body h2 {
        font-family: 'Changa', sans-serif;
        margin-bottom: 0.85rem;
    }

    .system-error-body p,
    .system-error-body li {
        color: #627475;
        line-height: 1.9;
    }

    .system-error-list {
        margin: 1.25rem 0 0;
        padding-right: 1.1rem;
    }

    .system-error-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.8rem;
        margin-top: 1.6rem;
    }

    .system-error-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        padding: 0.9rem 1.3rem;
        border-radius: 999px;
        font-weight: 700;
        text-decoration: none !important;
    }

    .system-error-btn-primary {
        background: linear-gradient(135deg, #155e75, #0f766e);
        color: #fff !important;
    }

    .system-error-btn-secondary {
        background: #f8f4ee;
        color: #19343d !important;
        border: 1px solid rgba(25, 52, 61, 0.12);
    }

    .system-error-note {
        margin-top: 1.4rem;
        padding: 1rem 1.1rem;
        border-radius: 20px;
        background: rgba(217, 119, 6, 0.08);
        color: #9a5c0b;
        border: 1px solid rgba(217, 119, 6, 0.16);
    }

    @media (max-width: 767.98px) {
        .system-error-grid {
            grid-template-columns: 1fr;
        }

        .system-error-side,
        .system-error-body {
            padding: 1.5rem;
        }

        .system-error-actions {
            flex-direction: column;
        }
    }
</style>
<?php $this->endSection(); ?>

<div class="system-error-wrap">
    <div class="system-error-card">
        <div class="system-error-grid">
            <section class="system-error-side">
                <div class="system-error-code">
                    <i class="fas fa-database"></i>
                    <span>الحالة <?= (int) ($statusCode ?? 503) ?></span>
                </div>
                <h1>تعذر تشغيل النظام حالياً</h1>
                <p>تم اعتراض الخطأ التقني ومنع عرضه بشكل خام. المشكلة الحالية مرتبطة بعدم القدرة على الوصول إلى قاعدة البيانات المطلوبة لتشغيل النظام.</p>
            </section>

            <section class="system-error-body">
                <h2>ما الذي يمكن مراجعته؟</h2>
                <p>غالباً تكون المشكلة في بيانات الاتصال بقاعدة البيانات أو في تشغيل خدمة MySQL نفسها. تم استبدال رسالة PHP الخام بهذه الصفحة حتى يظهر العطل بصورة احترافية وواضحة.</p>

                <ul class="system-error-list">
                    <li>تحقق من اسم المضيف واسم المستخدم وكلمة المرور واسم قاعدة البيانات في إعدادات الاتصال.</li>
                    <li>تأكد من أن خدمة MySQL تعمل على الخادم وأن المستخدم لديه الصلاحيات المطلوبة.</li>
                    <li>إذا كان النظام في أول تشغيل، يمكن فتح معالج الإعداد لمراجعة البيانات أو إعادة ضبطها.</li>
                </ul>

                <div class="system-error-actions">
                    <a href="<?= e($retryUrl ?? url('/')) ?>" class="system-error-btn system-error-btn-primary">
                        <i class="fas fa-rotate-right"></i>
                        <span>إعادة المحاولة</span>
                    </a>
                    <a href="<?= e($installUrl ?? url('/install.php')) ?>" class="system-error-btn system-error-btn-secondary">
                        <i class="fas fa-sliders"></i>
                        <span>فتح الإعداد</span>
                    </a>
                    <a href="<?= e($homeUrl ?? url('/')) ?>" class="system-error-btn system-error-btn-secondary">
                        <i class="fas fa-house"></i>
                        <span>العودة للرئيسية</span>
                    </a>
                </div>

                <div class="system-error-note">
                    إذا استمرت المشكلة بعد تحديث الإعدادات، راجع ملف إعداد قاعدة البيانات على الخادم أو تواصل مع مسؤول الاستضافة.
                </div>
            </section>
        </div>
    </div>
</div>
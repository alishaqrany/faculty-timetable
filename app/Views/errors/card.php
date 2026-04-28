<?php
$tone = $tone ?? 'teal';
$tones = [
    'teal' => [
        'gradient' => 'radial-gradient(circle at top right, rgba(15, 118, 110, 0.18), transparent 35%), linear-gradient(165deg, #184149 0%, #0f766e 100%)',
        'accentBg' => 'rgba(15, 118, 110, 0.10)',
        'accentBorder' => 'rgba(15, 118, 110, 0.16)',
        'accentColor' => '#0f766e',
        'noteBg' => 'rgba(15, 118, 110, 0.08)',
        'noteBorder' => 'rgba(15, 118, 110, 0.16)',
        'noteColor' => '#155e75',
    ],
    'rose' => [
        'gradient' => 'radial-gradient(circle at top right, rgba(225, 29, 72, 0.20), transparent 35%), linear-gradient(165deg, #4c1d2a 0%, #9f1239 100%)',
        'accentBg' => 'rgba(225, 29, 72, 0.10)',
        'accentBorder' => 'rgba(225, 29, 72, 0.16)',
        'accentColor' => '#be123c',
        'noteBg' => 'rgba(190, 24, 93, 0.08)',
        'noteBorder' => 'rgba(190, 24, 93, 0.16)',
        'noteColor' => '#9f1239',
    ],
    'amber' => [
        'gradient' => 'radial-gradient(circle at top right, rgba(217, 119, 6, 0.18), transparent 35%), linear-gradient(165deg, #6b3d05 0%, #b45309 100%)',
        'accentBg' => 'rgba(217, 119, 6, 0.10)',
        'accentBorder' => 'rgba(217, 119, 6, 0.16)',
        'accentColor' => '#b45309',
        'noteBg' => 'rgba(217, 119, 6, 0.08)',
        'noteBorder' => 'rgba(217, 119, 6, 0.16)',
        'noteColor' => '#9a5c0b',
    ],
    'slate' => [
        'gradient' => 'radial-gradient(circle at top right, rgba(71, 85, 105, 0.18), transparent 35%), linear-gradient(165deg, #1e293b 0%, #334155 100%)',
        'accentBg' => 'rgba(71, 85, 105, 0.10)',
        'accentBorder' => 'rgba(71, 85, 105, 0.16)',
        'accentColor' => '#334155',
        'noteBg' => 'rgba(71, 85, 105, 0.08)',
        'noteBorder' => 'rgba(71, 85, 105, 0.16)',
        'noteColor' => '#334155',
    ],
];
$palette = $tones[$tone] ?? $tones['teal'];
$actions = $actions ?? [];
$bullets = $bullets ?? [];
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
        width: min(100%, 940px);
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
        background: <?= $palette['gradient'] ?>;
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

    .system-error-note,
    .system-error-reference {
        margin-top: 1.25rem;
        padding: 1rem 1.1rem;
        border-radius: 20px;
        border: 1px solid;
    }

    .system-error-note {
        background: <?= $palette['noteBg'] ?>;
        color: <?= $palette['noteColor'] ?>;
        border-color: <?= $palette['noteBorder'] ?>;
    }

    .system-error-reference {
        background: <?= $palette['accentBg'] ?>;
        border-color: <?= $palette['accentBorder'] ?>;
        color: <?= $palette['accentColor'] ?>;
    }

    .system-error-reference code {
        direction: ltr;
        display: inline-block;
        font-weight: 700;
        color: inherit;
        background: transparent;
        padding: 0;
        font-size: 0.98rem;
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
                    <i class="fas <?= e($badgeIcon ?? 'fa-circle-exclamation') ?>"></i>
                    <span>الحالة <?= (int) ($statusCode ?? 500) ?></span>
                </div>
                <h1><?= e($pageHeading ?? 'حدث خطأ غير متوقع') ?></h1>
                <p><?= e($heroText ?? 'تعذر إكمال الطلب حالياً.') ?></p>
            </section>

            <section class="system-error-body">
                <h2><?= e($panelTitle ?? 'تفاصيل الحالة') ?></h2>
                <p><?= e($panelText ?? '') ?></p>

                <?php if (!empty($bullets)): ?>
                    <ul class="system-error-list">
                        <?php foreach ($bullets as $bullet): ?>
                            <li><?= e($bullet) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (!empty($actions)): ?>
                    <div class="system-error-actions">
                        <?php foreach ($actions as $action): ?>
                            <?php $variantClass = ($action['variant'] ?? 'secondary') === 'primary' ? 'system-error-btn-primary' : 'system-error-btn-secondary'; ?>
                            <a href="<?= e($action['url'] ?? url('/')) ?>" class="system-error-btn <?= $variantClass ?>">
                                <i class="fas <?= e($action['icon'] ?? 'fa-arrow-left') ?>"></i>
                                <span><?= e($action['label'] ?? 'متابعة') ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errorReference)): ?>
                    <div class="system-error-reference">
                        مرجع المتابعة: <code><?= e($errorReference) ?></code><br>
                        استخدم هذا المرجع لمطابقة الحالة مع السجلات الفنية على الخادم.
                    </div>
                <?php endif; ?>

                <?php if (!empty($note)): ?>
                    <div class="system-error-note">
                        <?= e($note) ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</div>
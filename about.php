<?php require_once __DIR__ . '/includes/header.php'; ?>

<main class="page">
    <div class="container page-header">
        <div>
            <p class="eyebrow">عن النظام</p>
            <h1><?= $about['headline']; ?></h1>
        </div>
        <p><?= $about['description']; ?></p>
    </div>

    <section id="contact" class="section alt">
        <div class="container detail">
            <div>
                <p class="eyebrow">تواصل معنا</p>
                <h3>معلومات الاتصال</h3>
                <ul class="bullets">
                    <li>هاتف: <?= $about['phone']; ?></li>
                    <li>البريد: <?= $about['email']; ?></li>
                    <li>العنوان: <?= $about['address']; ?></li>
                </ul>
                <?php $waPhone = preg_replace('/\D+/', '', $about['phone']); ?>
                <a class="btn solid" href="https://wa.me/<?= $waPhone; ?>" target="_blank" rel="noopener">راسلنا عبر الواتساب</a>
            </div>
           
    
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>



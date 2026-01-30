<?php require_once __DIR__ . '/includes/header.php'; ?>
<?php
$serviceCats = $pdo->query("SELECT id, name, slug, description FROM service_categories ORDER BY id DESC LIMIT 4")->fetchAll();
$productCats = $pdo->query("SELECT id, name, slug FROM product_categories ORDER BY id DESC LIMIT 4")->fetchAll();

$serviceItemsByCat = [];
foreach ($serviceCats as $cat) {
    $stmt = $pdo->prepare("SELECT name FROM service_items WHERE category_id = ? ORDER BY id DESC LIMIT 2");
    $stmt->execute([$cat['id']]);
    $serviceItemsByCat[$cat['id']] = $stmt->fetchAll();
}
$productItemsByCat = [];
foreach ($productCats as $cat) {
    $stmt = $pdo->prepare("SELECT name FROM products WHERE category_id = ? ORDER BY id DESC LIMIT 2");
    $stmt->execute([$cat['id']]);
    $productItemsByCat[$cat['id']] = $stmt->fetchAll();
}
// جلب البنرات من قاعدة البيانات (مع تحمل الأخطاء)
try {
    $banners = $pdo->query("SELECT id, title, badge, price_current, price_old, description, image, link FROM banners WHERE COALESCE(active,1)=1 ORDER BY sort_order ASC, id DESC")->fetchAll();
} catch (Exception $e) {
    $banners = [];
}
?>

<main>
    <section class="hero">
        <div class="container hero-grid">
            <div>
                <p class="eyebrow">نظام نقاط بيع متكامل</p>
                <h1>حوّل إدارة مبيعاتك إلى تجربة سلسة وآمنة</h1>
                <p class="lead">حلول أجهزة وبرمجيات مع دعم فني ولوحة تحكم إدارية مرنة.</p>
                <div class="hero-actions">
                    <a class="btn solid" href="#services">استكشف الخدمات</a>
                    <a class="btn ghost" href="products.php">تصفح المنتجات</a>
                </div>
            </div>
            <div class="hero-card">
                <h3>أبرز المزايا</h3>
                <ul>
                    <li>تركيب وتشغيل سريع للموقع والخدمات</li>
                    <li>تدريب ودعم فني مستمر</li>
                    <li>ضمان وتحديثات دورية</li>
                    <li>باقات وخطط أسعار مرنة</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="promo-strip">
        <div class="marquee" aria-label="عروض وترويجات">
            <button type="button" class="arrow right" aria-label="التالي">›</button>
            <button type="button" class="arrow left" aria-label="السابق">‹</button>
            <div class="track">
                <?php if (!empty($banners)): ?>
                    <?php foreach ($banners as $b): ?>
                        <a class="promo-card" href="<?= htmlspecialchars($b['link'] ?: '#'); ?>">
                            <img src="<?= htmlspecialchars($b['image'] ?: 'https://picsum.photos/seed/fallback/800/450'); ?>" alt="<?= htmlspecialchars($b['title'] ?: 'عرض'); ?>">
                            <div class="overlay">
                                <div class="content">
                                    <div class="row">
                                        <?php if (!empty($b['badge'])): ?><span class="badge"><?= htmlspecialchars($b['badge']); ?></span><?php endif; ?>
                                        <span class="price">
                                            <?php if (!empty($b['price_current'])): ?><span class="current"><?= htmlspecialchars($b['price_current']); ?></span><?php endif; ?>
                                            <?php if (!empty($b['price_old'])): ?><span class="old"><?= htmlspecialchars($b['price_old']); ?></span><?php endif; ?>
                                        </span>
                                    </div>
                                    <h4 class="title"><?= htmlspecialchars($b['title'] ?: 'عرض'); ?></h4>
                                    <?php if (!empty($b['description'])): ?><p class="desc"><?= htmlspecialchars($b['description']); ?></p><?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <a class="promo-card" href="#">
                        <img src="https://picsum.photos/seed/pos_offer1/800/450" alt="عرض الباقة الأساسية">
                        <div class="overlay">
                            <div class="content">
                                <div class="row">
                                    <span class="badge">خصم 20%</span>
                                    <span class="price"><span class="current">799 ر.س</span><span class="old">999</span></span>
                                </div>
                                <h4 class="title">الباقة الأساسية</h4>
                                <p class="desc">مثالية للمتاجر الصغيرة مع أهم الميزات.</p>
                            </div>
                        </div>
                    </a>
                    <a class="promo-card" href="#">
                        <img src="https://picsum.photos/seed/pos_offer2/800/450" alt="أجهزة + نظام">
                        <div class="overlay">
                            <div class="content">
                                <div class="row">
                                    <span class="badge">خصم 30%</span>
                                    <span class="price"><span class="current">1,699 ر.س</span><span class="old">2,399</span></span>
                                </div>
                                <h4 class="title">حزمة أجهزة + نظام</h4>
                                <p class="desc">كل ما تحتاجه للبدء فورًا.</p>
                            </div>
                        </div>
                    </a>
                    <a class="promo-card" href="#">
                        <img src="https://picsum.photos/seed/pos_offer3/800/450" alt="اشتراك سنوي">
                        <div class="overlay">
                            <div class="content">
                                <div class="row">
                                    <span class="badge">وفر أكثر</span>
                                    <span class="price"><span class="current">2,999 ر.س</span><span class="old">3,499</span></span>
                                </div>
                                <h4 class="title">اشتراك سنوي</h4>
                                <p class="desc">يشمل التحديثات والدعم طوال العام.</p>
                            </div>
                        </div>
                    </a>
                <?php endif; ?>
            </div>
            <div class="indicators" aria-hidden="true"></div>
        </div>
    </section>

    <section id="services" class="section">
        <div class="container section-header">
            <div>
                <p class="eyebrow">خدماتنا</p>
                <h2>حلول تغطي كامل دورة العمل</h2>
            </div>
            <a class="link" href="services.php">المزيد</a>
        </div>
        <div class="container cards-grid">
            <?php foreach ($serviceCats as $service): ?>
                <article class="card">
                    <h3><?= $service['name']; ?></h3>
                    <p><?= $service['description']; ?></p>
                    <a class="link" href="services.php?category=<?= $service['slug']; ?>">عرض التصنيف</a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="products" class="section alt">
        <div class="container section-header">
            <div>
                <p class="eyebrow">منتجاتنا</p>
                <h2>أجهزة وبرمجيات متوافقة</h2>
            </div>
            <a class="link" href="products.php">المزيد</a>
        </div>
        <div class="container cards-grid">
            <?php foreach ($productCats as $product): ?>
                <article class="card">
                    <h3><?= $product['name']; ?></h3>
                    <?php if (!empty($productItemsByCat[$product['id']])): ?>
                        <p>أبرز العناصر داخل التصنيف:</p>
                        <ul class="bullets">
                            <?php foreach ($productItemsByCat[$product['id']] as $item): ?>
                                <li><?= $item['name']; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <a class="link" href="products.php?category=<?= $product['slug']; ?>">عرض التصنيف</a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>



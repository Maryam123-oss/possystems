<?php require_once __DIR__ . '/includes/header.php'; ?>
<?php
$selectedCategory = $_GET['category'] ?? null;
$selectedItemId = isset($_GET['item']) ? (int) $_GET['item'] : null;

$categories = $pdo->query("SELECT id, name, slug, description FROM service_categories ORDER BY id DESC")->fetchAll();
$categoryData = null;
$items = [];

if ($selectedCategory) {
    $stmt = $pdo->prepare("SELECT * FROM service_categories WHERE slug = ?");
    $stmt->execute([$selectedCategory]);
    $categoryData = $stmt->fetch();
    if ($categoryData) {
        $itemsStmt = $pdo->prepare("SELECT * FROM service_items WHERE category_id = ? ORDER BY id DESC");
        $itemsStmt->execute([$categoryData['id']]);
        $items = $itemsStmt->fetchAll();
    }
}

$detail = null;
if ($categoryData && $selectedItemId) {
    $detailStmt = $pdo->prepare("SELECT * FROM service_items WHERE id = ? AND category_id = ?");
    $detailStmt->execute([$selectedItemId, $categoryData['id']]);
    $detail = $detailStmt->fetch();
    if ($detail && $detail['features']) {
        $decoded = json_decode($detail['features'], true);
        $detail['features'] = is_array($decoded) ? $decoded : [];
    } elseif ($detail) {
        $detail['features'] = [];
    }
}
?>

<main class="page">
    <div class="container page-header">
        <div>
            <p class="eyebrow">الخدمات</p>
            <h1>تصنيفات الخدمات</h1>
        </div>
    </div>

    <div class="container cards-grid">
        <?php foreach ($categories as $service): ?>
            <article class="card <?= $selectedCategory === $service['slug'] ? 'active' : ''; ?>">
                <h3><?= $service['name']; ?></h3>
                <p><?= $service['description']; ?></p>
                <a class="link" href="?category=<?= $service['slug']; ?>">عرض العناصر</a>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if ($categoryData): ?>
        <section class="section">
            <div class="container section-header">
                <h2>عناصر تصنيف: <?= $categoryData['name']; ?></h2>
            </div>
            <div class="container cards-grid">
                <?php foreach ($items as $item): ?>
                    <article class="card">
                        <h4><?= $item['name']; ?></h4>
                        <p><?= $item['details']; ?></p>
                        <a class="link" href="?category=<?= $categoryData['slug']; ?>&item=<?= $item['id']; ?>">تفاصيل الخدمة</a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($detail): ?>
        <section class="section alt">
            <div class="container detail">
                <div>
                    <p class="eyebrow">تفاصيل الخدمة</p>
                    <h3><?= $detail['name']; ?></h3>
                    <p><?= $detail['details']; ?></p>
                    <?php if (!empty($detail['features'])): ?>
                        <h5>المميزات:</h5>
                        <ul class="bullets">
                            <?php foreach ($detail['features'] as $feature): ?>
                                <li><?= $feature; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <a class="btn solid" href="about.php#contact">اطلب الخدمة</a>
                </div>
                <div class="detail-media placeholder">
                    <?= $detail['image'] ? '<img src="'.htmlspecialchars($detail['image']).'" alt="" style="max-width:100%;">' : 'صورة الخدمة'; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>



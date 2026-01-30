<?php require_once __DIR__ . '/includes/header.php'; ?>
<?php
$selectedCategory = $_GET['category'] ?? null;
$selectedItemId = isset($_GET['item']) ? (int) $_GET['item'] : null;

$categories = $pdo->query("SELECT id, name, slug FROM product_categories ORDER BY id DESC")->fetchAll();
$categoryData = null;
$items = [];

if ($selectedCategory) {
    $stmt = $pdo->prepare("SELECT * FROM product_categories WHERE slug = ?");
    $stmt->execute([$selectedCategory]);
    $categoryData = $stmt->fetch();
    if ($categoryData) {
        $itemsStmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? ORDER BY id DESC");
        $itemsStmt->execute([$categoryData['id']]);
        $items = $itemsStmt->fetchAll();
    }
}

$detail = null;
if ($categoryData && $selectedItemId) {
    $detailStmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND category_id = ?");
    $detailStmt->execute([$selectedItemId, $categoryData['id']]);
    $detail = $detailStmt->fetch();
    if ($detail && $detail['specs']) {
        $decoded = json_decode($detail['specs'], true);
        $detail['specs'] = is_array($decoded) ? $decoded : [];
    } elseif ($detail) {
        $detail['specs'] = [];
    }
}
?>

<main class="page">
    <div class="container page-header">
        <div>
            <p class="eyebrow">المنتجات</p>
            <h1>تصنيفات المنتجات</h1>
        </div>
    </div>

    <div class="container cards-grid">
        <?php foreach ($categories as $product): ?>
            <article class="card <?= $selectedCategory === $product['slug'] ? 'active' : ''; ?>">
                <h3><?= $product['name']; ?></h3>
                <a class="link" href="?category=<?= $product['slug']; ?>">عرض العناصر</a>
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
                        <p class="price">السعر: <?= $item['price']; ?></p>
                        <a class="link" href="?category=<?= $categoryData['slug']; ?>&item=<?= $item['id']; ?>">تفاصيل المنتج</a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($detail): ?>
        <section class="section alt">
            <div class="container detail">
                <div>
                    <p class="eyebrow">تفاصيل المنتج</p>
                    <h3><?= $detail['name']; ?></h3>
                    <p><?= $detail['details']; ?></p>
                    <?php if (!empty($detail['specs'])): ?>
                        <h5>الخصائص:</h5>
                        <ul class="bullets">
                            <?php foreach ($detail['specs'] as $spec): ?>
                                <li><?= $spec; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <p class="price">السعر: <?= $detail['price']; ?></p>
                    <a class="btn solid" href="about.php#contact">اطلب الآن</a>
                </div>
                <div class="detail-media placeholder">
                    <?= $detail['image'] ? '<img src="'.htmlspecialchars($detail['image']).'" alt="" style="max-width:100%;">' : 'صورة المنتج'; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>



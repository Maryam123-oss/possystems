<?php require_once __DIR__ . '/includes/header.php'; ?>
<?php
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND COALESCE(status,'published') = 'published'");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: news.php');
    exit;
}
?>

<main class="page">
    <div class="container page-header">
        <div>
            <p class="eyebrow"><?= $post['published_at'] ?? date('Y-m-d'); ?></p>
            <h1><?= htmlspecialchars($post['title']); ?></h1>
        </div>
        <a class="link" href="news.php">← العودة للأخبار</a>
    </div>

    <article class="container detail">
        <?php if ($post['image']): ?>
            <div class="detail-media">
                <img src="<?= htmlspecialchars($post['image']); ?>" alt="<?= htmlspecialchars($post['title']); ?>">
            </div>
        <?php endif; ?>
        <div>
            <?php if ($post['excerpt']): ?>
                <p class="lead"><?= nl2br(htmlspecialchars($post['excerpt'])); ?></p>
            <?php endif; ?>
            <div class="content">
                <?= nl2br(htmlspecialchars($post['body'] ?? $post['excerpt'] ?? '')); ?>
            </div>
        </div>
    </article>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>


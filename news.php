<?php require_once __DIR__ . '/includes/header.php'; ?>
<?php
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 6;
$offset = ($page - 1) * $perPage;
$total = $pdo->query("SELECT COUNT(*) AS c FROM posts WHERE COALESCE(status,'published') = 'published'")->fetch()['c'] ?? 0;
$pages = max(1, ceil($total / $perPage));
$stmt = $pdo->prepare("SELECT * FROM posts WHERE COALESCE(status,'published') = 'published' ORDER BY published_at DESC, id DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();
?>

<main class="page">
    <div class="container page-header">
        <div>
            <p class="eyebrow">الأخبار / المدونة</p>
            <h1>آخر المقالات والتحديثات</h1>
        </div>
        <p>نظام ترقيم صفحات بسيط للعرض.</p>
    </div>

    <div class="container cards-grid">
        <?php foreach ($posts as $post): ?>
            <article class="card">
                <p class="eyebrow"><?= $post['published_at']; ?></p>
                <h3><?= $post['title']; ?></h3>
                <p><?= $post['excerpt']; ?></p>
                <a class="link" href="post.php?id=<?= $post['id']; ?>">قراءة المزيد</a>
            </article>
        <?php endforeach; ?>
        <?php if (empty($posts)): ?>
            <p>لا توجد مقالات حالياً.</p>
        <?php endif; ?>
    </div>

    <div class="container pagination">
        <a class="page-btn <?= $page <= 1 ? 'disabled' : ''; ?>" href="?page=<?= max(1, $page-1); ?>">السابق</a>
        <span class="page-indicator"><?= $page; ?> / <?= $pages; ?></span>
        <a class="page-btn <?= $page >= $pages ? 'disabled' : ''; ?>" href="?page=<?= min($pages, $page+1); ?>">التالي</a>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>



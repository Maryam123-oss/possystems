<?php
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}
$role = $_SESSION['user']['role'];
// فقط الأدمن والطاقم يمكنهم الوصول للوحة التحكم
if ($role !== 'admin' && $role !== 'staff') {
    header('Location: ../index.php');
    exit;
}
$serviceCount = $pdo->query("SELECT COUNT(*) AS c FROM service_categories")->fetch()['c'] ?? 0;
$productCount = $pdo->query("SELECT COUNT(*) AS c FROM product_categories")->fetch()['c'] ?? 0;
$postsCount = $pdo->query("SELECT COUNT(*) AS c FROM posts")->fetch()['c'] ?? 0;
$usersCount = $pdo->query("SELECT COUNT(*) AS c FROM users")->fetch()['c'] ?? 0;
try { $bannersCount = $pdo->query("SELECT COUNT(*) AS c FROM banners")->fetch()['c'] ?? 0; } catch (Exception $e) { $bannersCount = 0; }
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - POS Systems</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard">
    <header class="dashboard-topbar">
        <div class="container dash-inner">
            <div>
                <strong>لوحة التحكم</strong>
                <span class="badge"><?= $role === 'admin' ? 'مدير' : 'طاقم'; ?></span>
            </div>
            <div class="dash-actions">
                <a class="link" href="../index.php">العودة للموقع</a>
                <a class="btn ghost" href="../logout.php">تسجيل الخروج</a>
            </div>
        </div>
    </header>
    <main class="container dash-grid">
        <aside class="dash-nav">
            <a href="index.php" class="active">نظرة عامة</a>
            <a href="services.php">إدارة الخدمات</a>
            <a href="products.php">إدارة المنتجات</a>
            <a href="news.php">إدارة المقالات</a>
            <a href="banners.php">إدارة البنرات</a>
            <a href="users.php">إدارة المستخدمين</a>
            <a href="settings.php">الإعدادات</a>
        </aside>
        <section class="dash-content">
            <h1>مرحباً، <?= htmlspecialchars($_SESSION['user']['name'] ?? $_SESSION['user']['email']); ?></h1>
            <p>لوحة التحكم الرئيسية - إدارة المحتوى والخدمات.</p>
            <div class="cards-grid">
                <div class="card">
                    <p class="eyebrow">الخدمات</p>
                    <h3><?= $serviceCount; ?> تصنيف</h3>
                    <a class="link" href="services.php">إدارة</a>
                </div>
                <div class="card">
                    <p class="eyebrow">المنتجات</p>
                    <h3><?= $productCount; ?> تصنيف</h3>
                    <a class="link" href="products.php">إدارة</a>
                </div>
                <div class="card">
                    <p class="eyebrow">المقالات</p>
                    <h3><?= $postsCount; ?> مقال</h3>
                    <a class="link" href="news.php">إدارة</a>
                </div>
                <div class="card">
                    <p class="eyebrow">البنرات</p>
                    <h3><?= $bannersCount; ?> بنر</h3>
                    <a class="link" href="banners.php">إدارة</a>
                </div>
                <?php if ($role === 'admin'): ?>
                <div class="card">
                    <p class="eyebrow">المستخدمون</p>
                    <h3><?= $usersCount; ?> حساب</h3>
                    <a class="link" href="users.php">إدارة</a>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>



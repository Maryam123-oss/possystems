<?php require_once __DIR__ . '/config.php'; ?>
<?php require_once __DIR__ . '/data.php'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Systems</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="navbar">
        <div class="container nav-inner">
            <div class="logo"><img src="FullLogo_Transparent.png" alt="POS Systems"></div>
            <?php
                $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
                $base = basename($uriPath);
                if ($base === '' || $base === false) {
                    $base = basename($_SERVER['SCRIPT_NAME'] ?? ($_SERVER['PHP_SELF'] ?? ''));
                }
                $isHome = ($base === '' || $base === 'index.php');
                $isServices = ($base === 'services.php');
                $isProducts = ($base === 'products.php');
                $isNews = ($base === 'news.php' || $base === 'post.php');
                $isAbout = ($base === 'about.php');
            ?>
            <nav>
                <a href="index.php" class="<?= $isHome ? 'active' : '' ?>">الرئيسية</a>
                <a href="services.php" class="<?= $isServices ? 'active' : '' ?>">خدماتنا</a>
                <a href="products.php" class="<?= $isProducts ? 'active' : '' ?>">منتجاتنا</a>
                <a href="news.php" class="<?= $isNews ? 'active' : '' ?>">الأخبار</a>
                <a href="about.php" class="<?= $isAbout ? 'active' : '' ?>">عن النظام</a>
            </nav>
            <div class="nav-actions">
                <?php if (isset($_SESSION['user'])): ?>
                    <div class="user-profile">
                        <?php
                            $displayName = $_SESSION['user']['name'] ?? ($_SESSION['user']['email'] ?? '');
                            $initial = function($s) {
                                if (function_exists('mb_substr')) return mb_substr($s, 0, 1, 'UTF-8');
                                return substr($s, 0, 1);
                            };
                            $avatar = $_SESSION['user']['avatar'] ?? '';
                        ?>
                        <div class="avatar" title="<?= htmlspecialchars($displayName); ?>">
                            <?php if (!empty($avatar)): ?>
                                <img src="<?= htmlspecialchars($avatar); ?>" alt="<?= htmlspecialchars($displayName); ?>">
                            <?php else: ?>
                                <span><?= htmlspecialchars(strtoupper($initial($displayName))); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (($_SESSION['user']['role'] ?? 'user') === 'admin' || ($_SESSION['user']['role'] ?? 'user') === 'staff'): ?>
                            <a class="btn ghost" href="dashboard/index.php">لوحة التحكم</a>
                        <?php endif; ?>
                        <a class="btn ghost" href="logout.php">تسجيل الخروج</a>
                    </div>
                <?php else: ?>
                    <a class="btn ghost" href="login.php">تسجيل الدخول</a>
                    <a class="btn solid" href="register.php">إنشاء حساب</a>
                <?php endif; ?>
            </div>
        </div>
    </header>



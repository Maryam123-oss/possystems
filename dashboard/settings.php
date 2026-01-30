<?php
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}
$role = $_SESSION['user']['role'] ?? 'user';
if ($role !== 'admin' && $role !== 'staff') {
    header('Location: ../index.php');
    exit;
}

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_admin()) {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'update_settings') {
            $settings = [
                'about_headline' => trim($_POST['about_headline'] ?? ''),
                'about_description' => trim($_POST['about_description'] ?? ''),
                'contact_phone' => trim($_POST['contact_phone'] ?? ''),
                'contact_email' => trim($_POST['contact_email'] ?? ''),
                'contact_address' => trim($_POST['contact_address'] ?? ''),
                'footer_description' => trim($_POST['footer_description'] ?? '')
            ];
            
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            foreach ($settings as $key => $value) {
                $stmt->execute([$key, $value, $value]);
            }
            $message = 'تم تحديث الإعدادات بنجاح.';
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// قراءة الإعدادات الحالية
$currentSettings = [];
$settingsStmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settingsRows = $settingsStmt->fetchAll();
foreach ($settingsRows as $row) {
    $currentSettings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعدادات الموقع</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard">
    <header class="dashboard-topbar">
        <div class="container dash-inner">
            <strong>إعدادات الموقع</strong>
            <div class="dash-actions">
                <a class="link" href="index.php">العودة للوحة</a>
                <a class="btn ghost" href="../logout.php">خروج</a>
            </div>
        </div>
    </header>
    <main class="container dash-grid">
        <aside class="dash-nav">
            <a href="index.php">نظرة عامة</a>
            <a href="services.php">الخدمات</a>
            <a href="products.php">المنتجات</a>
            <a href="news.php">المقالات</a>
            <a href="banners.php">إدارة البنرات</a>
            <a href="users.php">المستخدمون</a>
            <a class="active" href="settings.php">الإعدادات</a>
        </aside>
        <section class="dash-content">
            <h1>إعدادات الموقع</h1>
            <p>تعديل معلومات صفحة "عن النظام" والفوتور.</p>
            <?php if ($message): ?><div class="alert success"><?= $message; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert error"><?= $error; ?></div><?php endif; ?>

            <?php if (is_admin()): ?>
                <div class="form-panel">
                    <h3>معلومات صفحة "عن النظام"</h3>
                    <form method="post">
                        <input type="hidden" name="action" value="update_settings">
                        <label>العنوان الرئيسي
                            <input type="text" name="about_headline" value="<?= htmlspecialchars($currentSettings['about_headline'] ?? ''); ?>" required>
                        </label>
                        <label>الوصف
                            <textarea name="about_description" required><?= htmlspecialchars($currentSettings['about_description'] ?? ''); ?></textarea>
                        </label>
                        <label>رقم الهاتف
                            <input type="text" name="contact_phone" value="<?= htmlspecialchars($currentSettings['contact_phone'] ?? ''); ?>" required>
                        </label>
                        <label>البريد الإلكتروني
                            <input type="email" name="contact_email" value="<?= htmlspecialchars($currentSettings['contact_email'] ?? ''); ?>" required>
                        </label>
                        <label>العنوان
                            <input type="text" name="contact_address" value="<?= htmlspecialchars($currentSettings['contact_address'] ?? ''); ?>" required>
                        </label>
                        <label>وصف الفوتور
                            <textarea name="footer_description" required><?= htmlspecialchars($currentSettings['footer_description'] ?? ''); ?></textarea>
                        </label>
                        <button class="btn solid" type="submit">حفظ التغييرات</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="alert error">فقط الأدمن يستطيع تعديل الإعدادات.</div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>


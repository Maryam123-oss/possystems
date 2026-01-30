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

function can($perm) {
    return is_admin() || has_permission($perm);
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        if ($action === 'create') {
            if (!can('banners.create')) throw new Exception('لا تملك صلاحية إضافة بنر.');
            $title = trim($_POST['title'] ?? '');
            $badge = trim($_POST['badge'] ?? '');
            $price_current = trim($_POST['price_current'] ?? '');
            $price_old = trim($_POST['price_old'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $link = trim($_POST['link'] ?? '');
            $sort_order = (int)($_POST['sort_order'] ?? 0);
            $active = isset($_POST['active']) ? 1 : 0;
            $image = handle_upload('image');
            if (!$title || !$image) throw new Exception('العنوان والصورة مطلوبان.');
            $stmt = $pdo->prepare("INSERT INTO banners (title,badge,price_current,price_old,description,image,link,sort_order,active) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$title,$badge,$price_current,$price_old,$description,$image,$link,$sort_order,$active]);
            $message = 'تم إضافة البنر بنجاح.';
        } elseif ($action === 'update') {
            if (!can('banners.edit')) throw new Exception('لا تملك صلاحية تعديل البنر.');
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) throw new Exception('معرّف غير صالح.');
            $row = $pdo->prepare('SELECT * FROM banners WHERE id = ?');
            $row->execute([$id]);
            $b = $row->fetch();
            if (!$b) throw new Exception('لم يتم العثور على البنر.');
            $title = trim($_POST['title'] ?? $b['title']);
            $badge = trim($_POST['badge'] ?? $b['badge']);
            $price_current = trim($_POST['price_current'] ?? $b['price_current']);
            $price_old = trim($_POST['price_old'] ?? $b['price_old']);
            $description = trim($_POST['description'] ?? $b['description']);
            $link = trim($_POST['link'] ?? $b['link']);
            $sort_order = (int)($_POST['sort_order'] ?? (int)$b['sort_order']);
            $active = isset($_POST['active']) ? 1 : 0;
            $image = handle_upload('image') ?: $b['image'];
            if (!$title || !$image) throw new Exception('العنوان والصورة مطلوبان.');
            $stmt = $pdo->prepare("UPDATE banners SET title=?, badge=?, price_current=?, price_old=?, description=?, image=?, link=?, sort_order=?, active=? WHERE id=?");
            $stmt->execute([$title,$badge,$price_current,$price_old,$description,$image,$link,$sort_order,$active,$id]);
            $message = 'تم تحديث البنر.';
        } elseif ($action === 'delete') {
            if (!can('banners.delete')) throw new Exception('لا تملك صلاحية حذف البنر.');
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) throw new Exception('معرّف غير صالح.');
            $pdo->prepare('DELETE FROM banners WHERE id = ?')->execute([$id]);
            $message = 'تم حذف البنر.';
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

$banners = [];
try {
    $banners = $pdo->query('SELECT * FROM banners ORDER BY sort_order ASC, id DESC')->fetchAll();
} catch (Exception $e) {
    $banners = [];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة البنرات</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard">
    <header class="dashboard-topbar">
        <div class="container dash-inner">
            <strong>إدارة البنرات</strong>
            <div class="dash-actions">
                <a class="link" href="index.php">العودة للوحة</a>
                <a class="btn ghost" href="../index.php">الموقع</a>
            </div>
        </div>
    </header>
    <main class="container dash-grid">
        <aside class="dash-nav">
            <a href="index.php">نظرة عامة</a>
            <a href="services.php">الخدمات</a>
            <a href="products.php">المنتجات</a>
            <a href="news.php">المقالات</a>
            <a class="active" href="banners.php">إدارة البنرات</a>
            <a href="users.php">المستخدمون</a>
            <a href="settings.php">الإعدادات</a>
        </aside>
        <section class="dash-content">
            <h1>البنرات الترويجية</h1>
            <?php if ($message): ?><div class="alert success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

            <?php if (can('banners.create')): ?>
            <div class="form-panel">
                <h3>إضافة بنر جديد</h3>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create">
                    <div style="display:grid; gap:12px; grid-template-columns: repeat(auto-fit, minmax(220px,1fr));">
                        <label>العنوان
                            <input type="text" name="title" required>
                        </label>
                        <label>الوسم (مثال: خصم 20%)
                            <input type="text" name="badge">
                        </label>
                        <label>السعر الحالي
                            <input type="text" name="price_current" placeholder="مثال: 799 ر.س">
                        </label>
                        <label>السعر القديم (اختياري)
                            <input type="text" name="price_old" placeholder="مثال: 999">
                        </label>
                        <label>الترتيب
                            <input type="number" name="sort_order" value="0">
                        </label>
                        <label>الرابط عند النقر
                            <input type="url" name="link" placeholder="https://...">
                        </label>
                        <label>فعّال؟
                            <input type="checkbox" name="active" checked>
                        </label>
                        <label>الصورة
                            <input type="file" name="image" accept="image/*" required>
                        </label>
                    </div>
                    <label>الوصف المختصر
                        <textarea name="description" rows="3" placeholder="وصف مختصر يظهر على الصورة"></textarea>
                    </label>
                    <button class="btn solid" type="submit">حفظ</button>
                </form>
            </div>
            <?php endif; ?>

            <div class="cards-grid">
                <?php foreach ($banners as $b): ?>
                <div class="card">
                    <p class="eyebrow">#<?= (int)$b['id']; ?> <?= $b['active'] ? 'فعّال' : 'غير فعّال'; ?></p>
                    <h3><?= htmlspecialchars($b['title']); ?></h3>
                    <p><?= htmlspecialchars($b['description'] ?? ''); ?></p>
                    <div class="actions">
                        <?php if (can('banners.edit')): ?>
                        <button class="btn ghost sm" type="button" onclick="togglePanel(<?= (int)$b['id']; ?>,'edit')">تعديل</button>
                        <?php endif; ?>
                        <?php if (can('banners.delete')): ?>
                        <form method="post" onsubmit="return confirm('حذف البنر؟');" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$b['id']; ?>">
                            <button class="btn ghost sm" type="submit">حذف</button>
                        </form>
                        <?php endif; ?>
                    </div>
                    <?php if (can('banners.edit')): ?>
                    <div class="form-panel" id="edit-<?= (int)$b['id']; ?>" style="display:none;">
                        <h4>تعديل البنر</h4>
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?= (int)$b['id']; ?>">
                            <div style="display:grid; gap:12px; grid-template-columns: repeat(auto-fit, minmax(220px,1fr));">
                                <label>العنوان
                                    <input type="text" name="title" required value="<?= htmlspecialchars($b['title']); ?>">
                                </label>
                                <label>الوسم
                                    <input type="text" name="badge" value="<?= htmlspecialchars($b['badge']); ?>">
                                </label>
                                <label>السعر الحالي
                                    <input type="text" name="price_current" value="<?= htmlspecialchars($b['price_current']); ?>">
                                </label>
                                <label>السعر القديم
                                    <input type="text" name="price_old" value="<?= htmlspecialchars($b['price_old']); ?>">
                                </label>
                                <label>الترتيب
                                    <input type="number" name="sort_order" value="<?= (int)$b['sort_order']; ?>">
                                </label>
                                <label>الرابط
                                    <input type="url" name="link" value="<?= htmlspecialchars($b['link']); ?>">
                                </label>
                                <label>فعّال؟
                                    <input type="checkbox" name="active" <?= $b['active'] ? 'checked' : ''; ?>>
                                </label>
                                <label>استبدال الصورة (اختياري)
                                    <input type="file" name="image" accept="image/*">
                                </label>
                            </div>
                            <label>الوصف المختصر
                                <textarea name="description" rows="3"><?= htmlspecialchars($b['description']); ?></textarea>
                            </label>
                            <button class="btn solid" type="submit">حفظ التغييرات</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <script>
        function togglePanel(id, which) {
            var a = document.getElementById(which + '-' + id);
            if (!a) return;
            a.style.display = (a.style.display === 'none' || a.style.display === '') ? 'block' : 'none';
        }
    </script>
</body>
</html>

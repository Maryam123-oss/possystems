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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    // تحقق من الصلاحيات الدقيقة لكل إجراء
    if (!is_admin()) {
        $required = null;
        if ($action === 'add_category') $required = 'services.category.create';
        elseif ($action === 'edit_category') $required = 'services.category.edit';
        elseif ($action === 'delete_category') $required = 'services.category.delete';
        elseif ($action === 'add_item') $required = 'services.item.create';
        elseif ($action === 'edit_item') $required = 'services.item.edit';
        elseif ($action === 'delete_item') $required = 'services.item.delete';
        if (!$required || !has_permission($required)) {
            $error = 'لا تملك صلاحية تنفيذ هذا الإجراء.';
        }
    }
    if (!$error) {
        try {
        if ($action === 'add_category') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            if (!$name) throw new Exception('اسم التصنيف مطلوب');
            $slug = slugify($name);
            $stmt = $pdo->prepare("INSERT INTO service_categories (name, slug, description) VALUES (?,?,?)");
            $stmt->execute([$name, $slug, $description]);
            $message = 'تمت إضافة التصنيف.';
        } elseif ($action === 'add_item') {
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $details = trim($_POST['details'] ?? '');
            $featuresRaw = trim($_POST['features'] ?? '');
            if (!$categoryId || !$name) throw new Exception('البيانات غير مكتملة.');
            $featuresArr = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $featuresRaw)));
            $imagePath = handle_upload('image');
            $stmt = $pdo->prepare("INSERT INTO service_items (category_id, name, image, details, features) VALUES (?,?,?,?,?)");
            $stmt->execute([$categoryId, $name, $imagePath, $details, json_encode($featuresArr, JSON_UNESCAPED_UNICODE)]);
            $message = 'تمت إضافة الخدمة.';
        } elseif ($action === 'delete_category') {
            $id = (int) ($_POST['id'] ?? 0);
            $pdo->prepare("DELETE FROM service_categories WHERE id = ?")->execute([$id]);
            $message = 'تم حذف التصنيف وما يحتويه.';
        } elseif ($action === 'edit_category') {
            $id = (int) ($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            if (!$name || !$id) throw new Exception('البيانات غير مكتملة.');
            $slug = slugify($name);
            $pdo->prepare("UPDATE service_categories SET name = ?, slug = ?, description = ? WHERE id = ?")
                ->execute([$name, $slug, $description, $id]);
            $message = 'تم تحديث التصنيف.';
        } elseif ($action === 'edit_item') {
            $id = (int) ($_POST['id'] ?? 0);
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $details = trim($_POST['details'] ?? '');
            $featuresRaw = trim($_POST['features'] ?? '');
            if (!$id || !$categoryId || !$name) throw new Exception('البيانات غير مكتملة.');
            $featuresArr = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $featuresRaw)));
            $imagePath = handle_upload('image');
            if ($imagePath) {
                $pdo->prepare("UPDATE service_items SET category_id = ?, name = ?, image = ?, details = ?, features = ? WHERE id = ?")
                    ->execute([$categoryId, $name, $imagePath, $details, json_encode($featuresArr, JSON_UNESCAPED_UNICODE), $id]);
            } else {
                $pdo->prepare("UPDATE service_items SET category_id = ?, name = ?, details = ?, features = ? WHERE id = ?")
                    ->execute([$categoryId, $name, $details, json_encode($featuresArr, JSON_UNESCAPED_UNICODE), $id]);
            }
            $message = 'تم تحديث الخدمة.';
        } elseif ($action === 'delete_item') {
            $id = (int) ($_POST['id'] ?? 0);
            $pdo->prepare("DELETE FROM service_items WHERE id = ?")->execute([$id]);
            $message = 'تم حذف الخدمة.';
        }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$categories = $pdo->query("SELECT * FROM service_categories ORDER BY id DESC")->fetchAll();
$items = $pdo->query("SELECT si.*, sc.name AS category_name FROM service_items si JOIN service_categories sc ON sc.id = si.category_id ORDER BY si.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الخدمات</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard">
    <header class="dashboard-topbar">
        <div class="container dash-inner">
            <strong>إدارة الخدمات</strong>
            <div class="dash-actions">
                <a class="link" href="index.php">العودة للوحة</a>
                <a class="btn ghost" href="../logout.php">خروج</a>
            </div>
        </div>
    </header>
    <main class="container dash-grid">
        <aside class="dash-nav">
            <a href="index.php">نظرة عامة</a>
            <a class="active" href="services.php">الخدمات</a>
            <a href="products.php">المنتجات</a>
            <a href="news.php">المقالات</a>
            <a href="banners.php">إدارة البنرات</a>
            <a href="users.php">المستخدمون</a>
            <a href="settings.php">الإعدادات</a>
        </aside>
        <section class="dash-content">
            <h1>تصنيفات الخدمات</h1>
            <p>إضافة / تعديل / حذف التصنيفات والعناصر داخل كل تصنيف.</p>
            <?php if ($message): ?><div class="alert success"><?= $message; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert error"><?= $error; ?></div><?php endif; ?>

            <div class="cards-grid">
                <?php foreach ($categories as $cat): ?>
                    <div class="card">
                        <h3><?= $cat['name']; ?></h3>
                        <p><?= $cat['description']; ?></p>
                        <div class="actions">
                            <?php if (is_admin() || has_permission('services.category.edit')): ?>
                                <button class="btn ghost" onclick="editCategory(<?= $cat['id']; ?>)">تعديل</button>
                            <?php endif; ?>
                            <?php if (is_admin() || has_permission('services.category.delete')): ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_category">
                                    <input type="hidden" name="id" value="<?= $cat['id']; ?>">
                                    <button class="btn ghost" type="submit" onclick="return confirm('هل أنت متأكد من الحذف؟ سيتم حذف جميع العناصر داخل هذا التصنيف')">حذف</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <h3>الخدمات (العناصر)</h3>
            <div class="cards-grid">
                <?php foreach ($items as $item): ?>
                    <div class="card">
                        <p class="eyebrow"><?= $item['category_name']; ?></p>
                        <h4><?= $item['name']; ?></h4>
                        <p><?= $item['details']; ?></p>
                        <div class="actions">
                            <?php if (is_admin() || has_permission('services.item.edit')): ?>
                                <button class="btn ghost" onclick="editItem(<?= $item['id']; ?>)">تعديل</button>
                            <?php endif; ?>
                            <?php if (is_admin() || has_permission('services.item.delete')): ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_item">
                                    <input type="hidden" name="id" value="<?= $item['id']; ?>">
                                    <button class="btn ghost" type="submit" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (is_admin() || has_permission('services.category.create')): ?>
                <div class="form-panel">
                    <h3>إضافة تصنيف خدمة</h3>
                    <form method="post">
                        <input type="hidden" name="action" value="add_category">
                        <label>اسم التصنيف
                            <input type="text" name="name" required placeholder="مثل: الصيانة">
                        </label>
                        <label>وصف مختصر
                            <textarea name="description" placeholder="وصف مختصر للتصنيف"></textarea>
                        </label>
                        <button class="btn solid" type="submit">حفظ</button>
                    </form>
                </div>

            <?php endif; ?>

            <?php if (is_admin() || has_permission('services.item.create')): ?>
                <div class="form-panel">
                    <h3>إضافة خدمة داخل تصنيف</h3>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_item">
                        <label>التصنيف
                            <select name="category_id" required>
                                <option value="">اختر التصنيف</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id']; ?>"><?= $cat['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>اسم الخدمة
                            <input type="text" name="name" required placeholder="اسم الخدمة">
                        </label>
                        <label>وصف / تفاصيل
                            <textarea name="details" placeholder="تفاصيل الخدمة"></textarea>
                        </label>
                        <label>المميزات (افصلها بسطر جديد أو فاصلة)
                            <textarea name="features" placeholder="ميزة 1\nميزة 2"></textarea>
                        </label>
                        <label>صورة (اختياري)
                            <input type="file" name="image" accept="image/*">
                        </label>
                        <button class="btn solid" type="submit">حفظ</button>
                    </form>
                </div>
            <?php endif; ?>

            <?php if (is_admin() || has_permission('services.category.edit')): ?>
                <div class="form-panel" id="edit-category-form" style="display:none;">
                    <h3>تعديل تصنيف</h3>
                    <form method="post" id="edit-category-form-data">
                        <input type="hidden" name="action" value="edit_category">
                        <input type="hidden" name="id" id="edit-cat-id">
                        <label>اسم التصنيف
                            <input type="text" name="name" id="edit-cat-name" required>
                        </label>
                        <label>وصف مختصر
                            <textarea name="description" id="edit-cat-description"></textarea>
                        </label>
                        <button class="btn solid" type="submit">حفظ التعديلات</button>
                        <button class="btn ghost" type="button" onclick="cancelEditCategory()">إلغاء</button>
                    </form>
                </div>

            <?php endif; ?>

            <?php if (is_admin() || has_permission('services.item.edit')): ?>
                <div class="form-panel" id="edit-item-form" style="display:none;">
                    <h3>تعديل خدمة</h3>
                    <form method="post" enctype="multipart/form-data" id="edit-item-form-data">
                        <input type="hidden" name="action" value="edit_item">
                        <input type="hidden" name="id" id="edit-item-id">
                        <label>التصنيف
                            <select name="category_id" id="edit-item-category" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id']; ?>"><?= $cat['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>اسم الخدمة
                            <input type="text" name="name" id="edit-item-name" required>
                        </label>
                        <label>وصف / تفاصيل
                            <textarea name="details" id="edit-item-details"></textarea>
                        </label>
                        <label>المميزات (افصلها بسطر جديد أو فاصلة)
                            <textarea name="features" id="edit-item-features"></textarea>
                        </label>
                        <label>صورة (اختياري - اتركه فارغاً للاحتفاظ بالصورة الحالية)
                            <input type="file" name="image" accept="image/*">
                        </label>
                        <button class="btn solid" type="submit">حفظ التعديلات</button>
                        <button class="btn ghost" type="button" onclick="cancelEditItem()">إلغاء</button>
                    </form>
                </div>
            <?php endif; ?>
        </section>
    </main>
    <script>
        <?php
        $categoriesJson = json_encode($categories, JSON_UNESCAPED_UNICODE);
        $itemsJson = json_encode($items, JSON_UNESCAPED_UNICODE);
        echo "const categories = $categoriesJson;\n";
        echo "const items = $itemsJson;\n";
        ?>
        function editCategory(id) {
            const cat = categories.find(c => c.id == id);
            if (cat) {
                document.getElementById('edit-cat-id').value = cat.id;
                document.getElementById('edit-cat-name').value = cat.name || '';
                document.getElementById('edit-cat-description').value = cat.description || '';
                document.getElementById('edit-category-form').style.display = 'block';
                document.getElementById('edit-category-form').scrollIntoView({ behavior: 'smooth' });
            }
        }
        function cancelEditCategory() {
            document.getElementById('edit-category-form').style.display = 'none';
        }
        function editItem(id) {
            const item = items.find(i => i.id == id);
            if (item) {
                document.getElementById('edit-item-id').value = item.id;
                document.getElementById('edit-item-category').value = item.category_id;
                document.getElementById('edit-item-name').value = item.name || '';
                document.getElementById('edit-item-details').value = item.details || '';
                const features = item.features ? (Array.isArray(JSON.parse(item.features)) ? JSON.parse(item.features).join('\n') : '') : '';
                document.getElementById('edit-item-features').value = features;
                document.getElementById('edit-item-form').style.display = 'block';
                document.getElementById('edit-item-form').scrollIntoView({ behavior: 'smooth' });
            }
        }
        function cancelEditItem() {
            document.getElementById('edit-item-form').style.display = 'none';
        }
    </script>
</body>
</html>

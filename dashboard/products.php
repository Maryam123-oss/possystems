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
    if (!is_admin()) {
        $required = null;
        if ($action === 'add_category') $required = 'products.category.create';
        elseif ($action === 'edit_category') $required = 'products.category.edit';
        elseif ($action === 'delete_category') $required = 'products.category.delete';
        elseif ($action === 'add_product') $required = 'products.create';
        elseif ($action === 'edit_product') $required = 'products.edit';
        elseif ($action === 'delete_product') $required = 'products.delete';
        if (!$required || !has_permission($required)) {
            $error = 'لا تملك صلاحية تنفيذ هذا الإجراء.';
        }
    }
    if (!$error) {
        try {
        if ($action === 'add_category') {
            $name = trim($_POST['name'] ?? '');
            if (!$name) throw new Exception('اسم التصنيف مطلوب');
            $slug = slugify($name);
            $pdo->prepare("INSERT INTO product_categories (name, slug) VALUES (?,?)")->execute([$name, $slug]);
            $message = 'تمت إضافة تصنيف المنتج.';
        } elseif ($action === 'add_product') {
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $price = trim($_POST['price'] ?? '');
            $details = trim($_POST['details'] ?? '');
            $specsRaw = trim($_POST['specs'] ?? '');
            if (!$categoryId || !$name) throw new Exception('البيانات غير مكتملة.');
            $specs = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $specsRaw)));
            $imagePath = handle_upload('image');
            $pdo->prepare("INSERT INTO products (category_id, name, price, specs, details, image) VALUES (?,?,?,?,?,?)")
                ->execute([$categoryId, $name, $price, json_encode($specs, JSON_UNESCAPED_UNICODE), $details, $imagePath]);
            $message = 'تمت إضافة المنتج.';
        } elseif ($action === 'delete_category') {
            $id = (int) ($_POST['id'] ?? 0);
            $pdo->prepare("DELETE FROM product_categories WHERE id = ?")->execute([$id]);
            $message = 'تم حذف التصنيف.';
        } elseif ($action === 'edit_category') {
            $id = (int) ($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            if (!$name || !$id) throw new Exception('البيانات غير مكتملة.');
            $slug = slugify($name);
            $pdo->prepare("UPDATE product_categories SET name = ?, slug = ? WHERE id = ?")
                ->execute([$name, $slug, $id]);
            $message = 'تم تحديث التصنيف.';
        } elseif ($action === 'edit_product') {
            $id = (int) ($_POST['id'] ?? 0);
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $price = trim($_POST['price'] ?? '');
            $details = trim($_POST['details'] ?? '');
            $specsRaw = trim($_POST['specs'] ?? '');
            if (!$id || !$categoryId || !$name) throw new Exception('البيانات غير مكتملة.');
            $specs = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $specsRaw)));
            $imagePath = handle_upload('image');
            if ($imagePath) {
                $pdo->prepare("UPDATE products SET category_id = ?, name = ?, price = ?, specs = ?, details = ?, image = ? WHERE id = ?")
                    ->execute([$categoryId, $name, $price, json_encode($specs, JSON_UNESCAPED_UNICODE), $details, $imagePath, $id]);
            } else {
                $pdo->prepare("UPDATE products SET category_id = ?, name = ?, price = ?, specs = ?, details = ? WHERE id = ?")
                    ->execute([$categoryId, $name, $price, json_encode($specs, JSON_UNESCAPED_UNICODE), $details, $id]);
            }
            $message = 'تم تحديث المنتج.';
        } elseif ($action === 'delete_product') {
            $id = (int) ($_POST['id'] ?? 0);
            $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
            $message = 'تم حذف المنتج.';
        }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$categories = $pdo->query("SELECT * FROM product_categories ORDER BY id DESC")->fetchAll();
$products = $pdo->query("SELECT p.*, c.name AS category_name FROM products p JOIN product_categories c ON c.id = p.category_id ORDER BY p.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المنتجات</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard">
    <header class="dashboard-topbar">
        <div class="container dash-inner">
            <strong>إدارة المنتجات</strong>
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
            <a class="active" href="products.php">المنتجات</a>
            <a href="news.php">المقالات</a>
            <a href="banners.php">إدارة البنرات</a>
            <a href="users.php">المستخدمون</a>
            <a href="settings.php">الإعدادات</a>
        </aside>
        <section class="dash-content">
            <h1>تصنيفات المنتجات</h1>
            <p>إدارة التصنيفات والمنتجات داخل كل تصنيف.</p>
            <?php if ($message): ?><div class="alert success"><?= $message; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert error"><?= $error; ?></div><?php endif; ?>

            <h3>تصنيفات المنتجات</h3>
            <div class="cards-grid">
                <?php foreach ($categories as $cat): ?>
                    <div class="card">
                        <h4><?= $cat['name']; ?></h4>
                        <div class="actions">
                            <?php if (is_admin() || has_permission('products.category.edit')): ?>
                                <button class="btn ghost" onclick="editCategory(<?= $cat['id']; ?>)">تعديل</button>
                            <?php endif; ?>
                            <?php if (is_admin() || has_permission('products.category.delete')): ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_category">
                                    <input type="hidden" name="id" value="<?= $cat['id']; ?>">
                                    <button class="btn ghost" type="submit" onclick="return confirm('هل أنت متأكد من الحذف؟ سيتم حذف جميع المنتجات داخل هذا التصنيف')">حذف</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <h3>المنتجات</h3>
            <div class="cards-grid">
                <?php foreach ($products as $product): ?>
                    <div class="card">
                        <p class="eyebrow"><?= $product['category_name']; ?></p>
                        <h4><?= $product['name']; ?></h4>
                        <p><?= $product['details']; ?></p>
                        <p class="price"><?= $product['price']; ?></p>
                        <div class="actions">
                            <?php if (is_admin() || has_permission('products.edit')): ?>
                                <button class="btn ghost" onclick="editProduct(<?= $product['id']; ?>)">تعديل</button>
                            <?php endif; ?>
                            <?php if (is_admin() || has_permission('products.delete')): ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_product">
                                    <input type="hidden" name="id" value="<?= $product['id']; ?>">
                                    <button class="btn ghost" type="submit" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (is_admin() || has_permission('products.category.create')): ?>
                <div class="form-panel">
                    <h3>إضافة تصنيف منتج</h3>
                    <form method="post">
                        <input type="hidden" name="action" value="add_category">
                        <label>اسم التصنيف
                            <input type="text" name="name" required placeholder="أجهزة / برمجيات">
                        </label>
                        <button class="btn solid" type="submit">حفظ</button>
                    </form>
                </div>

            <?php endif; ?>

            <?php if (is_admin() || has_permission('products.create')): ?>
                <div class="form-panel">
                    <h3>إضافة منتج</h3>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_product">
                        <label>التصنيف
                            <select name="category_id" required>
                                <option value="">اختر التصنيف</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id']; ?>"><?= $cat['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>اسم المنتج
                            <input type="text" name="name" required placeholder="اسم المنتج">
                        </label>
                        <label>السعر
                            <input type="text" name="price" placeholder="مثال: 1200$">
                        </label>
                        <label>خصائص (افصلها بسطر جديد أو فاصلة)
                            <textarea name="specs" placeholder="خاصية 1\nخاصية 2"></textarea>
                        </label>
                        <label>وصف
                            <textarea name="details" placeholder="وصف مختصر + خصائص رئيسية"></textarea>
                        </label>
                        <label>صورة (اختياري)
                            <input type="file" name="image" accept="image/*">
                        </label>
                        <button class="btn solid" type="submit">حفظ</button>
                    </form>
                </div>
            <?php endif; ?>

            <?php if (is_admin() || has_permission('products.category.edit')): ?>
                <div class="form-panel" id="edit-category-form" style="display:none;">
                    <h3>تعديل تصنيف</h3>
                    <form method="post" id="edit-category-form-data">
                        <input type="hidden" name="action" value="edit_category">
                        <input type="hidden" name="id" id="edit-cat-id">
                        <label>اسم التصنيف
                            <input type="text" name="name" id="edit-cat-name" required>
                        </label>
                        <button class="btn solid" type="submit">حفظ التعديلات</button>
                        <button class="btn ghost" type="button" onclick="cancelEditCategory()">إلغاء</button>
                    </form>
                </div>

            <?php endif; ?>

            <?php if (is_admin() || has_permission('products.edit')): ?>
                <div class="form-panel" id="edit-product-form" style="display:none;">
                    <h3>تعديل منتج</h3>
                    <form method="post" enctype="multipart/form-data" id="edit-product-form-data">
                        <input type="hidden" name="action" value="edit_product">
                        <input type="hidden" name="id" id="edit-product-id">
                        <label>التصنيف
                            <select name="category_id" id="edit-product-category" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id']; ?>"><?= $cat['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>اسم المنتج
                            <input type="text" name="name" id="edit-product-name" required>
                        </label>
                        <label>السعر
                            <input type="text" name="price" id="edit-product-price">
                        </label>
                        <label>خصائص (افصلها بسطر جديد أو فاصلة)
                            <textarea name="specs" id="edit-product-specs"></textarea>
                        </label>
                        <label>وصف
                            <textarea name="details" id="edit-product-details"></textarea>
                        </label>
                        <label>صورة (اختياري - اتركه فارغاً للاحتفاظ بالصورة الحالية)
                            <input type="file" name="image" accept="image/*">
                        </label>
                        <button class="btn solid" type="submit">حفظ التعديلات</button>
                        <button class="btn ghost" type="button" onclick="cancelEditProduct()">إلغاء</button>
                    </form>
                </div>
            <?php endif; ?>
        </section>
    </main>
    <script>
        <?php
        $categoriesJson = json_encode($categories, JSON_UNESCAPED_UNICODE);
        $productsJson = json_encode($products, JSON_UNESCAPED_UNICODE);
        echo "const categories = $categoriesJson;\n";
        echo "const products = $productsJson;\n";
        ?>
        function editCategory(id) {
            const cat = categories.find(c => c.id == id);
            if (cat) {
                document.getElementById('edit-cat-id').value = cat.id;
                document.getElementById('edit-cat-name').value = cat.name || '';
                document.getElementById('edit-category-form').style.display = 'block';
                document.getElementById('edit-category-form').scrollIntoView({ behavior: 'smooth' });
            }
        }
        function cancelEditCategory() {
            document.getElementById('edit-category-form').style.display = 'none';
        }
        function editProduct(id) {
            const product = products.find(p => p.id == id);
            if (product) {
                document.getElementById('edit-product-id').value = product.id;
                document.getElementById('edit-product-category').value = product.category_id;
                document.getElementById('edit-product-name').value = product.name || '';
                document.getElementById('edit-product-price').value = product.price || '';
                document.getElementById('edit-product-details').value = product.details || '';
                const specs = product.specs ? (Array.isArray(JSON.parse(product.specs)) ? JSON.parse(product.specs).join('\n') : '') : '';
                document.getElementById('edit-product-specs').value = specs;
                document.getElementById('edit-product-form').style.display = 'block';
                document.getElementById('edit-product-form').scrollIntoView({ behavior: 'smooth' });
            }
        }
        function cancelEditProduct() {
            document.getElementById('edit-product-form').style.display = 'none';
        }
    </script>
</body>
</html>

<?php
// إعداد الاتصال بقاعدة البيانات (حدّث القيم إذا لزم)
$db_host = 'localhost';
$db_name = 'pos_site';
$db_user = 'root';
$db_pass = '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (Exception $e) {
    exit('فشل الاتصال بقاعدة البيانات: ' . $e->getMessage());
}

// أدوات مساعدة عامة
function current_user() {
    return $_SESSION['user'] ?? null;
}

function is_admin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

function is_staff() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'staff';
}

function has_permission($perm) {
    if (is_admin()) return true;
    if (!is_staff()) return false;
    global $pdo;
    $uid = $_SESSION['user']['id'] ?? 0;
    static $cache = [];
    if (!isset($cache[$uid])) {
        try {
            $stmt = $pdo->prepare("SELECT permission FROM user_permissions WHERE user_id = ?");
            $stmt->execute([$uid]);
            $cache[$uid] = array_column($stmt->fetchAll(), 'permission');
        } catch (Exception $e) {
            $cache[$uid] = [];
        }
    }
    return in_array($perm, $cache[$uid] ?? [], true);
}

function permission_catalog() {
    $fallback = [
        // مقالات
        'posts.create' => 'إنشاء مقالات',
        'posts.edit' => 'تعديل مقالات',
        'posts.delete' => 'حذف مقالات',
        // نشر المقالات يمكن إبقاءه للأدمن فقط، لكن نضيفه للمرونة مستقبلاً
        'posts.publish' => 'نشر/موافقة مقالات',

        // خدمات - تصنيفات
        'services.category.create' => 'إنشاء تصنيف خدمة',
        'services.category.edit' => 'تعديل تصنيف خدمة',
        'services.category.delete' => 'حذف تصنيف خدمة',
        // خدمات - عناصر
        'services.item.create' => 'إنشاء خدمة',
        'services.item.edit' => 'تعديل خدمة',
        'services.item.delete' => 'حذف خدمة',

        // منتجات - تصنيفات
        'products.category.create' => 'إنشاء تصنيف منتج',
        'products.category.edit' => 'تعديل تصنيف منتج',
        'products.category.delete' => 'حذف تصنيف منتج',
        // منتجات - عناصر
        'products.create' => 'إنشاء منتج',
        'products.edit' => 'تعديل منتج',
        'products.delete' => 'حذف منتج',

        // بنرات
        'banners.create' => 'إنشاء بنر',
        'banners.edit' => 'تعديل بنر',
        'banners.delete' => 'حذف بنر',

        // مفاتيح قديمة (للخلفية) إن وُجدت
        'services.manage' => 'إدارة الخدمات (قديم)',
        'products.manage' => 'إدارة المنتجات (قديم)',
    ];
    try {
        global $pdo;
        if (!isset($pdo)) return $fallback;
        $rows = $pdo->query("SELECT perm_key, label FROM permissions ORDER BY perm_key ASC")->fetchAll();
        // ابدأ بكتالوج من قاعدة البيانات إن وجد
        $out = [];
        foreach ($rows ?: [] as $r) {
            $k = trim($r['perm_key'] ?? '');
            if ($k !== '') $out[$k] = $r['label'] ?? $k;
        }
        // أضف أي مفاتيح ناقصة من الافتراضي لضمان الظهور
        foreach ($fallback as $k => $label) {
            if (!isset($out[$k])) $out[$k] = $label;
        }
        ksort($out);
        return $out ?: $fallback;
    } catch (Exception $e) {
        return $fallback;
    }
}

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = strtolower($text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    return $text ?: uniqid();
}

function handle_upload($field, $dir = 'uploads') {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $root = realpath(__DIR__ . '/..');
    $targetDir = $root . DIRECTORY_SEPARATOR . $dir;
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
    $name = uniqid('file_', true) . ($ext ? ".$ext" : '');
    $path = $targetDir . DIRECTORY_SEPARATOR . $name;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $path)) {
        return null;
    }
    return $dir . '/' . $name;
}

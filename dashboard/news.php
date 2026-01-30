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
    try {
        if ($action === 'add_post') {
            if (!is_admin() && !has_permission('posts.create')) throw new Exception('لا تملك صلاحية إنشاء مقالات.');
            $title = trim($_POST['title'] ?? '');
            $excerpt = trim($_POST['excerpt'] ?? '');
            $body = trim($_POST['body'] ?? '');
            $published = $_POST['published_at'] ?? null;
            if (!$title) throw new Exception('العنوان مطلوب.');
            $imagePath = handle_upload('image');
            $uid = $_SESSION['user']['id'] ?? null;
            if (is_admin()) {
                $stmt = $pdo->prepare("INSERT INTO posts (title, excerpt, body, image, published_at, status, created_by) VALUES (?,?,?,?,?,?,?)");
                $stmt->execute([$title, $excerpt, $body, $imagePath, $published ?: date('Y-m-d'), 'published', $uid]);
                $message = 'تم نشر المقال.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO posts (title, excerpt, body, image, status, created_by) VALUES (?,?,?,?,?,?)");
                $stmt->execute([$title, $excerpt, $body, $imagePath, 'pending', $uid]);
                $message = 'تم إرسال المقال للمراجعة.';
            }
        } elseif ($action === 'edit_post') {
            $id = (int) ($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $excerpt = trim($_POST['excerpt'] ?? '');
            $body = trim($_POST['body'] ?? '');
            $published = $_POST['published_at'] ?? null;
            if (!$title || !$id) throw new Exception('البيانات غير مكتملة.');
            $imagePath = handle_upload('image');
            if (is_admin()) {
                if ($imagePath) {
                    $pdo->prepare("UPDATE posts SET title = ?, excerpt = ?, body = ?, image = ?, published_at = ? WHERE id = ?")
                        ->execute([$title, $excerpt, $body, $imagePath, $published ?: date('Y-m-d'), $id]);
                } else {
                    $pdo->prepare("UPDATE posts SET title = ?, excerpt = ?, body = ?, published_at = ? WHERE id = ?")
                        ->execute([$title, $excerpt, $body, $published ?: date('Y-m-d'), $id]);
                }
                $message = 'تم تحديث المقال.';
            } else {
                if (!has_permission('posts.edit')) throw new Exception('لا تملك صلاحية تعديل المقالات.');
                // تحقق أن المقال يعود للمستخدم وأنه ليس منشوراً
                $row = $pdo->prepare("SELECT id, created_by, status FROM posts WHERE id = ?");
                $row->execute([$id]);
                $row = $row->fetch();
                if (!$row || (int)$row['created_by'] !== (int)($_SESSION['user']['id'] ?? 0)) throw new Exception('غير مسموح لك بتعديل هذا المقال.');
                if ($row['status'] === 'published') throw new Exception('لا يمكن تعديل مقال منشور.');
                if ($imagePath) {
                    $pdo->prepare("UPDATE posts SET title = ?, excerpt = ?, body = ?, image = ? WHERE id = ?")
                        ->execute([$title, $excerpt, $body, $imagePath, $id]);
                } else {
                    $pdo->prepare("UPDATE posts SET title = ?, excerpt = ?, body = ? WHERE id = ?")
                        ->execute([$title, $excerpt, $body, $id]);
                }
                $message = 'تم حفظ التعديلات (بانتظار موافقة الأدمن).';
            }
        } elseif ($action === 'delete_post') {
            $id = (int) ($_POST['id'] ?? 0);
            if (is_admin()) {
                $pdo->prepare("DELETE FROM posts WHERE id = ?")->execute([$id]);
                $message = 'تم حذف المقال.';
            } else {
                if (!has_permission('posts.delete')) throw new Exception('لا تملك صلاحية حذف المقالات.');
                $row = $pdo->prepare("SELECT id, created_by, status FROM posts WHERE id = ?");
                $row->execute([$id]);
                $row = $row->fetch();
                if (!$row || (int)$row['created_by'] !== (int)($_SESSION['user']['id'] ?? 0)) throw new Exception('غير مسموح لك بحذف هذا المقال.');
                if (($row['status'] ?? '') === 'published') throw new Exception('لا يمكن حذف مقال منشور.');
                $pdo->prepare("DELETE FROM posts WHERE id = ?")->execute([$id]);
                $message = 'تم حذف المقال.';
            }
        } elseif ($action === 'approve_post') {
            if (!is_admin()) throw new Exception('فقط الأدمن يمكنه النشر.');
            $id = (int) ($_POST['id'] ?? 0);
            $published = $_POST['published_at'] ?? date('Y-m-d');
            $pdo->prepare("UPDATE posts SET status = 'published', published_at = ? WHERE id = ?")->execute([$published, $id]);
            $message = 'تم نشر المقال.';
        } elseif ($action === 'reject_post') {
            if (!is_admin()) throw new Exception('فقط الأدمن يمكنه الرفض.');
            $id = (int) ($_POST['id'] ?? 0);
            $pdo->prepare("UPDATE posts SET status = 'rejected' WHERE id = ?")->execute([$id]);
            $message = 'تم رفض المقال.';
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

if (is_admin()) {
    $posts = $pdo->query("SELECT * FROM posts ORDER BY COALESCE(published_at, created_at) DESC, id DESC")->fetchAll();
} else {
    $uid = (int)($_SESSION['user']['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE created_by = ? ORDER BY COALESCE(published_at, created_at) DESC, id DESC");
    $stmt->execute([$uid]);
    $posts = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المقالات</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard">
    <header class="dashboard-topbar">
        <div class="container dash-inner">
            <strong>إدارة المقالات / الأخبار</strong>
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
            <a class="active" href="news.php">المقالات</a>
            <a href="banners.php">إدارة البنرات</a>
            <a href="users.php">المستخدمون</a>
            <a href="settings.php">الإعدادات</a>
        </aside>
        <section class="dash-content">
            <h1>المقالات</h1>
            <p>إنشاء / تعديل / حذف المقالات. الطاقم يمكنه الإرسال للمراجعة وفق الصلاحيات.</p>
            <?php if ($message): ?><div class="alert success"><?= $message; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert error"><?= $error; ?></div><?php endif; ?>

            <div class="cards-grid">
                <?php foreach ($posts as $post): ?>
                    <div class="card" id="post-<?= $post['id']; ?>">
                        <p class="eyebrow"><?= htmlspecialchars($post['published_at'] ?: 'غير منشور'); ?></p>
                        <h3><?= $post['title']; ?></h3>
                        <p><?= $post['excerpt']; ?></p>
                        <div class="actions">
                            <span class="badge">
                                <?php
                                $status = $post['status'] ?? 'published';
                                echo $status === 'published' ? 'منشور' : ($status === 'pending' ? 'بانتظار الموافقة' : ($status === 'draft' ? 'مسودة' : 'مرفوض'));
                                ?>
                            </span>
                            <?php if (is_admin()): ?>
                                <button class="btn ghost" onclick="editPost(<?= $post['id']; ?>)">تعديل</button>
                                <?php if (($post['status'] ?? 'published') !== 'published'): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="action" value="approve_post">
                                        <input type="hidden" name="id" value="<?= $post['id']; ?>">
                                        <input type="hidden" name="published_at" value="<?= date('Y-m-d'); ?>">
                                        <button class="btn ghost" type="submit">نشر</button>
                                    </form>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="action" value="reject_post">
                                        <input type="hidden" name="id" value="<?= $post['id']; ?>">
                                        <button class="btn ghost" type="submit">رفض</button>
                                    </form>
                                <?php endif; ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_post">
                                    <input type="hidden" name="id" value="<?= $post['id']; ?>">
                                    <button class="btn ghost" type="submit" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</button>
                                </form>
                            <?php else: ?>
                                <?php $isOwner = (int)($post['created_by'] ?? 0) === (int)($_SESSION['user']['id'] ?? 0); $notPublished = (($post['status'] ?? '') !== 'published'); ?>
                                <?php if (has_permission('posts.edit') && $isOwner && $notPublished): ?>
                                    <button class="btn ghost" onclick="editPost(<?= $post['id']; ?>)">تعديل</button>
                                <?php endif; ?>
                                <?php if (has_permission('posts.delete') && $isOwner && $notPublished): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="action" value="delete_post">
                                        <input type="hidden" name="id" value="<?= $post['id']; ?>">
                                        <button class="btn ghost" type="submit" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (is_admin() || has_permission('posts.edit')): ?>
                <div class="form-panel" id="edit-form" style="display:none;">
                    <h3>تعديل مقال</h3>
                    <form method="post" enctype="multipart/form-data" id="edit-post-form">
                        <input type="hidden" name="action" value="edit_post">
                        <input type="hidden" name="id" id="edit-post-id">
                        <label>عنوان المقال
                            <input type="text" name="title" id="edit-post-title" required>
                        </label>
                        <label>ملخص
                            <textarea name="excerpt" id="edit-post-excerpt"></textarea>
                        </label>
                        <label>المحتوى
                            <textarea name="body" id="edit-post-body"></textarea>
                        </label>
                        <?php if (is_admin()): ?>
                            <label>تاريخ النشر
                                <input type="date" name="published_at" id="edit-post-date">
                            </label>
                        <?php endif; ?>
                        <label>صورة مصغرة (اختياري - اتركه فارغاً للاحتفاظ بالصورة الحالية)
                            <input type="file" name="image" accept="image/*">
                        </label>
                        <button class="btn solid" type="submit">حفظ التعديلات</button>
                        <button class="btn ghost" type="button" onclick="cancelEdit()">إلغاء</button>
                    </form>
                </div>
            <?php endif; ?>

            <?php if (is_admin() || has_permission('posts.create')): ?>
                <div class="form-panel">
                    <h3>إضافة مقال</h3>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_post">
                        <label>عنوان المقال
                            <input type="text" name="title" required placeholder="العنوان">
                        </label>
                        <label>ملخص
                            <textarea name="excerpt" placeholder="ملخص مختصر"></textarea>
                        </label>
                        <label>المحتوى
                            <textarea name="body" placeholder="نص المقال"></textarea>
                        </label>
                        <?php if (is_admin()): ?>
                            <label>تاريخ النشر
                                <input type="date" name="published_at" value="<?= date('Y-m-d'); ?>">
                            </label>
                        <?php endif; ?>
                        <label>صورة مصغرة
                            <input type="file" name="image" accept="image/*">
                        </label>
                        <button class="btn solid" type="submit"><?= is_admin() ? 'نشر' : 'إرسال للمراجعة'; ?></button>
                    </form>
                </div>
            <?php endif; ?>
        </section>
    </main>
    <script>
        function editPost(id) {
            <?php
            $postsJson = json_encode($posts, JSON_UNESCAPED_UNICODE);
            echo "const posts = $postsJson;\n";
            ?>
            const post = posts.find(p => p.id == id);
            if (post) {
                document.getElementById('edit-post-id').value = post.id;
                document.getElementById('edit-post-title').value = post.title || '';
                document.getElementById('edit-post-excerpt').value = post.excerpt || '';
                document.getElementById('edit-post-body').value = post.body || '';
                document.getElementById('edit-post-date').value = post.published_at || '';
                document.getElementById('edit-form').style.display = 'block';
                document.getElementById('edit-form').scrollIntoView({ behavior: 'smooth' });
            }
        }
        function cancelEdit() {
            document.getElementById('edit-form').style.display = 'none';
        }
    </script>
</body>
</html>

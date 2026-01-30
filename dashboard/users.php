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
    if ($role !== 'admin') {
        $error = 'فقط الأدمن يستطيع إدارة المستخدمين.';
    } else {
        $action = $_POST['action'] ?? '';
        try {
            if ($action === 'create') {
                $name = trim($_POST['name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $newRole = $_POST['role'] ?? 'staff';
                $password = trim($_POST['password'] ?? '');
                if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
                    throw new Exception('بيانات غير صالحة أو كلمة مرور قصيرة.');
                }
                $exists = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $exists->execute([$email]);
                if ($exists->fetch()) {
                    throw new Exception('البريد مسجل مسبقاً.');
                }
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?,?)")
                    ->execute([$name, $email, $hash, $newRole]);
                $message = 'تم إضافة المستخدم.';
            } elseif ($action === 'delete') {
                $id = (int) ($_POST['id'] ?? 0);
                if ($id === (int)$_SESSION['user']['id']) {
                    throw new Exception('لا يمكنك حذف نفسك.');
                }
                $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
                $message = 'تم حذف المستخدم.';
            } elseif ($action === 'promote') {
                $id = (int) ($_POST['id'] ?? 0);
                $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?")->execute([$id]);
                $message = 'تمت الترقية إلى أدمن.';
            } elseif ($action === 'demote') {
                $id = (int) ($_POST['id'] ?? 0);
                if ($id === (int)$_SESSION['user']['id']) {
                    throw new Exception('لا يمكنك تخفيض نفسك.');
                }
                $pdo->prepare("UPDATE users SET role = 'staff' WHERE id = ?")->execute([$id]);
                $message = 'تم تخفيض الصلاحية إلى طاقم.';
            } elseif ($action === 'update_permissions') {
                $userId = (int) ($_POST['user_id'] ?? 0);
                if (!$userId) throw new Exception('معرّف المستخدم غير صالح.');
                // إعادة بناء صلاحيات المستخدم
                $pdo->prepare("DELETE FROM user_permissions WHERE user_id = ?")->execute([$userId]);
                $perms = $_POST['permissions'] ?? [];
                if (!is_array($perms)) $perms = [];
                $insert = $pdo->prepare("INSERT INTO user_permissions (user_id, permission) VALUES (?, ?)");
                foreach ($perms as $perm) {
                    $perm = trim((string)$perm);
                    if ($perm !== '') {
                        $insert->execute([$userId, $perm]);
                    }
                }
                $message = 'تم تحديث صلاحيات المستخدم.';
            } elseif ($action === 'update_user') {
                $id = (int)($_POST['id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $newPass = trim($_POST['password'] ?? '');
                if (!$id || !$name || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('البيانات غير صالحة.');
                }
                $dup = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
                $dup->execute([$email, $id]);
                if ($dup->fetch()) throw new Exception('هذا البريد مستخدم بالفعل.');
                $avatarPath = handle_upload('avatar');
                if ($newPass && strlen($newPass) < 6) throw new Exception('كلمة المرور يجب أن تكون 6 أحرف على الأقل.');
                if ($newPass && $avatarPath) {
                    $pdo->prepare("UPDATE users SET name = ?, email = ?, password_hash = ?, avatar = ? WHERE id = ?")
                        ->execute([$name, $email, password_hash($newPass, PASSWORD_DEFAULT), $avatarPath, $id]);
                } elseif ($newPass) {
                    $pdo->prepare("UPDATE users SET name = ?, email = ?, password_hash = ? WHERE id = ?")
                        ->execute([$name, $email, password_hash($newPass, PASSWORD_DEFAULT), $id]);
                } elseif ($avatarPath) {
                    $pdo->prepare("UPDATE users SET name = ?, email = ?, avatar = ? WHERE id = ?")
                        ->execute([$name, $email, $avatarPath, $id]);
                } else {
                    $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?")
                        ->execute([$name, $email, $id]);
                }
                $message = 'تم تحديث بيانات المستخدم.';
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$users = $pdo->query("SELECT id, name, email, role FROM users ORDER BY id DESC")->fetchAll();
// جلب صلاحيات جميع المستخدمين
$permissionsByUser = [];
try {
    $permRows = $pdo->query("SELECT user_id, permission FROM user_permissions")->fetchAll();
    foreach ($permRows as $pr) {
        $uid = (int)$pr['user_id'];
        if (!isset($permissionsByUser[$uid])) $permissionsByUser[$uid] = [];
        $permissionsByUser[$uid][] = $pr['permission'];
    }
} catch (Exception $e) {
    $permissionsByUser = [];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المستخدمين</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard">
    <header class="dashboard-topbar">
        <div class="container dash-inner">
            <strong>إدارة المستخدمين</strong>
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
            <a class="active" href="users.php">المستخدمون</a>
            <a href="settings.php">الإعدادات</a>
        </aside>
        <section class="dash-content">
            <h1>المستخدمون والصلاحيات</h1>
            <p>إدارة الحسابات وترقيتها (أدمن / طاقم).</p>
            <?php if ($message): ?><div class="alert success"><?= $message; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert error"><?= $error; ?></div><?php endif; ?>

            <?php if ($role !== 'admin'): ?>
                <div class="alert error">فقط الأدمن يستطيع إدارة المستخدمين.</div>
            <?php else: ?>
                <div class="cards-grid">
                    <?php foreach ($users as $user): ?>
                        <div class="card">
                            <h3><?= $user['name']; ?></h3>
                            <p><?= $user['email']; ?></p>
                            <p class="badge"><?= $user['role'] === 'admin' ? 'مدير' : ($user['role'] === 'staff' ? 'طاقم' : 'مستخدم'); ?></p>
                            <div class="actions">
                                <?php if ($user['role'] === 'staff'): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="action" value="promote">
                                        <input type="hidden" name="id" value="<?= $user['id']; ?>">
                                        <button class="btn ghost sm" type="submit">ترقية لأدمن</button>
                                    </form>
                                <?php elseif ($user['role'] === 'admin'): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="action" value="demote">
                                        <input type="hidden" name="id" value="<?= $user['id']; ?>">
                                        <button class="btn ghost sm" type="submit">تخفيض لطاقم</button>
                                    </form>
                                <?php else: ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="action" value="demote">
                                        <input type="hidden" name="id" value="<?= $user['id']; ?>">
                                        <button class="btn ghost sm" type="submit">ترقية لطاقم</button>
                                    </form>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="action" value="promote">
                                        <input type="hidden" name="id" value="<?= $user['id']; ?>">
                                        <button class="btn ghost sm" type="submit">ترقية لأدمن</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($user['id'] !== (int)$_SESSION['user']['id']): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $user['id']; ?>">
                                        <button class="btn ghost sm" type="submit">حذف</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($role === 'admin'): ?>
                                    <button class="btn ghost sm" type="button" onclick="togglePanel(<?= $user['id']; ?>, 'perms')">الصلاحيات</button>
                                    <button class="btn ghost sm" type="button" onclick="togglePanel(<?= $user['id']; ?>, 'edit')">تعديل</button>
                                <?php endif; ?>
                            </div>
                            <?php if ($role === 'admin'): ?>
                                <?php if ($user['role'] === 'staff'): ?>
                                    <?php $uPerms = $permissionsByUser[$user['id']] ?? []; ?>
                                    <div class="form-panel perms-panel" id="perms-<?= $user['id']; ?>" style="display:none;">
                                        <h4>صلاحيات الطاقم</h4>
                                        <form method="post">
                                            <input type="hidden" name="action" value="update_permissions">
                                            <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                                            <div class="perm-grid">
                                                <?php foreach (permission_catalog() as $key => $label): ?>
                                                    <label class="perm-item">
                                                        <input type="checkbox" name="permissions[]" value="<?= htmlspecialchars($key); ?>" <?= in_array($key, $uPerms ?? [], true) ? 'checked' : ''; ?>>
                                                        <span><?= htmlspecialchars($label); ?></span>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                            <button class="btn solid" type="submit">حفظ الصلاحيات</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                                <div class="form-panel" id="edit-<?= $user['id']; ?>" style="display:none;">
                                    <h4>تعديل بيانات المستخدم</h4>
                                    <form method="post" enctype="multipart/form-data">
                                        <input type="hidden" name="action" value="update_user">
                                        <input type="hidden" name="id" value="<?= $user['id']; ?>">
                                        <label>الاسم
                                            <input type="text" name="name" required value="<?= htmlspecialchars($user['name']); ?>">
                                        </label>
                                        <label>البريد الإلكتروني
                                            <input type="email" name="email" required value="<?= htmlspecialchars($user['email']); ?>">
                                        </label>
                                        <label>كلمة مرور جديدة (اختياري)
                                            <input type="password" name="password" placeholder="اتركها فارغة للإبقاء عليها">
                                        </label>
                                        <label>صورة رمزية (اختياري)
                                            <input type="file" name="avatar" accept="image/*">
                                        </label>
                                        <button class="btn solid" type="submit">حفظ</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="form-panel">
                    <h3>إضافة مستخدم</h3>
                    <form method="post">
                        <input type="hidden" name="action" value="create">
                        <label>الاسم
                            <input type="text" name="name" required placeholder="الاسم الكامل">
                        </label>
                        <label>البريد الإلكتروني
                            <input type="email" name="email" required placeholder="you@example.com">
                        </label>
                        <label>كلمة المرور
                            <input type="password" name="password" required placeholder="كلمة مرور (6 أحرف على الأقل)">
                        </label>
                        <label>الصلاحية
                            <select name="role">
                                <option value="staff">طاقم</option>
                                <option value="admin">أدمن</option>
                            </select>
                        </label>
                        <button class="btn solid" type="submit">حفظ</button>
                    </form>
                </div>
            <?php endif; ?>
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

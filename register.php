<?php require_once __DIR__ . '/includes/header.php'; ?>
<?php
$message = null;
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        $error = 'يرجى إدخال بيانات صحيحة (كلمة المرور 6 أحرف على الأقل).';
    } else {
        // تحقق من التكرار
        $exists = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $exists->execute([$email]);
        if ($exists->fetch()) {
            $error = 'هذا البريد مسجل مسبقاً.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'user')");
            $insert->execute([$name, $email, $hash]);
            $userId = $pdo->lastInsertId();
            $_SESSION['user'] = [
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'role' => 'user'
            ];
            $message = 'تم إنشاء الحساب بنجاح.';
            header('Location: index.php');
            exit;
        }
    }
}
?>

<main class="page auth">
    <div class="container auth-card">
        <h1>إنشاء حساب</h1>
        <p>إنشاء حساب مستخدم جديد.</p>
        <?php if ($message): ?>
            <div class="alert success"><?= $message; ?></div>
        <?php elseif ($error): ?>
            <div class="alert error"><?= $error; ?></div>
        <?php endif; ?>
        <form method="post">
            <label>الاسم
                <input type="text" name="name" required placeholder="الاسم الكامل">
            </label>
            <label>البريد الإلكتروني
                <input type="email" name="email" required placeholder="you@example.com">
            </label>
            <label>كلمة المرور
                <input type="password" name="password" required placeholder="••••••••">
            </label>
            <button class="btn solid" type="submit">تسجيل</button>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>



<?php require_once __DIR__ . '/includes/header.php'; ?>
<?php
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !$password) {
        $error = 'يرجى إدخال بريد وكلمة مرور صالحين.';
    } else {
        $stmt = $pdo->prepare("SELECT id, name, email, password_hash, role, avatar FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $hash = $user['password_hash'];
            $valid = password_verify($password, $hash);
            // دعم قديم لـ MD5 وإعادة تحويله إلى password_hash
            if (!$valid && strlen($hash) === 32 && md5($password) === $hash) {
                $valid = true;
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$newHash, $user['id']]);
                $hash = $newHash;
            }
            if ($valid) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'avatar' => $user['avatar'] ?? null
                ];
                // فقط الأدمن والطاقم يمكنهم الوصول للوحة التحكم
                if ($user['role'] === 'admin' || $user['role'] === 'staff') {
                    header('Location: dashboard/index.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            }
        }
        $error = 'بيانات الدخول غير صحيحة.';
    }
}
?>

<main class="page auth">
    <div class="container auth-card">
        <h1>تسجيل الدخول</h1>
        <p>الوصول للوحة التحكم (أدمن / طاقم).</p>
        <?php if ($error): ?>
            <div class="alert error"><?= $error; ?></div>
        <?php endif; ?>
        <form method="post">
            <label>البريد الإلكتروني
                <input type="email" name="email" required placeholder="you@example.com">
            </label>
            <label>كلمة المرور
                <input type="password" name="password" required placeholder="••••••••">
            </label>
            <button class="btn solid" type="submit">دخول</button>
        </form>
        <p class="hint">إذا نسيت كلمة المرور، أعد إنشاء الحساب أو اطلب من الأدمن إعادة التعيين.</p>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>



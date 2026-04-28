<?php
require_once __DIR__ . '/functions.php';
if (isLoggedIn()) { redirect(APP_URL . '/dashboard.php'); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        $error = 'Please enter username/email and password.';
    } else {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND status != 'blocked'");
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            redirect(APP_URL . '/dashboard.php');
        } else {
            $error = 'Invalid credentials or account is blocked.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?= h(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        body { background: linear-gradient(135deg, #1e3a5f 0%, #0d1b2a 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: #fff; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); width: 100%; max-width: 420px; padding: 2.5rem; }
        .brand-logo { text-align: center; margin-bottom: 1.5rem; }
        .brand-logo h1 { font-size: 1.6rem; font-weight: 700; color: #1e3a5f; margin: 0; }
        .brand-logo p { color: #6c757d; font-size: .85rem; margin: 0; }
        .form-control:focus { border-color: #1e3a5f; box-shadow: 0 0 0 .25rem rgba(30,58,95,.25); }
        .btn-login { background: #1e3a5f; border: none; color: #fff; padding: .65rem 1.5rem; font-weight: 600; width: 100%; border-radius: 8px; }
        .btn-login:hover { background: #16304f; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="brand-logo">
        <h1>🔐 <?= h(APP_NAME) ?></h1>
        <p>A2P OTP Management Panel</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= h($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= h($flash['type']) ?> alert-dismissible fade show">
            <?= h($flash['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <div class="mb-3">
            <label class="form-label fw-semibold">Username or Email</label>
            <input type="text" name="login" class="form-control" placeholder="Enter username or email"
                   value="<?= h($_POST['login'] ?? '') ?>" required autofocus>
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
        </div>
        <button type="submit" class="btn btn-login">Sign In</button>
    </form>
    <p class="text-center text-muted mt-3 mb-0" style="font-size:.8rem;">
        Default: admin / password — change immediately
    </p>
</div>
<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>

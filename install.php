<?php
/**
 * Sigma SMS A2P — Installer
 * Visit this file once in browser: http://yoursite.com/install.php
 * DELETE this file after installation!
 */

define('INSTALL_STEP', true);

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost    = trim($_POST['db_host']    ?? 'localhost');
    $dbName    = trim($_POST['db_name']    ?? 'sigma_sms_a2p');
    $dbUser    = trim($_POST['db_user']    ?? 'root');
    $dbPass    = $_POST['db_pass']         ?? '';
    $appUrl    = rtrim(trim($_POST['app_url'] ?? ''), '/');
    $adminUser = trim($_POST['admin_user'] ?? 'admin');
    $adminPass = $_POST['admin_pass']      ?? '';
    $adminEmail= trim($_POST['admin_email']?? '');

    if (empty($adminUser) || strlen($adminPass) < 6) {
        $error = 'Admin username and password (min 6 chars) are required.';
    } else {
        // Test DB connection
        try {
            $dsn = "mysql:host=$dbHost;charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            // Create database
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$dbName`");

            // Run schema
            $schema = file_get_contents(__DIR__ . '/schema.sql');
            // Split by semicolons and run each statement
            $statements = array_filter(array_map('trim', explode(';', $schema)));
            foreach ($statements as $stmt) {
                if (!empty($stmt) && !str_starts_with($stmt, '--')) {
                    try { $pdo->exec($stmt); } catch (PDOException $e) {
                        // Ignore duplicate column/table errors
                        if (!in_array($e->getCode(), ['42S01','42S21'])) {
                            // table already exists - skip
                        }
                    }
                }
            }

            // Update/insert admin user
            $hash = password_hash($adminPass, PASSWORD_DEFAULT);
            $exists = $pdo->prepare("SELECT id FROM users WHERE role='admin' LIMIT 1");
            $exists->execute();
            $adminRow = $exists->fetch();
            if ($adminRow) {
                $pdo->prepare("UPDATE users SET username=?, email=?, password=? WHERE id=?")
                    ->execute([$adminUser, $adminEmail ?: null, $hash, $adminRow['id']]);
            } else {
                $pdo->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?,?,?,'admin','active')")
                    ->execute([$adminUser, $adminEmail ?: null, $hash]);
            }

            // Write config.php
            $configContent = <<<PHP
<?php
define('DB_HOST', '$dbHost');
define('DB_NAME', '$dbName');
define('DB_USER', '$dbUser');
define('DB_PASS', '$dbPass');
define('DB_CHARSET', 'utf8mb4');
define('APP_NAME', 'Sigma SMS A2P');
define('APP_URL', '$appUrl');
define('APP_VERSION', '1.0.0');
define('OTP_API_URL', 'https://tempnum.net/api/public/otps');
define('OTP_FETCH_INTERVAL', 60);
define('SESSION_LIFETIME', 86400);
date_default_timezone_set('UTC');
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
function getDB(): PDO {
    static \$pdo = null;
    if (\$pdo === null) {
        \$dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
        \$options = [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false];
        try { \$pdo = new PDO(\$dsn, DB_USER, DB_PASS, \$options); }
        catch (PDOException \$e) { http_response_code(500); die(json_encode(['error'=>'DB connection failed'])); }
    }
    return \$pdo;
}
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['lifetime'=>SESSION_LIFETIME,'path'=>'/','secure'=>false,'httponly'=>true,'samesite'=>'Lax']);
    session_start();
}
PHP;
            file_put_contents(__DIR__ . '/config.php', $configContent);
            $success = "Installation complete! <a href='$appUrl/login.php'>Go to Login</a>. <strong>Delete install.php now!</strong>";

        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Auto-detect app URL
$proto    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$path     = rtrim(dirname($_SERVER['PHP_SELF']), '/');
$guessUrl = "$proto://$host$path";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Install — Sigma SMS A2P</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>body{background:#f4f6fa;}
.install-card{max-width:560px;margin:3rem auto;background:#fff;border-radius:14px;box-shadow:0 4px 24px rgba(0,0,0,.08);padding:2rem;}
h2{color:#1e3a5f;font-weight:700;}</style>
</head>
<body>
<div class="install-card">
  <h2>🔐 Sigma SMS A2P</h2>
  <p class="text-muted mb-4">Run this installer once to set up your database and admin account.</p>

  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php else: ?>

  <form method="POST">
    <h5 class="mb-3">Database Configuration</h5>
    <div class="row g-3 mb-4">
      <div class="col-6"><label class="form-label">DB Host</label>
        <input type="text" name="db_host" class="form-control" value="localhost" required></div>
      <div class="col-6"><label class="form-label">DB Name</label>
        <input type="text" name="db_name" class="form-control" value="sigma_sms_a2p" required></div>
      <div class="col-6"><label class="form-label">DB User</label>
        <input type="text" name="db_user" class="form-control" value="root" required></div>
      <div class="col-6"><label class="form-label">DB Password</label>
        <input type="password" name="db_pass" class="form-control" placeholder="Leave blank if none"></div>
    </div>

    <h5 class="mb-3">Application</h5>
    <div class="mb-3"><label class="form-label">App URL (no trailing slash)</label>
      <input type="text" name="app_url" class="form-control" value="<?= htmlspecialchars($guessUrl) ?>" required></div>

    <h5 class="mb-3">Admin Account</h5>
    <div class="row g-3 mb-4">
      <div class="col-6"><label class="form-label">Username *</label>
        <input type="text" name="admin_user" class="form-control" value="admin" required></div>
      <div class="col-6"><label class="form-label">Password * (min 6 chars)</label>
        <input type="password" name="admin_pass" class="form-control" required></div>
      <div class="col-12"><label class="form-label">Email</label>
        <input type="email" name="admin_email" class="form-control" placeholder="admin@example.com"></div>
    </div>

    <button type="submit" class="btn btn-primary w-100">Install Now</button>
  </form>
  <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

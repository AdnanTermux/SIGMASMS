<?php
/**
 * Sigma SMS A2P OTP Panel - Configuration
 * Railway-compatible: reads DB credentials from environment variables.
 */

// ── Database ──────────────────────────────────────────────────────────────────
// On Railway: set these in your service Variables tab.
// Locally: set them in your shell or just hard-code below.
define('DB_HOST',    getenv('DB_HOST')    ?: 'localhost');
define('DB_NAME',    getenv('DB_NAME')    ?: 'sigma_sms_a2p');
define('DB_USER',    getenv('DB_USER')    ?: 'root');
define('DB_PASS',    getenv('DB_PASS')    ?: '');
define('DB_CHARSET', 'utf8mb4');

// ── Application ───────────────────────────────────────────────────────────────
// APP_URL: on Railway this is usually https://<your-project>.up.railway.app
// Set the APP_URL environment variable in Railway, or hard-code your domain below.
define('APP_NAME',    'Sigma SMS A2P');
define('APP_URL',     rtrim(getenv('APP_URL') ?: 'http://localhost/sigma_sms', '/'));
define('APP_VERSION', '1.0.0');

// ── External OTP API ──────────────────────────────────────────────────────────
define('OTP_API_URL',       'https://tempnum.net/api/public/otps');
define('OTP_FETCH_INTERVAL', 60); // seconds between fetches

// ── Session ───────────────────────────────────────────────────────────────────
define('SESSION_LIFETIME', 86400); // 24 hours

// ── Timezone / Error reporting ────────────────────────────────────────────────
date_default_timezone_set('UTC');
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors',     '1');

// ── PDO connection (singleton) ────────────────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed']));
        }
    }
    return $pdo;
}

// ── Session start ─────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

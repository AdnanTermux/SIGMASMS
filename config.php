<?php
/**
 * Sigma SMS A2P OTP Panel - Configuration
 * Railway-compatible: reads credentials from Railway environment variables.
 */

// ── Database ──────────────────────────────────────────────────────────────────
define('DB_HOST',    getenv('MYSQLHOST')     ?: '127.0.0.1');
define('DB_NAME',    getenv('MYSQLDATABASE') ?: 'sigma_sms_a2p');
define('DB_USER',    getenv('MYSQLUSER')     ?: 'root');
define('DB_PASS',    getenv('MYSQLPASSWORD') ?: '');
define('DB_PORT',    getenv('MYSQLPORT')     ?: '3306');
define('DB_CHARSET', 'utf8mb4');

// ── Application ───────────────────────────────────────────────────────────────
define('APP_NAME',    'Sigma SMS A2P');
define('APP_URL',     rtrim(getenv('APP_URL') ?: 'http://localhost', '/'));
define('APP_VERSION', '1.0.0');

// ── External OTP API ──────────────────────────────────────────────────────────
define('OTP_API_URL',        'https://tempnum.net/api/public/otps');
define('OTP_FETCH_INTERVAL', 60);

// ── Session ───────────────────────────────────────────────────────────────────
define('SESSION_LIFETIME', 86400);

// ── Timezone / Error reporting ────────────────────────────────────────────────
date_default_timezone_set('UTC');
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors',     '1');

// ── PDO connection (singleton) ────────────────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        // Force TCP (not Unix socket) by using host= and port= explicitly
        $dsn = 'mysql:host=' . DB_HOST
             . ';port='      . DB_PORT
             . ';dbname='    . DB_NAME
             . ';charset='   . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
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

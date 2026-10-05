<?php
/**
 * ============================================================
 *  SIM Mahasiswa Universitas Muhammadiyah Bengkulu (UMB)
 *  config/app.php — Konfigurasi BASE_URL & setting global
 * ============================================================
 */

// Basis URL proyek di web server (Laragon).
// Contoh: http://localhost/websemantikKLP2/ → '/websemantikKLP2'
// Ubah sesuai lingkungan deploy jika berbeda.
define('BASE_URL', '/websemantikKLP2');

// Path root proyek di server file (untuk upload, include, dll.)
define('APP_ROOT', dirname(__DIR__));

// Informasi aplikasi global
define('APP_NAME', 'SIM Mahasiswa UMB');
define('APP_SLOGAN', 'Sistem Informasi Manajemen Mahasiswa');
define('UNIVERSITY_SHORT', 'UMB');

// ---------- Environment ----------
// 'development' → tampilkan error (memudahkan debugging)
// 'production'  → sembunyikan error dari user (WAJIB saat deploy)
define('APP_ENV', getenv('APP_ENV') ?: 'development');

// Zona waktu
date_default_timezone_set('Asia/Jakarta');

// ---------- Error reporting per environment ----------
if (APP_ENV === 'production') {
    // Jangan pernah membocorkan path server, query, atau stack trace ke user
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

// ---------- Header keamanan (anti clickjacking, sniffing, referrer bocor) ----------
if (!headers_sent() && PHP_SAPI !== 'cli') {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header_remove('X-Powered-By');
}

// ---------- Hardening cookie session ----------
if (session_status() === PHP_SESSION_NONE) {
    // Nama cookie custom (megahSessionID) — tidak membocorkan teknologi server
    ini_set('session.name', 'SIMMAHASISWA_SESSID');

    // Cookie hanya boleh dikirim via HTTP(S), bukan lewat JavaScript
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');

    // Tolak session ID dari client yang tidak dikenal server (anti session fixation)
    ini_set('session.use_strict_mode', '1');

    // Atribut cookie
    ini_set('session.cookie_httponly', '1');                 // tidak bisa dibaca JavaScript (anti XSS session theft)
    ini_set('session.cookie_samesite', 'Lax');                // anti CSRF lintas situs
    // Secure hanya aktif jika situs memakai HTTPS (deteksi otomatis)
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    ini_set('session.cookie_secure', $isHttps ? '1' : '0');

    // Batas umur session (2 jam) & garbage collection
    ini_set('session.gc_maxlifetime', '7200');
    ini_set('session.gc_probability', '1');
    ini_set('session.gc_divisor', '100');

    session_start();

    // Timeout inaktivitas: 30 menit tanpa aktivitas → logout paksa
    if (!defined('SESSION_IDLE_TIMEOUT')) {
        define('SESSION_IDLE_TIMEOUT', 1800);
    }
    $now = time();
    if (isset($_SESSION['last_activity']) && ($now - (int) $_SESSION['last_activity']) > SESSION_IDLE_TIMEOUT) {
        $_SESSION = [];
        session_regenerate_id(true);
    }
    $_SESSION['last_activity'] = $now;
}

/**
 * Helper global: membangun URL absolut dari path relatif terhadap BASE_URL
 */
function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return rtrim(BASE_URL, '/') . '/' . $path;
}
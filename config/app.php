<?php
/**
 * ============================================================
 *  SIM Mahasiswa Universitas Muhammadiyah Bengkulu (UMB)
 *  config/app.php — Konfigurasi BASE_URL & setting global
 * ============================================================
 */

// Path root proyek di server file (untuk upload, include, dll.)
define('APP_ROOT', dirname(__DIR__));

// Path absolut project (dipakai untuk deteksi BASE_URL di berbagai hosting)
define('APP_ROOT_REAL', realpath(APP_ROOT) ?: APP_ROOT);

// Basis URL proyek di web server.
//
// Dulu di-hardcode '/websemantikKLP2' (khas Laragon). Itu bermasalah
// ketika di-hosting di domain lain (mis. InfinityFree) karena path
// aplikasi bisa jadi '/' (root domain) atau '/subfolder'.
//
// Urutan prioritas:
//   1. Environment variable APP_BASE_URL (kalau diisi manual)
//   2. Deteksi otomatis: selisih DOCUMENT_ROOT server dengan folder project
//   3. Fallback ke nama folder project
$appBaseUrl = detect_base_url();
define('BASE_URL', $appBaseUrl);

/**
 * Deteksi basis URL aplikasi secara otomatis.
 * Contoh hasil: '/websemantikKLP2' (Laragon) atau '' (hosting di root).
 */
function detect_base_url(): string
{
    // 1. Override manual via environment variable
    $fromEnv = getenv('APP_BASE_URL');
    if (is_string($fromEnv) && trim($fromEnv) !== '') {
        return rtrim(trim($fromEnv), '/');
    }

    // 2. Bandingkan DOCUMENT_ROOT server dengan folder project
    $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
    if (is_string($docRoot) && $docRoot !== '' && APP_ROOT_REAL !== '') {
        $doc  = rtrim(str_replace('\\', '/', $docRoot), '/');
        $root = rtrim(str_replace('\\', '/', APP_ROOT_REAL), '/');

        // Normalisasi huruf besar (Windows tidak case-sensitive)
        if (strcasecmp($doc, $root) === 0) {
            return ''; // aplikasi berada tepat di root domain
        }
        if (stripos($root . '/', $doc . '/') === 0) {
            $base = substr($root, strlen($doc));      // mis. '/websemantikKLP2'
            return rtrim($base, '/');
        }
    }

    // 3. Fallback: pakai nama folder project
    return '/' . basename(APP_ROOT_REAL);
}

// Informasi aplikasi global
define('APP_NAME', 'SIM Mahasiswa UMB');
define('APP_SLOGAN', 'Sistem Informasi Manajemen Mahasiswa');
define('UNIVERSITY_SHORT', 'UMB');

// ---------- Environment ----------
// 'development' → tampilkan error (memudahkan debugging)
// 'production'  → sembunyikan error dari user (Wajib saat deploy)
//
// Deteksi otomatis: localhost / .test → development,
// sedangkan domain sungguhan (hosting) → production.
// Override manual dengan environment variable APP_ENV.
$appHost = strtolower(explode(':', (string) ($_SERVER['HTTP_HOST'] ?? ''))[0]);
$isLocal = in_array($appHost, ['localhost', '127.0.0.1', '::1', ''], true)
    || substr($appHost, -5) === '.test'
    || substr($appHost, -10) === '.localhost';
define('APP_ENV', getenv('APP_ENV') ?: ($isLocal ? 'development' : 'production'));

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
 * Helper global: membangun URL absolut dari path relatif terhadap BASE_URL.
 *
 * Aman untuk BASE_URL kosong (aplikasi di root domain):
 *   BASE_URL = ''       → url('auth/login.php')        = '/auth/login.php'
 *   BASE_URL = '/sub'   → url('auth/login.php')        = '/sub/auth/login.php'
 */
function url(string $path = ''): string
{
    $base = rtrim((string) BASE_URL, '/');
    $path = ltrim($path, '/');

    if ($path === '') {
        return $base === '' ? '/' : $base;
    }
    return $base . '/' . $path;
}

/**
 * Helper: atribut URL aset (gambar, CSS, JS) dengan cache-busting ringan
 * agar browser tidak memakai file lama setelah di-upload ulang.
 */
function asset(string $path): string
{
    $full = url($path);
    $file = APP_ROOT . '/' . ltrim($path, '/');
    $ver  = is_file($file) ? substr((string) filemtime($file), -6) : '';
    return $ver === '' ? $full : $full . '?v=' . $ver;
}
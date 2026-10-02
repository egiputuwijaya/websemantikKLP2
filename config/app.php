<?php
/**
 * ============================================================
 *  SIM Mahasiswa Universitas Muhammadiyah Bengkulu (UMB)
 *  config/app.php — Konfigurasi BASE_URL & setting global
 * ============================================================
 */

// Basis URL proyek di web server (Laragon).
// Jika diakses via http://localhost/websemantikKLP2/ → nilai berikut.
// Ubah sesuai lingkungan deploy jika berbeda.
define('BASE_URL', '/websemantikKLP2');

// Path root proyek di server file (opsional, untuk upload nanti)
define('APP_ROOT', dirname(__DIR__));

// Informasi aplikasi global
define('APP_NAME', 'SIM Mahasiswa UMB');
define('APP_SLOGAN', 'Sistem Informasi Manajemen Mahasiswa');
define('UNIVERSITY_SHORT', 'UMB');

// Zona waktu
date_default_timezone_set('Asia/Jakarta');

// Error reporting (nonaktifkan di produksi)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Session mulai (aman untuk digunakan di semua halaman publik & admin)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Helper global: membangun URL absolut dari path relatif terhadap BASE_URL
 */
function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return rtrim(BASE_URL, '/') . '/' . $path;
}

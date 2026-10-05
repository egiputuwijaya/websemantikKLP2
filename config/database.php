<?php
/**
 * ============================================================
 *  config/database.php — Koneksi database MySQL via PDO
 * ============================================================
 *  Pastikan database `sim_mahasiswa` sudah di-import
 *  (sim_mahasiswa.sql) pada phpMyAdmin / MariaDB Laragon.
 */

// ---------- Kredensial koneksi ----------
//
// Nilai di bawah adalah default LOKAL (Laragon). Untuk hosting online
// (mis. InfinityFree / cPanel), isi environment variable berikut di server
// atau langsung ubah nilainya di sini:
//
//   DB_HOST  = sqlXXX.infinityfreeapp.com   (bukan 127.0.0.1!)
//   DB_USER  = nama akun hosting
//   DB_PASS  = password dari control panel
//   DB_NAME  = nama database
//
// Prioritas: environment variable > nilai default di bawah.
$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
$DB_NAME = getenv('DB_NAME') ?: 'sim_mahasiswa';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') !== false ? (string) getenv('DB_PASS') : '';
$DB_PORT = getenv('DB_PORT') ?: '3306';
$DB_CHARSET = 'utf8mb4';

// ---------- DSN ----------
$dsn = "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset={$DB_CHARSET}";

// ---------- Opsi PDO ----------
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// ---------- Membuat koneksi $pdo ----------
try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    // Pesan error aman — tidak membocorkan kredensial ke pengguna
    http_response_code(500);
    error_log('[DB ERROR] ' . $e->getMessage());

    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>Database Error — SIM Mahasiswa UMB</title>';
    echo '<script src="https://cdn.tailwindcss.com"></script></head>';
    echo '<body class="bg-slate-100 flex items-center justify-center min-h-screen">';
    echo '<div class="bg-white rounded-2xl shadow-card p-8 max-w-md text-center border border-slate-200">';
    echo '<div class="w-16 h-16 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-5">';
    echo '<i class="fa-solid fa-database text-2xl"></i></div>';
    echo '<h1 class="font-bold text-xl text-slate-900 mb-2">Gagal Terhubung ke Database</h1>';
    echo '<p class="text-slate-500 text-sm leading-relaxed mb-4">';
    echo 'Terjadi gangguan saat menghubungi basis data sistem. ';
    echo 'Silakan coba lagi beberapa saat lagi atau hubungi administrator.</p>';
    echo '<p class="text-xs text-slate-400">Detail teknis telah dicatat pada log server.</p>';
    echo '</div></body></html>';
    exit;
}

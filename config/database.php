<?php
/**
 * ============================================================
 *  config/database.php — Koneksi database MySQL via PDO
 * ============================================================
 *  Pastikan database `sim_mahasiswa` sudah di-import
 *  (sim_mahasiswa.sql) pada phpMyAdmin / MariaDB Laragon.
 */

// ---------- Kredensial koneksi ----------
$DB_HOST = '127.0.0.1';
$DB_NAME = 'sim_mahasiswa';
$DB_USER = 'root';
$DB_PASS = '';          // Laragon default: password kosong
$DB_PORT = '3306';
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
    // Tampilkan pesan error yang jelas untuk debugging lokal
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Database Error</title></head><body style="font-family:sans-serif;padding:40px">';
    echo '<h2>Gagal terhubung ke database</h2>';
    echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    echo '<p>Pastikan MySQL Laragon berjalan dan database <code>' . htmlspecialchars($DB_NAME, ENT_QUOTES, 'UTF-8') . '</code> sudah di-import dari <code>sim_mahasiswa.sql</code>.</p>';
    echo '</body></html>';
    exit;
}

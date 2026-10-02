<?php
/**
 * ============================================================
 *  app/helper.php — Fungsi pendukung umum
 * ============================================================
 *  Berisi: sanitize, redirect, flash message (notifikasi sementara).
 */

if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/config/app.php';
}

/**
 * Sanitasi string input pengguna.
 * - trim: buang spasi di awal/akhir
 * - stripslashes: buang escape backslash (legacy magic quotes)
 * - htmlspecialchars: neutral karakter HTML berbahaya (XSS)
 *
 * @param  mixed $data
 * @return mixed  string hasil sanitasi, atau data non-string apa adanya
 */
function sanitize($data)
{
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    if (!is_string($data)) {
        return $data;
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Redirect ke URL tertentu lalu hentikan eksekusi skrip.
 *
 * @param string $url URL tujuan (boleh path relatif atau absolute)
 */
function redirect(string $url): void
{
    // Jika path relatif tanpa skema, bangun dari BASE_URL
    if ($url !== '' && $url[0] !== '/' && stripos($url, 'http') !== 0) {
        $url = rtrim(BASE_URL, '/') . '/' . ltrim($url, '/');
    }
    if (!headers_sent()) {
        header('Location: ' . $url, true, 302);
    } else {
        echo '<script>window.location.href = ' . json_encode($url) . ';</script>';
    }
    exit;
}

/**
 * Simpan notifikasi flash (hanya tampil sekali di halaman berikutnya).
 *
 * Tipe: success | danger | warning | info
 *
 * @param string $type    Jenis notifikasi
 * @param string $message Isi pesan
 */
function set_flash_message(string $type, string $message): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $allowed = ['success', 'danger', 'warning', 'info'];
    if (!in_array($type, $allowed, true)) {
        $type = 'info';
    }
    $_SESSION['flash_message'] = [
        'type'    => $type,
        'message' => $message,
    ];
}

/**
 * Ambil & hapus flash message terakhir (sekali pakai).
 *
 * @return array|null  ['type' => ..., 'message' => ...] atau null jika tidak ada
 */
function get_flash_message(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['flash_message'])) {
        return null;
    }
    $flash = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
    return $flash;
}

/**
 * Render HTML elemen alert dari flash message (opsional, siap pakai).
 * Dipanggil setelah get_flash_message() — atau gunakan get_flash_message()
 * manual jika ingin markup sendiri.
 */
function render_flash_alert(): string
{
    $flash = get_flash_message();
    if ($flash === null) {
        return '';
    }

    $map = [
        'success' => ['bg-green-50',  'border-green-200',  'text-green-800',  'fa-circle-check',    'text-green-600'],
        'danger'  => ['bg-red-50',    'border-red-200',    'text-red-800',    'fa-circle-exclamation','text-red-600'],
        'warning' => ['bg-amber-50',  'border-amber-200',  'text-amber-800',  'fa-triangle-exclamation', 'text-amber-600'],
        'info'    => ['bg-blue-50',   'border-blue-200',   'text-blue-800',   'fa-circle-info',     'text-blue-600'],
    ];

    $type   = $flash['type'];
    $cfg    = $map[$type] ?? $map['info'];
    $msg    = htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8');

    return sprintf(
        '<div class="flex items-start gap-3 p-4 rounded-xl border %s %s mb-4" role="alert">'
        . '<i class="fa-solid %s %s mt-0.5"></i>'
        . '<p class="text-sm font-medium %s">%s</p>'
        . '</div>',
        $cfg[0],
        $cfg[1],
        $cfg[3],
        $cfg[4],
        $cfg[2],
        $msg
    );
}

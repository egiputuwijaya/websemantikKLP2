<?php
/**
 * ============================================================
 *  app/helper.php — Fungsi pendukung umum
 * ============================================================
 *  Berisi: sanitize, redirect, flash message (notifikasi sementara),
 *  dan proteksi CSRF.
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
    // Path relatif tanpa skema → bangun dari BASE_URL.
    // BASE_URL boleh kosong (aplikasi di root domain hosting).
    if ($url !== '' && $url[0] !== '/' && stripos($url, 'http') !== 0) {
        $url = url($url);
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

/**
 * ============================================================
 *  PROTEKSI CSRF
 * ============================================================
 */

/**
 * Ambil (atau buat) token CSRF untuk session saat ini.
 */
function csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Cetak input hidden untuk dipasang di dalam <form method="POST">.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Validasi token CSRF dari request POST.
 * Jika tidak valid: tampilkan flash message lalu kembali ke halaman asal.
 */
function csrf_verify(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $sent   = $_POST['csrf_token'] ?? '';
    $stored = $_SESSION['csrf_token'] ?? '';

    if (!is_string($sent) || $stored === '' || !hash_equals($stored, $sent)) {
        set_flash_message('danger', 'Permintaan ditolak: token keamanan tidak valid atau sudah kedaluwarsa. Silakan coba lagi.');

        // Kembali ke halaman asal hanya jika masih satu domain
        $back = BASE_URL !== '' ? BASE_URL : '/';
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $refHost = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
            if ($refHost !== null && $refHost === ($_SERVER['HTTP_HOST'] ?? '')) {
                $back = $_SERVER['HTTP_REFERER'];
            }
        }
        redirect($back);
    }
}

/**
 * ============================================================
 *  VALIDASI FILE UPLOAD
 * ============================================================
 */

/**
 * Validasi file upload (foto profil, dll.) sebelum disimpan.
 * Melakukan 4 lapis pemeriksaan:
 *   1. Error bawaan PHP
 *   2. Ukuran maksimal
 *   3. Ekstensi putih (whitelist)
 *   4. MIME asli file (bukan sekadar dari header browser)
 *   5. Pastikan file benar-benar gambar yang valid (getimagesize)
 *
 * @param  array  $file        Elemen dari $_FILES
 * @param  int    $maxBytes    Ukuran maksimal (default 2MB)
 * @param  array  $allowedExt  Ekstensi yang diizinkan
 * @return array  ['ok' => bool, 'error' => string|null, 'ext' => string]
 */
function validate_upload(array $file, int $maxBytes = 2097152, array $allowedExt = ['jpg', 'jpeg', 'png']): array
{
    $fail = static fn(string $msg): array => ['ok' => false, 'error' => $msg, 'ext' => null];

    // 1. Error bawaan PHP
    if (!isset($file['error']) || is_array($file['error'])) {
        return $fail('Data file tidak valid.');
    }
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return $fail('Ukuran file melebihi batas yang diperbolehkan (maksimal 2MB).');
        case UPLOAD_ERR_NO_FILE:
            return $fail('File tidak ditemukan.');
        case UPLOAD_ERR_PARTIAL:
            return $fail('File hanya terunggah sebagian. Silakan coba lagi.');
        default:
            return $fail('Gagal mengunggah file (kode error: ' . $file['error'] . ').');
    }

    // 2. Ukuran
    if (!isset($file['size']) || (int) $file['size'] <= 0) {
        return $fail('File kosong.');
    }
    if ((int) $file['size'] > $maxBytes) {
        return $fail('Ukuran file melebihi ' . round($maxBytes / 1048576, 1) . 'MB.');
    }

    // 3. Ekstensi (whitelist)
    $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return $fail('Format file tidak diizinkan. Gunakan: ' . implode(', ', $allowedExt) . '.');
    }

    // 4. MIME asli file (bukan dari header browser)
    $mimeMap = [
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
    ];
    $mime = null;
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        }
    }
    if ($mime !== null && !in_array($mime, $mimeMap[$ext] ?? [$mime], true)) {
        return $fail('Isi file tidak sesuai dengan formatnya (kemungkinan percobaan menyisipkan kode berbahaya).');
    }

    // 5. Pastikan benar-benar gambar yang valid
    if (@getimagesize($file['tmp_name']) === false) {
        return $fail('File bukan gambar yang valid atau sudah rusak.');
    }

    return ['ok' => true, 'error' => null, 'ext' => $ext];
}

/**
 * Buat nama file acak yang aman (mencegah path traversal & tabrakan nama).
 */
function safe_upload_name(string $originalName, string $ext): string
{
    $base = pathinfo((string) $originalName, PATHINFO_FILENAME);
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', (string) $base));
    $slug = trim((string) $slug, '-');
    if ($slug === '') {
        $slug = 'file';
    }
    return substr($slug, 0, 40) . '-' . bin2hex(random_bytes(8)) . '.' . $ext;
}
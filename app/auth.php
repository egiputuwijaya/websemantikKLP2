<?php
/**
 * ============================================================
 *  app/auth.php — Proteksi session & cek role pengguna
 * ============================================================
 *  Fungsi utama:
 *   - check_auth()        → wajib login
 *   - check_role()        → wajib role tertentu
 *   - get_user_login()    → data pengguna aktif dari session
 */

// Pastikan helper (flash message & redirect) tersedia
require_once __DIR__ . '/helper.php';

/**
 * Dapatkan data pengguna yang sedang login dari session.
 *
 * @return array|null  Array data user atau null jika belum login
 */
function get_user_login(): ?array
{
    if (empty($_SESSION['user']) || !is_array($_SESSION['user'])) {
        return null;
    }
    return $_SESSION['user'];
}

/**
 * Pastikan pengguna sudah login.
 * Jika belum → set flash warning & redirect ke halaman login.
 *
 * Panggil di awal setiap halaman admin/internal:
 *   require_once 'app/auth.php';
 *   check_auth();
 */
function check_auth(): void
{
    $user = get_user_login();

    if ($user === null) {
        set_flash_message('warning', 'Silakan login terlebih dahulu untuk mengakses halaman tersebut.');
        redirect(url('auth/login.php'));
        exit;
    }

    // Jika akun ditandai tidak aktif di session (edge case), paksa logout
    if (isset($user['status_aktif']) && $user['status_aktif'] !== 'Aktif') {
        set_flash_message('danger', 'Akun Anda tidak aktif. Silakan hubungi administrator.');
        redirect(url('auth/logout.php') . '?csrf_token=' . urlencode(csrf_token()));
        exit;
    }
}

/**
 * Pastikan role pengguna termasuk dalam daftar yang diizinkan.
 *
 * Perbandingan dilakukan terhadap `id_role` (integer).
 * Contoh pemakaian:
 *   check_role([1]);                      // khusus Admin
 *   check_role([1, 2]);                   // Admin & Operator Prodi
 *   check_role([1, 2, 3, 4, 5]);          // semua role internal
 *
 * @param  array $allowed_roles Daftar id_role yang diizinkan
 * @return bool  true jika role diizinkan, false jika tidak
 */
function check_role(array $allowed_roles): bool
{
    $user = get_user_login();

    if ($user === null || !isset($user['id_role'])) {
        return false;
    }

    // Normalisasi ke integer agar perbandingan aman
    $userRole    = (int) $user['id_role'];
    $allowedRole = array_map('intval', $allowed_roles);

    return in_array($userRole, $allowedRole, true);
}

/**
 * Kombinasi: wajib login + wajib role tertentu.
 * Jika gagal → flash + redirect ke dashboard (atau login jika belum login).
 *
 * @param array $allowed_roles Daftar id_role yang diizinkan
 */
function require_role(array $allowed_roles): void
{
    check_auth();

    if (!check_role($allowed_roles)) {
        set_flash_message('danger', 'Maaf, Anda tidak memiliki akses ke halaman tersebut.');
        redirect(url('admin/dashboard/index.php'));
        exit;
    }
}

/**
 * Cek apakah pengguna saat ini sudah login (tanpa redirect).
 *
 * @return bool
 */
function is_logged_in(): bool
{
    return get_user_login() !== null;
}

/**
 * Ambil id_role pengguna yang sedang login.
 *
 * @return int|null
 */
function current_user_role(): ?int
{
    $user = get_user_login();
    return $user !== null && isset($user['id_role']) ? (int) $user['id_role'] : null;
}

/**
 * Ambil kode role (mis. 'ADMIN', 'OPERATOR_PRODI') pengguna login.
 *
 * @return string|null
 */
function current_role_code(): ?string
{
    $user = get_user_login();
    return $user['kode_role'] ?? null;
}

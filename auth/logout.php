<?php
/**
 * ============================================================
 *  auth/logout.php — Handler Logout & penulisan audit_log
 * ============================================================
 *  Alur:
 *   1. Ambil data user dari session (untuk audit)
 *   2. Catat LOGOUT ke audit_log
 *   3. Hancurkan seluruh session
 *   4. Buat session baru hanya untuk flash message
 *   5. Redirect ke halaman login
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/helper.php';
require_once dirname(__DIR__) . '/app/audit.php';
require_once dirname(__DIR__) . '/app/auth.php';

// ----------------------------------------------------------
// 1. Ambil data pengguna yang sedang login (sebelum dihancurkan)
// ----------------------------------------------------------
$user = get_user_login();

// ----------------------------------------------------------
// 2. Catat LOGOUT ke audit_log (jika memang ada user login)
// ----------------------------------------------------------
if ($user !== null) {
    log_activity(
        $pdo,
        (int) $user['id_pengguna'],
        'pengguna',
        (string) $user['id_pengguna'],
        'LOGOUT',
        [
            'username'     => $user['username'] ?? null,
            'nama_lengkap' => $user['nama_lengkap'] ?? null,
            'id_role'      => $user['id_role'] ?? null,
            'last_login'   => $user['last_login'] ?? null,
        ],
        null
    );
}

// ----------------------------------------------------------
// 3. Hancurkan seluruh session lama
// ----------------------------------------------------------
// Kosongkan superglobal session
$_SESSION = [];

// Hapus cookie session di sisi client (jika ada)
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Hancurkan sesi di server
session_destroy();

// ----------------------------------------------------------
// 4. Session BARU khusus untuk flash message
//    session_id('') memaksa PHP membuat ID session baru
//    dan mengirim Set-Cookie baru ke browser.
// ----------------------------------------------------------
session_id('');
session_start();
set_flash_message('success', 'Anda telah berhasil keluar. Sampai jumpa kembali!');

// ----------------------------------------------------------
// 5. Redirect ke halaman login
// ----------------------------------------------------------
redirect(url('auth/login.php'));
exit;

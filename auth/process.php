<?php
/**
 * ============================================================
 *  auth/process.php — Handler Login (validasi & pembuat session)
 * ============================================================
 *  Hanya menangani method POST. Semua query memakai Prepared Statement.
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/helper.php';
require_once dirname(__DIR__) . '/app/audit.php';

// ----------------------------------------------------------
// 1. Hanya izinkan method POST
// ----------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash_message('warning', 'Permintaan tidak valid. Silakan gunakan form login.');
    redirect(url('auth/login.php'));
    exit;
}

csrf_verify();

// ----------------------------------------------------------
// 2. Ambil & bersihkan input
// ----------------------------------------------------------
$username = isset($_POST['username']) ? trim((string) $_POST['username']) : '';
$password = isset($_POST['password']) ? (string) $_POST['password'] : '';

// ----------------------------------------------------------
// 3. Validasi input tidak boleh kosong
// ----------------------------------------------------------
if ($username === '' || $password === '') {
    set_flash_message('danger', 'Username dan password wajib diisi.');
    redirect(url('auth/login.php'));
    exit;
}

// Batasi panjang username (sesuai kolom varchar(100))
if (strlen($username) > 100) {
    set_flash_message('danger', 'Username terlalu panjang.');
    redirect(url('auth/login.php'));
    exit;
}

// ----------------------------------------------------------
// 4. Cari akun berdasarkan username + status_aktif = 'Aktif'
//    (Prepared Statement — aman dari SQL Injection)
// ----------------------------------------------------------
try {
    $sql = "SELECT p.id_pengguna,
                   p.id_role,
                   p.id_universitas,
                   p.id_fakultas,
                   p.id_program_studi,
                   p.username,
                   p.password_hash,
                   p.nama_lengkap,
                   p.email,
                   p.no_hp,
                   p.status_aktif,
                   p.last_login,
                   r.kode_role,
                   r.nama_role
            FROM pengguna p
            LEFT JOIN roles r ON r.id_role = p.id_role
            WHERE p.username = :username
              AND p.status_aktif = 'Aktif'
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log('[LOGIN ERROR] Query gagal: ' . $e->getMessage());
    set_flash_message('danger', 'Terjadi kesalahan sistem. Silakan coba lagi.');
    redirect(url('auth/login.php'));
    exit;
}

// ----------------------------------------------------------
// 5. Verifikasi akun ditemukan + password cocok
// ----------------------------------------------------------
if ($user === false) {
    // Username tidak ada ATAU akun tidak aktif — pesan sengaja disamakan
    // agar tidak membocorkan keberadaan akun (anti user enumeration).
    set_flash_message('danger', 'Username atau password salah / Akun tidak aktif.');
    redirect(url('auth/login.php'));
    exit;
}

if (!password_verify($password, $user['password_hash'])) {
    // Password salah — coba catat ke audit sebagai upaya login gagal
    log_activity(
        $pdo,
        (int) $user['id_pengguna'],
        'pengguna',
        (string) $user['id_pengguna'],
        'LOGIN',
        null,
        ['hasil' => 'Gagal: password salah', 'username' => $username]
    );

    set_flash_message('danger', 'Username atau password salah / Akun tidak aktif.');
    redirect(url('auth/login.php'));
    exit;
}

// ----------------------------------------------------------
// 6. Login berhasil → update last_login
// ----------------------------------------------------------
try {
    $stmtUpdate = $pdo->prepare("UPDATE pengguna SET last_login = NOW() WHERE id_pengguna = :id");
    $stmtUpdate->execute([':id' => $user['id_pengguna']]);
} catch (PDOException $e) {
    // Update last_login gagal tidak boleh membatalkan login
    error_log('[LOGIN WARNING] Gagal update last_login: ' . $e->getMessage());
}

// ----------------------------------------------------------
// 7. Susun data session (TANPA password_hash)
// ----------------------------------------------------------
$_SESSION['user'] = [
    'id_pengguna'      => (int) $user['id_pengguna'],
    'id_role'          => (int) $user['id_role'],
    'kode_role'        => $user['kode_role'] ?? null,
    'nama_role'        => $user['nama_role'] ?? null,
    'id_universitas'   => $user['id_universitas'] !== null ? (int) $user['id_universitas'] : null,
    'id_fakultas'      => $user['id_fakultas'] !== null ? (int) $user['id_fakultas'] : null,
    'id_program_studi' => $user['id_program_studi'] !== null ? (int) $user['id_program_studi'] : null,
    'username'         => $user['username'],
    'nama_lengkap'     => $user['nama_lengkap'],
    'email'            => $user['email'] ?? null,
    'no_hp'            => $user['no_hp'] ?? null,
    'status_aktif'     => $user['status_aktif'],
    'last_login'       => date('Y-m-d H:i:s'),
];

// Regenerasi session ID (mencegah session fixation)
session_regenerate_id(true);

// ----------------------------------------------------------
// 8. Catat aktivitas LOGIN ke audit_log
// ----------------------------------------------------------
log_activity(
    $pdo,
    (int) $user['id_pengguna'],
    'pengguna',
    (string) $user['id_pengguna'],
    'LOGIN',
    null,
    [
        'username'     => $user['username'],
        'nama_lengkap' => $user['nama_lengkap'],
        'id_role'      => (int) $user['id_role'],
        'kode_role'    => $user['kode_role'] ?? null,
    ]
);

// ----------------------------------------------------------
// 9. Redirect ke dashboard sesuai role (default: dashboard admin)
// ----------------------------------------------------------
set_flash_message('success', 'Selamat datang, ' . $user['nama_lengkap'] . '!');

// Route sederhana berdasarkan role (bisa dikembangkan nanti)
$roleRoutes = [
    1 => 'admin/dashboard/index.php',   // ADMIN
    2 => 'admin/dashboard/index.php',   // OPERATOR_PRODI
    3 => 'admin/dashboard/index.php',   // DEKANAT
    4 => 'admin/dashboard/index.php',   // REKTORAT
    5 => 'admin/profil-saya/index.php', // MAHASISWA
];

$idRole = (int) $user['id_role'];
$target = $roleRoutes[$idRole] ?? 'admin/dashboard/index.php';

redirect(url($target));
exit;

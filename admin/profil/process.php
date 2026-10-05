<?php
/**
 * admin/profil/process.php — Handler CRUD Profil Diri
 */
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';
require_once dirname(__DIR__, 2) . '/app/audit.php';

require_role([1, 2, 3, 4, 5]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('admin/profil/index.php'));
    exit;
}

csrf_verify();

$action = sanitize($_POST['action'] ?? '');
$userActive = get_user_login();
$id = (int)$userActive['id_pengguna'];

if ($action === 'update_profile') {
    $namaLengkap = sanitize($_POST['nama_lengkap'] ?? '');
    $email       = sanitize($_POST['email'] ?? null);

    if (empty($namaLengkap)) {
        set_flash_message('danger', 'Nama Lengkap tidak boleh kosong.');
        redirect(url('admin/profil/index.php'));
        exit;
    }

    try {
        $pdo->beginTransaction();

        $stmtOld = $pdo->prepare("SELECT id_pengguna, nama_lengkap, email FROM pengguna WHERE id_pengguna = :id");
        $stmtOld->execute([':id' => $id]);
        $oldData = $stmtOld->fetch();

        $sql = "UPDATE pengguna SET nama_lengkap = :nama, email = :email WHERE id_pengguna = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nama'  => $namaLengkap,
            ':email' => $email ?: null,
            ':id'    => $id
        ]);

        $stmtNew = $pdo->prepare("SELECT id_pengguna, nama_lengkap, email FROM pengguna WHERE id_pengguna = :id");
        $stmtNew->execute([':id' => $id]);
        $newData = $stmtNew->fetch();

        log_activity($pdo, $id, 'pengguna', (string)$id, 'UPDATE', $oldData, $newData);

        // Update session agar perubahannya langsung terlihat di Navbar/Sidebar
        $_SESSION['user']['nama_lengkap'] = $namaLengkap;

        $pdo->commit();
        set_flash_message('success', 'Profil Anda berhasil diperbarui.');
    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->getCode() == 23000) {
            set_flash_message('danger', 'Gagal: Email tersebut sudah digunakan oleh pengguna lain.');
        } else {
            error_log($e->getMessage());
            set_flash_message('danger', 'Terjadi kesalahan sistem.');
        }
    }
    
    redirect(url('admin/profil/index.php'));

} elseif ($action === 'update_password') {
    $passLama = $_POST['password_lama'] ?? '';
    $passBaru = $_POST['password_baru'] ?? '';
    $passKonf = $_POST['password_konfirmasi'] ?? '';

    if (empty($passLama) || empty($passBaru) || empty($passKonf)) {
        set_flash_message('danger', 'Semua kolom kata sandi wajib diisi.');
        redirect(url('admin/profil/password.php'));
        exit;
    }

    if ($passBaru !== $passKonf) {
        set_flash_message('danger', 'Kata Sandi Baru dan Konfirmasi tidak cocok!');
        redirect(url('admin/profil/password.php'));
        exit;
    }

    if (strlen($passBaru) < 8) {
        set_flash_message('danger', 'Kata sandi baru minimal harus 8 karakter.');
        redirect(url('admin/profil/password.php'));
        exit;
    }

    // Policy kompleksitas: huruf besar, huruf kecil, dan angka
    if (!preg_match('/[A-Z]/', $passBaru)
        || !preg_match('/[a-z]/', $passBaru)
        || !preg_match('/[0-9]/', $passBaru)) {
        set_flash_message('danger', 'Kata sandi baru harus memuat huruf besar, huruf kecil, dan angka.');
        redirect(url('admin/profil/password.php'));
        exit;
    }

    // Larangan password yang terlalu umum / mudah ditebak
    $passwordLarum = ['12345678', 'password', 'admin123', 'qwerty123', '123456789', 'umb12345', 'mahasiswa', 'admin1234'];
    if (in_array(strtolower($passBaru), $passwordLarum, true)) {
        set_flash_message('danger', 'Kata sandi baru terlalu umum. Silakan gunakan kombinasi yang lebih unik.');
        redirect(url('admin/profil/password.php'));
        exit;
    }

    // Password baru tidak boleh sama dengan password lama
    if ($passBaru === $passLama) {
        set_flash_message('danger', 'Kata sandi baru harus berbeda dari kata sandi lama.');
        redirect(url('admin/profil/password.php'));
        exit;
    }

    try {
        $stmtUser = $pdo->prepare("SELECT password_hash FROM pengguna WHERE id_pengguna = :id");
        $stmtUser->execute([':id' => $id]);
        $hashDb = $stmtUser->fetchColumn();

        if (!password_verify($passLama, $hashDb)) {
            set_flash_message('danger', 'Kata Sandi Lama Anda salah.');
            redirect(url('admin/profil/password.php'));
            exit;
        }

        $hashBaru = password_hash($passBaru, PASSWORD_BCRYPT);

        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE pengguna SET password_hash = :hash WHERE id_pengguna = :id");
        $stmt->execute([':hash' => $hashBaru, ':id' => $id]);

        log_activity($pdo, $id, 'pengguna', (string)$id, 'UPDATE_PASSWORD', null, null);
        $pdo->commit();

        set_flash_message('success', 'Kata Sandi Anda berhasil diperbarui.');
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log($e->getMessage());
        set_flash_message('danger', 'Terjadi kesalahan sistem.');
    }

    redirect(url('admin/profil/password.php'));
}

<?php
/**
 * admin/pengguna/process.php — Handler CRUD Pengguna
 */
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';
require_once dirname(__DIR__, 2) . '/app/audit.php';

require_role([1]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('admin/pengguna/index.php'));
    exit;
}

csrf_verify();

$action = sanitize($_POST['action'] ?? '');
$userActive = get_user_login();
$idActor = $userActive ? (int)($userActive['id_pengguna'] ?? 0) : null;

// Helper function untuk sanitasi relasi opsional
function getOptionalId(string $key): ?int {
    $val = (int)($_POST[$key] ?? 0);
    return $val > 0 ? $val : null;
}

if ($action === 'create') {
    $idRole = (int)($_POST['id_role'] ?? 0);
    $idUniv = (int)($_POST['id_universitas'] ?? 1); // Default ke 1 karena single univ
    $idFak  = getOptionalId('id_fakultas');
    $idProdi = getOptionalId('id_program_studi');
    
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $namaLengkap = sanitize($_POST['nama_lengkap'] ?? '');
    $email = sanitize($_POST['email'] ?? null);
    $status = sanitize($_POST['status_aktif'] ?? 'Aktif');

    if (empty($username) || empty($password) || empty($namaLengkap) || $idRole === 0) {
        set_flash_message('danger', 'Semua kolom bertanda * wajib diisi.');
        redirect(url('admin/pengguna/create.php'));
        exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);

    try {
        $pdo->beginTransaction();
        
        $sql = "INSERT INTO pengguna 
                (id_role, id_universitas, id_fakultas, id_program_studi, username, password_hash, nama_lengkap, email, status_aktif) 
                VALUES (:id_role, :id_univ, :id_fak, :id_prodi, :username, :hash, :nama, :email, :status)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_role'  => $idRole,
            ':id_univ'  => $idUniv,
            ':id_fak'   => $idFak,
            ':id_prodi' => $idProdi,
            ':username' => $username,
            ':hash'     => $hash,
            ':nama'     => $namaLengkap,
            ':email'    => $email ?: null,
            ':status'   => $status
        ]);
        
        $newId = $pdo->lastInsertId();

        $stmtNew = $pdo->prepare("SELECT id_pengguna, username, nama_lengkap, id_role, status_aktif FROM pengguna WHERE id_pengguna = :id");
        $stmtNew->execute([':id' => $newId]);
        $newData = $stmtNew->fetch();
        
        log_activity($pdo, $idActor, 'pengguna', (string)$newId, 'INSERT', null, $newData);

        $pdo->commit();
        set_flash_message('success', "Pengguna <b>$username</b> berhasil ditambahkan.");
    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->getCode() == 23000) {
            set_flash_message('danger', 'Gagal: Username atau Email tersebut sudah digunakan.');
        } else {
            error_log($e->getMessage());
            set_flash_message('danger', 'Terjadi kesalahan sistem.');
        }
        redirect(url('admin/pengguna/create.php'));
        exit;
    }

} elseif ($action === 'update') {
    $id = (int)($_POST['id_pengguna'] ?? 0);
    $idRole = (int)($_POST['id_role'] ?? 0);
    $idFak  = getOptionalId('id_fakultas');
    $idProdi = getOptionalId('id_program_studi');
    
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $namaLengkap = sanitize($_POST['nama_lengkap'] ?? '');
    $email = sanitize($_POST['email'] ?? null);
    $status = sanitize($_POST['status_aktif'] ?? 'Aktif');

    if (empty($username) || empty($namaLengkap) || $id === 0 || $idRole === 0) {
        set_flash_message('danger', 'Semua kolom bertanda * wajib diisi.');
        redirect(url('admin/pengguna/edit.php?id=' . $id));
        exit;
    }

    try {
        $pdo->beginTransaction();

        $stmtOld = $pdo->prepare("SELECT id_pengguna, username, nama_lengkap, id_role, status_aktif FROM pengguna WHERE id_pengguna = :id");
        $stmtOld->execute([':id' => $id]);
        $oldData = $stmtOld->fetch();

        if (!$oldData) throw new Exception("Data pengguna tidak ditemukan.");

        // Jika password diisi, update hash. Jika tidak, pertahankan yang lama.
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE pengguna SET 
                    id_role = :id_role, id_fakultas = :id_fak, id_program_studi = :id_prodi, 
                    username = :username, password_hash = :hash, nama_lengkap = :nama, 
                    email = :email, status_aktif = :status 
                    WHERE id_pengguna = :id";
            $params = [
                ':id_role'  => $idRole, ':id_fak'   => $idFak, ':id_prodi' => $idProdi,
                ':username' => $username, ':hash'     => $hash, ':nama'     => $namaLengkap,
                ':email'    => $email ?: null, ':status'   => $status, ':id'       => $id
            ];
        } else {
            $sql = "UPDATE pengguna SET 
                    id_role = :id_role, id_fakultas = :id_fak, id_program_studi = :id_prodi, 
                    username = :username, nama_lengkap = :nama, email = :email, status_aktif = :status 
                    WHERE id_pengguna = :id";
            $params = [
                ':id_role'  => $idRole, ':id_fak'   => $idFak, ':id_prodi' => $idProdi,
                ':username' => $username, ':nama'     => $namaLengkap, ':email'    => $email ?: null, 
                ':status'   => $status, ':id'       => $id
            ];
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $stmtNew = $pdo->prepare("SELECT id_pengguna, username, nama_lengkap, id_role, status_aktif FROM pengguna WHERE id_pengguna = :id");
        $stmtNew->execute([':id' => $id]);
        $newData = $stmtNew->fetch();

        log_activity($pdo, $idActor, 'pengguna', (string)$id, 'UPDATE', $oldData, $newData);

        $pdo->commit();
        set_flash_message('success', 'Data Pengguna berhasil diperbarui.');
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('[PENGGUNA UPDATE] ' . $e->getMessage());
        if ($e->getCode() == 23000) {
            set_flash_message('danger', 'Gagal: Username atau Email tersebut sudah digunakan oleh pengguna lain.');
        } else {
            error_log($e->getMessage());
            set_flash_message('danger', 'Terjadi kesalahan sistem.');
        }
        redirect(url('admin/pengguna/edit.php?id=' . $id));
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        // Detail error hanya ke log server, tidak ditampilkan ke user
        error_log('[PENGGUNA UPDATE] ' . $e->getMessage());
        set_flash_message('danger', 'Terjadi kesalahan sistem saat memproses data.');
    }

} elseif ($action === 'delete') {
    $id = (int)($_POST['id_pengguna'] ?? 0);

    if ($id === $idActor) {
        set_flash_message('danger', 'Anda tidak dapat menghapus akun Anda sendiri.');
        redirect(url('admin/pengguna/index.php'));
        exit;
    }

    try {
        $pdo->beginTransaction();

        $stmtOld = $pdo->prepare("SELECT id_pengguna, username, nama_lengkap, id_role FROM pengguna WHERE id_pengguna = :id");
        $stmtOld->execute([':id' => $id]);
        $oldData = $stmtOld->fetch();

        if ($oldData) {
            // Cek proteksi role admin 
            if ($oldData['id_role'] == 1) {
                // Memastikan minimal masih ada 1 admin aktif lainnya
                $cek = $pdo->query("SELECT COUNT(*) FROM pengguna WHERE id_role = 1")->fetchColumn();
                if ($cek <= 1) {
                    throw new Exception("Sistem memerlukan setidaknya 1 pengguna Admin. Tidak dapat menghapus admin terakhir.");
                }
            }

            $stmt = $pdo->prepare("DELETE FROM pengguna WHERE id_pengguna = :id");
            $stmt->execute([':id' => $id]);
            log_activity($pdo, $idActor, 'pengguna', (string)$id, 'DELETE', $oldData, null);
        }

        $pdo->commit();
        set_flash_message('success', 'Pengguna berhasil dihapus dari sistem.');
    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->getCode() == 23000) {
            set_flash_message('danger', 'Pengguna tidak dapat dihapus karena memiliki rekam data (misal: data mahasiswa yang terikat). Silakan ubah Status menjadi "Tidak Aktif" melalui tombol Edit.');
        } else {
            error_log($e->getMessage());
            set_flash_message('danger', 'Terjadi kesalahan sistem.');
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        // Detail error hanya ke log server, tidak ditampilkan ke user
        error_log('[PENGGUNA DELETE] ' . $e->getMessage());
        set_flash_message('warning', 'Pengguna tidak dapat dihapus. Silakan ubah Status menjadi "Tidak Aktif" melalui tombol Edit.');
    }
}

redirect(url('admin/pengguna/index.php'));

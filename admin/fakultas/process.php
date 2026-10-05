<?php
/**
 * admin/fakultas/process.php — Handler CRUD Fakultas
 */
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';
require_once dirname(__DIR__, 2) . '/app/audit.php';

require_role([1]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('admin/fakultas/index.php'));
    exit;
}

csrf_verify();

$action = sanitize($_POST['action'] ?? '');
$user = get_user_login();
$idPengguna = $user ? (int)($user['id_pengguna'] ?? 0) : null;

if ($action === 'create') {
    $idUniv = (int) ($_POST['id_universitas'] ?? 0);
    $kode   = strtoupper(sanitize($_POST['kode_fakultas'] ?? ''));
    $nama   = sanitize($_POST['nama_fakultas'] ?? '');

    if (empty($kode) || empty($nama) || $idUniv === 0) {
        set_flash_message('danger', 'Semua kolom wajib diisi.');
        redirect(url('admin/fakultas/create.php'));
        exit;
    }

    try {
        $pdo->beginTransaction();
        
        $sql = "INSERT INTO fakultas (id_universitas, kode_fakultas, nama_fakultas) VALUES (:id_univ, :kode, :nama)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_univ' => $idUniv,
            ':kode'    => $kode,
            ':nama'    => $nama
        ]);
        $newId = $pdo->lastInsertId();

        // Audit Log
        $stmtNew = $pdo->prepare("SELECT * FROM fakultas WHERE id_fakultas = :id");
        $stmtNew->execute([':id' => $newId]);
        $newData = $stmtNew->fetch();
        log_activity($pdo, $idPengguna, 'fakultas', (string)$newId, 'INSERT', null, $newData);

        $pdo->commit();
        set_flash_message('success', 'Fakultas berhasil ditambahkan.');
    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->getCode() == 23000) {
            set_flash_message('danger', 'Gagal: Kode Fakultas sudah digunakan.');
        } else {
            error_log($e->getMessage());
            set_flash_message('danger', 'Terjadi kesalahan sistem.');
        }
        redirect(url('admin/fakultas/create.php'));
        exit;
    }

} elseif ($action === 'update') {
    $id   = (int) ($_POST['id_fakultas'] ?? 0);
    $kode = strtoupper(sanitize($_POST['kode_fakultas'] ?? ''));
    $nama = sanitize($_POST['nama_fakultas'] ?? '');

    if (empty($kode) || empty($nama) || $id === 0) {
        set_flash_message('danger', 'Semua kolom wajib diisi.');
        redirect(url('admin/fakultas/edit.php?id=' . $id));
        exit;
    }

    try {
        $pdo->beginTransaction();

        $stmtOld = $pdo->prepare("SELECT * FROM fakultas WHERE id_fakultas = :id");
        $stmtOld->execute([':id' => $id]);
        $oldData = $stmtOld->fetch();

        if (!$oldData) throw new Exception("Data tidak ditemukan.");

        $sql = "UPDATE fakultas SET kode_fakultas = :kode, nama_fakultas = :nama WHERE id_fakultas = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':kode' => $kode,
            ':nama' => $nama,
            ':id'   => $id
        ]);

        $stmtNew = $pdo->prepare("SELECT * FROM fakultas WHERE id_fakultas = :id");
        $stmtNew->execute([':id' => $id]);
        $newData = $stmtNew->fetch();

        log_activity($pdo, $idPengguna, 'fakultas', (string)$id, 'UPDATE', $oldData, $newData);

        $pdo->commit();
        set_flash_message('success', 'Data Fakultas berhasil diperbarui.');
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('[FAKULTAS UPDATE] ' . $e->getMessage());
        if ($e->getCode() == 23000) {
            set_flash_message('danger', 'Gagal: Kode Fakultas sudah digunakan.');
        } else {
            set_flash_message('danger', 'Terjadi kesalahan sistem.');
        }
        redirect(url('admin/fakultas/edit.php?id=' . $id));
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        // Detail error hanya ke log server, tidak ditampilkan ke user
        error_log('[FAKULTAS UPDATE] ' . $e->getMessage());
        set_flash_message('danger', 'Terjadi kesalahan sistem saat memproses data.');
    }

} elseif ($action === 'delete') {
    $id = (int) ($_POST['id_fakultas'] ?? 0);

    try {
        $pdo->beginTransaction();

        $stmtOld = $pdo->prepare("SELECT * FROM fakultas WHERE id_fakultas = :id");
        $stmtOld->execute([':id' => $id]);
        $oldData = $stmtOld->fetch();

        if ($oldData) {
            $stmt = $pdo->prepare("DELETE FROM fakultas WHERE id_fakultas = :id");
            $stmt->execute([':id' => $id]);
            log_activity($pdo, $idPengguna, 'fakultas', (string)$id, 'DELETE', $oldData, null);
        }

        $pdo->commit();
        set_flash_message('success', 'Fakultas berhasil dihapus.');
    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->getCode() == 23000) {
            set_flash_message('danger', 'Gagal: Fakultas ini masih memiliki Program Studi atau terikat dengan pengguna/mahasiswa.');
        } else {
            set_flash_message('danger', 'Terjadi kesalahan sistem.');
        }
    }
}

redirect(url('admin/fakultas/index.php'));

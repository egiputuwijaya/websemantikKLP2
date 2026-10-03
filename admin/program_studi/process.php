<?php
/**
 * admin/program_studi/process.php — Handler CRUD Prodi
 */
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';
require_once dirname(__DIR__, 2) . '/app/audit.php';

require_role([1]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('admin/program_studi/index.php'));
    exit;
}

$action = sanitize($_POST['action'] ?? '');
$user = get_user_login();
$idPengguna = $user ? (int)($user['id_pengguna'] ?? 0) : null;

if ($action === 'create') {
    $idFakultas = (int) ($_POST['id_fakultas'] ?? 0);
    $kode       = strtoupper(sanitize($_POST['kode_program_studi'] ?? ''));
    $nama       = sanitize($_POST['nama_program_studi'] ?? '');
    $jenjang    = sanitize($_POST['jenjang'] ?? '');
    $status     = sanitize($_POST['status_aktif'] ?? 'Aktif');

    if (empty($kode) || empty($nama) || $idFakultas === 0 || empty($jenjang)) {
        set_flash_message('danger', 'Semua kolom bertanda * wajib diisi.');
        redirect(url('admin/program_studi/create.php'));
        exit;
    }

    try {
        $pdo->beginTransaction();
        
        $sql = "INSERT INTO program_studi (id_fakultas, kode_program_studi, nama_program_studi, jenjang, status_aktif) 
                VALUES (:id_fak, :kode, :nama, :jenjang, :status)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_fak'  => $idFakultas,
            ':kode'    => $kode,
            ':nama'    => $nama,
            ':jenjang' => $jenjang,
            ':status'  => $status
        ]);
        $newId = $pdo->lastInsertId();

        // Audit Log
        $stmtNew = $pdo->prepare("SELECT * FROM program_studi WHERE id_program_studi = :id");
        $stmtNew->execute([':id' => $newId]);
        $newData = $stmtNew->fetch();
        log_activity($pdo, $idPengguna, 'program_studi', (string)$newId, 'INSERT', null, $newData);

        $pdo->commit();
        set_flash_message('success', 'Program Studi berhasil ditambahkan.');
    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->getCode() == 23000) {
            set_flash_message('danger', 'Gagal: Kode Program Studi sudah digunakan pada Fakultas tersebut.');
        } else {
            error_log($e->getMessage());
            set_flash_message('danger', 'Terjadi kesalahan sistem.');
        }
        redirect(url('admin/program_studi/create.php'));
        exit;
    }

} elseif ($action === 'update') {
    $id         = (int) ($_POST['id_program_studi'] ?? 0);
    $idFakultas = (int) ($_POST['id_fakultas'] ?? 0);
    $kode       = strtoupper(sanitize($_POST['kode_program_studi'] ?? ''));
    $nama       = sanitize($_POST['nama_program_studi'] ?? '');
    $jenjang    = sanitize($_POST['jenjang'] ?? '');
    $status     = sanitize($_POST['status_aktif'] ?? 'Aktif');

    if (empty($kode) || empty($nama) || $id === 0 || $idFakultas === 0 || empty($jenjang)) {
        set_flash_message('danger', 'Semua kolom bertanda * wajib diisi.');
        redirect(url('admin/program_studi/edit.php?id=' . $id));
        exit;
    }

    try {
        $pdo->beginTransaction();

        $stmtOld = $pdo->prepare("SELECT * FROM program_studi WHERE id_program_studi = :id");
        $stmtOld->execute([':id' => $id]);
        $oldData = $stmtOld->fetch();

        if (!$oldData) throw new Exception("Data tidak ditemukan.");

        $sql = "UPDATE program_studi 
                SET id_fakultas = :id_fak, kode_program_studi = :kode, nama_program_studi = :nama, 
                    jenjang = :jenjang, status_aktif = :status 
                WHERE id_program_studi = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_fak'  => $idFakultas,
            ':kode'    => $kode,
            ':nama'    => $nama,
            ':jenjang' => $jenjang,
            ':status'  => $status,
            ':id'      => $id
        ]);

        $stmtNew = $pdo->prepare("SELECT * FROM program_studi WHERE id_program_studi = :id");
        $stmtNew->execute([':id' => $id]);
        $newData = $stmtNew->fetch();

        log_activity($pdo, $idPengguna, 'program_studi', (string)$id, 'UPDATE', $oldData, $newData);

        $pdo->commit();
        set_flash_message('success', 'Data Program Studi berhasil diperbarui.');
    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->getCode() == 23000) {
            set_flash_message('danger', 'Gagal: Kode Program Studi sudah digunakan pada Fakultas tersebut.');
        } else {
            error_log($e->getMessage());
            set_flash_message('danger', 'Terjadi kesalahan sistem.');
        }
        redirect(url('admin/program_studi/edit.php?id=' . $id));
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        set_flash_message('danger', $e->getMessage());
    }

} elseif ($action === 'delete') {
    $id = (int) ($_POST['id_program_studi'] ?? 0);

    try {
        $pdo->beginTransaction();

        $stmtOld = $pdo->prepare("SELECT * FROM program_studi WHERE id_program_studi = :id");
        $stmtOld->execute([':id' => $id]);
        $oldData = $stmtOld->fetch();

        if ($oldData) {
            $stmt = $pdo->prepare("DELETE FROM program_studi WHERE id_program_studi = :id");
            $stmt->execute([':id' => $id]);
            log_activity($pdo, $idPengguna, 'program_studi', (string)$id, 'DELETE', $oldData, null);
        }

        $pdo->commit();
        set_flash_message('success', 'Program Studi berhasil dihapus.');
    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->getCode() == 23000) {
            set_flash_message('danger', 'Gagal: Program Studi tidak dapat dihapus karena masih digunakan (terikat dengan akun pengguna atau mahasiswa). Anda bisa mengubah statusnya menjadi "Tidak Aktif" melalui fitur Edit.');
        } else {
            error_log($e->getMessage());
            set_flash_message('danger', 'Terjadi kesalahan sistem saat menghapus data.');
        }
    }
}

redirect(url('admin/program_studi/index.php'));

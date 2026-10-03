<?php
/**
 * admin/profil-saya/process.php — Handler Update Kontak Mahasiswa Mandiri
 */
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';
require_once dirname(__DIR__, 2) . '/app/audit.php';

require_role([5]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('admin/profil-saya/index.php'));
    exit;
}

csrf_verify();

$action = sanitize($_POST['action'] ?? '');
$userActive = get_user_login();
$idPengguna = (int)$userActive['id_pengguna'];

if ($action === 'update') {
    $tempatLahir = sanitize($_POST['tempat_lahir'] ?? '');
    $tanggalLahir = sanitize($_POST['tanggal_lahir'] ?? '');
    if (empty($tanggalLahir)) $tanggalLahir = null;
    $alamat = sanitize($_POST['alamat'] ?? '');
    
    $email = sanitize($_POST['email'] ?? null);
    $noHp  = sanitize($_POST['no_hp'] ?? null);

    try {
        $pdo->beginTransaction();

        // 1. Dapatkan data lama (Tabel Mahasiswa)
        $stmtMhsOld = $pdo->prepare("SELECT id_mahasiswa, tempat_lahir, tanggal_lahir, alamat FROM mahasiswa WHERE id_pengguna = :id");
        $stmtMhsOld->execute([':id' => $idPengguna]);
        $mhsOld = $stmtMhsOld->fetch();

        if (!$mhsOld) throw new Exception("Data mahasiswa induk tidak ditemukan.");
        $idMhs = $mhsOld['id_mahasiswa'];

        // 2. Dapatkan data lama (Tabel Pengguna)
        $stmtPgnOld = $pdo->prepare("SELECT id_pengguna, email, no_hp FROM pengguna WHERE id_pengguna = :id");
        $stmtPgnOld->execute([':id' => $idPengguna]);
        $pgnOld = $stmtPgnOld->fetch();

        // 3. Update tabel Mahasiswa
        $sqlMhs = "UPDATE mahasiswa SET tempat_lahir = :tempat, tanggal_lahir = :tgl, alamat = :alamat WHERE id_mahasiswa = :id_mhs";
        $stmt1 = $pdo->prepare($sqlMhs);
        $stmt1->execute([
            ':tempat' => $tempatLahir,
            ':tgl'    => $tanggalLahir,
            ':alamat' => $alamat,
            ':id_mhs' => $idMhs
        ]);

        // 4. Update tabel Pengguna
        $sqlPgn = "UPDATE pengguna SET email = :email, no_hp = :nohp WHERE id_pengguna = :id_pgn";
        $stmt2 = $pdo->prepare($sqlPgn);
        $stmt2->execute([
            ':email'  => $email ?: null,
            ':nohp'   => $noHp ?: null,
            ':id_pgn' => $idPengguna
        ]);

        // 5. Fetch Data Baru & Log Audit
        $stmtMhsNew = $pdo->prepare("SELECT id_mahasiswa, tempat_lahir, tanggal_lahir, alamat FROM mahasiswa WHERE id_mahasiswa = :id_mhs");
        $stmtMhsNew->execute([':id_mhs' => $idMhs]);
        $mhsNew = $stmtMhsNew->fetch();
        
        $stmtPgnNew = $pdo->prepare("SELECT id_pengguna, email, no_hp FROM pengguna WHERE id_pengguna = :id");
        $stmtPgnNew->execute([':id' => $idPengguna]);
        $pgnNew = $stmtPgnNew->fetch();

        // Log dua kali (satu untuk tabel mahasiswa, satu untuk tabel pengguna)
        log_activity($pdo, $idPengguna, 'mahasiswa', (string)$idMhs, 'UPDATE', $mhsOld, $mhsNew);
        log_activity($pdo, $idPengguna, 'pengguna', (string)$idPengguna, 'UPDATE', $pgnOld, $pgnNew);

        $pdo->commit();
        set_flash_message('success', 'Data Profil Akademik Anda berhasil diperbarui.');
    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->getCode() == 23000) {
            set_flash_message('danger', 'Gagal: Alamat Email tersebut sudah digunakan.');
        } else {
            error_log($e->getMessage());
            set_flash_message('danger', 'Terjadi kesalahan sistem database.');
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        set_flash_message('danger', $e->getMessage());
    }
}

redirect(url('admin/profil-saya/index.php'));

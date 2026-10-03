<?php
/**
 * admin/universitas/process.php — Handler UPDATE universitas
 */
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';
require_once dirname(__DIR__, 2) . '/app/audit.php';

require_role([1]); // Akses hanya untuk Admin

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('admin/universitas/index.php'));
    exit;
}

$action = sanitize($_POST['action'] ?? '');
$id     = (int) ($_POST['id_universitas'] ?? 0);

if ($action === 'update' && $id > 0) {
    // 1. Ambil data input
    $kode    = sanitize($_POST['kode_universitas'] ?? '');
    $nama    = sanitize($_POST['nama_universitas'] ?? '');
    $slogan  = sanitize($_POST['slogan'] ?? '');
    $alamat  = sanitize($_POST['alamat'] ?? '');
    $kota    = sanitize($_POST['kota'] ?? '');
    $prov    = sanitize($_POST['provinsi'] ?? '');
    $pos     = sanitize($_POST['kode_pos'] ?? '');
    $telp    = sanitize($_POST['telepon'] ?? '');
    $email   = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $website = filter_var($_POST['website'] ?? '', FILTER_SANITIZE_URL);

    // Validasi dasar
    if (empty($kode) || empty($nama)) {
        set_flash_message('danger', 'Kode dan Nama Universitas wajib diisi.');
        redirect(url('admin/universitas/edit.php'));
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 2. Ambil data lama untuk keperluan audit_log
        $stmtOld = $pdo->prepare("SELECT * FROM universitas WHERE id_universitas = :id LIMIT 1");
        $stmtOld->execute([':id' => $id]);
        $oldData = $stmtOld->fetch();

        if (!$oldData) {
            throw new Exception("Data tidak ditemukan.");
        }

        // 3. Update data
        $sql = "UPDATE universitas SET 
                kode_universitas = :kode, 
                nama_universitas = :nama, 
                slogan = :slogan, 
                alamat = :alamat, 
                kota = :kota, 
                provinsi = :prov, 
                kode_pos = :pos, 
                telepon = :telp, 
                email = :email, 
                website = :website 
                WHERE id_universitas = :id";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':kode'    => $kode,
            ':nama'    => $nama,
            ':slogan'  => empty($slogan) ? null : $slogan,
            ':alamat'  => empty($alamat) ? null : $alamat,
            ':kota'    => empty($kota) ? null : $kota,
            ':prov'    => empty($prov) ? null : $prov,
            ':pos'     => empty($pos) ? null : $pos,
            ':telp'    => empty($telp) ? null : $telp,
            ':email'   => empty($email) ? null : $email,
            ':website' => empty($website) ? null : $website,
            ':id'      => $id
        ]);

        // 4. Ambil data baru
        $stmtNew = $pdo->prepare("SELECT * FROM universitas WHERE id_universitas = :id LIMIT 1");
        $stmtNew->execute([':id' => $id]);
        $newData = $stmtNew->fetch();

        // 5. Catat ke audit log
        $user = get_user_login();
        $idPengguna = $user ? (int)($user['id_pengguna'] ?? 0) : null;
        log_activity($pdo, $idPengguna, 'universitas', (string)$id, 'UPDATE', $oldData, $newData);

        $pdo->commit();

        set_flash_message('success', 'Profil Universitas berhasil diperbarui.');
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('[UPDATE UNIVERSITAS ERROR] ' . $e->getMessage());
        
        // Cek constraint unik
        if ($e->getCode() == 23000) {
            set_flash_message('danger', 'Gagal: Kode Universitas tersebut sudah digunakan oleh data lain.');
        } else {
            set_flash_message('danger', 'Terjadi kesalahan sistem saat memperbarui data.');
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        set_flash_message('danger', $e->getMessage());
    }
} else {
    set_flash_message('danger', 'Aksi tidak valid.');
}

redirect(url('admin/universitas/index.php'));

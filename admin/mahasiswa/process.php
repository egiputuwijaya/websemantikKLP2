<?php
/**
 * ============================================================
 *  admin/mahasiswa/process.php — Handler CRUD Mahasiswa
 * ============================================================
 *  Aksi: CREATE | UPDATE | DELETE
 *  Keamanan:
 *   - check_role([1, 2]) untuk semua aksi tulis
 *   - PDO Prepared Statements
 *   - PDO Transaction (rollback jika akun pengguna gagal)
 *   - Cek keunikan NPM
 *   - log_activity() ke audit_log
 *   - Operator Prodi dibatasi pada prodi miliknya
 */

require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/audit.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';

require_role([1, 2]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash_message('warning', 'Metode request tidak valid.');
    redirect(url('admin/mahasiswa/index.php'));
    exit;
}

csrf_verify();

$user   = get_user_login();
$idRole = (int) ($user['id_role'] ?? 0);
$action = isset($_POST['action']) ? strtoupper(trim((string) $_POST['action'])) : '';

// ----------------------------------------------------------
// Helper: simpan input lama ke session (untuk pre-fill form)
// ----------------------------------------------------------
function keep_old(array $data): void
{
    $_SESSION['old_mahasiswa'] = $data;
}

// ----------------------------------------------------------
// Helper: cek keunikan NPM
// ----------------------------------------------------------
function npm_exists(PDO $pdo, string $npm, int $excludeId = 0): bool
{
    $sql = "SELECT COUNT(*) FROM mahasiswa WHERE npm = :npm";
    $params = [':npm' => $npm];
    if ($excludeId > 0) {
        $sql .= " AND id_mahasiswa != :id";
        $params[':id'] = $excludeId;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn() > 0;
}

// ==========================================================
//  ACTION: CREATE
// ==========================================================
if ($action === 'CREATE') {

    // Ambil & sanitasi input
    $input = [
        'npm'              => sanitize($_POST['npm'] ?? ''),
        'nama_mahasiswa'   => sanitize($_POST['nama_mahasiswa'] ?? ''),
        'jenis_kelamin'    => isset($_POST['jenis_kelamin']) ? (string) $_POST['jenis_kelamin'] : '',
        'tempat_lahir'     => sanitize($_POST['tempat_lahir'] ?? ''),
        'tanggal_lahir'    => isset($_POST['tanggal_lahir']) ? (string) $_POST['tanggal_lahir'] : '',
        'tanggal_masuk'    => isset($_POST['tanggal_masuk']) ? (string) $_POST['tanggal_masuk'] : '',
        'alamat'           => sanitize($_POST['alamat'] ?? ''),
        'status_mahasiswa' => isset($_POST['status_mahasiswa']) ? (string) $_POST['status_mahasiswa'] : '',
        'id_program_studi' => isset($_POST['id_program_studi']) ? (int) $_POST['id_program_studi'] : 0,
        'buat_akun'        => !empty($_POST['buat_akun']) ? 1 : 0,
    ];

    // Validasi wajib
    $errors = [];
    if ($input['npm'] === '') {
        $errors[] = 'NPM wajib diisi.';
    }
    if ($input['nama_mahasiswa'] === '') {
        $errors[] = 'Nama mahasiswa wajib diisi.';
    }
    if (!in_array($input['jenis_kelamin'], ['L', 'P'], true)) {
        $errors[] = 'Jenis kelamin tidak valid.';
    }
    if ($input['tanggal_masuk'] === '') {
        $errors[] = 'Tanggal masuk wajib diisi.';
    }
    if (!in_array($input['status_mahasiswa'], ['Aktif', 'Cuti', 'Lulus', 'Mengundurkan Diri', 'Drop Out', 'Tidak Aktif'], true)) {
        $errors[] = 'Status mahasiswa tidak valid.';
    }
    if ($input['id_program_studi'] <= 0) {
        $errors[] = 'Program studi wajib dipilih.';
    }

    // Operator Prodi: prodi harus miliknya
    if ($idRole === 2) {
        $prodiUser = (int) ($user['id_program_studi'] ?? 0);
        if ($prodiUser <= 0 || $input['id_program_studi'] !== $prodiUser) {
            $errors[] = 'Anda hanya dapat menambahkan mahasiswa pada program studi Anda sendiri.';
        }
    }

    // Cek NPM unik
    if ($input['npm'] !== '' && npm_exists($pdo, $input['npm'])) {
        $errors[] = 'NPM "' . $input['npm'] . '" sudah terdaftar. Gunakan NPM lain.';
    }

    if (!empty($errors)) {
        keep_old($input);
        set_flash_message('danger', implode(' ', $errors));
        redirect(url('admin/mahasiswa/create.php'));
        exit;
    }

    // ------------------------------------------------------
    // Transaksi: INSERT mahasiswa + opsional INSERT pengguna
    // ------------------------------------------------------
    try {
        $pdo->beginTransaction();

        // Ambil info prodi (untuk id_universitas & id_fakultas akun)
        $stmtProdi = $pdo->prepare("SELECT ps.id_fakultas, f.id_universitas
                                    FROM program_studi ps
                                    JOIN fakultas f ON f.id_fakultas = ps.id_fakultas
                                    WHERE ps.id_program_studi = :id");
        $stmtProdi->execute([':id' => $input['id_program_studi']]);
        $prodiInfo = $stmtProdi->fetch();

        if ($prodiInfo === false) {
            throw new RuntimeException('Program studi tidak ditemukan.');
        }

        // 1) Insert akun pengguna (jika dicentang)
        $idPengguna = null;
        if ($input['buat_akun'] === 1) {
            // Cek username (NPM) belum dipakai
            $stmtUser = $pdo->prepare("SELECT COUNT(*) FROM pengguna WHERE username = :u");
            $stmtUser->execute([':u' => $input['npm']]);
            if ((int) $stmtUser->fetchColumn() > 0) {
                throw new RuntimeException('Username "' . $input['npm'] . '" sudah digunakan pada tabel pengguna.');
            }

            $hash = password_hash($input['npm'], PASSWORD_DEFAULT);
            if ($hash === false) {
                throw new RuntimeException('Gagal membuat hash password.');
            }

            $sqlInsUser = "INSERT INTO pengguna
                    (id_role, id_universitas, id_fakultas, id_program_studi, username, password_hash, nama_lengkap, email, status_aktif)
                   VALUES
                    (:id_role, :id_universitas, :id_fakultas, :id_program_studi, :username, :password_hash, :nama_lengkap, :email, 'Aktif')";
            $stmtInsUser = $pdo->prepare($sqlInsUser);
            $stmtInsUser->execute([
                ':id_role'          => 5, // MAHASISWA
                ':id_universitas'   => (int) $prodiInfo['id_universitas'],
                ':id_fakultas'      => (int) $prodiInfo['id_fakultas'],
                ':id_program_studi' => $input['id_program_studi'],
                ':username'         => $input['npm'],
                ':password_hash'    => $hash,
                ':nama_lengkap'     => $input['nama_mahasiswa'],
                ':email'            => null,
            ]);
            $idPengguna = (int) $pdo->lastInsertId();
        }

        // 2) Insert mahasiswa
        $sqlInsMhs = "INSERT INTO mahasiswa
                    (id_pengguna, id_program_studi, npm, nama_mahasiswa, jenis_kelamin, tempat_lahir, tanggal_lahir, tanggal_masuk, alamat, status_mahasiswa)
                   VALUES
                    (:id_pengguna, :id_program_studi, :npm, :nama_mahasiswa, :jenis_kelamin, :tempat_lahir, :tanggal_lahir, :tanggal_masuk, :alamat, :status_mahasiswa)";
        $stmtInsMhs = $pdo->prepare($sqlInsMhs);
        $stmtInsMhs->execute([
            ':id_pengguna'      => $idPengguna,
            ':id_program_studi' => $input['id_program_studi'],
            ':npm'              => $input['npm'],
            ':nama_mahasiswa'   => $input['nama_mahasiswa'],
            ':jenis_kelamin'    => $input['jenis_kelamin'],
            ':tempat_lahir'     => $input['tempat_lahir'] !== '' ? $input['tempat_lahir'] : null,
            ':tanggal_lahir'    => $input['tanggal_lahir'] !== '' ? $input['tanggal_lahir'] : null,
            ':tanggal_masuk'    => $input['tanggal_masuk'],
            ':alamat'           => $input['alamat'] !== '' ? $input['alamat'] : null,
            ':status_mahasiswa' => $input['status_mahasiswa'],
        ]);
        $idMahasiswa = (int) $pdo->lastInsertId();

        $pdo->commit();

        // 3) Audit log (di luar transaction — tidak menggagalkan proses)
        log_activity(
            $pdo,
            (int) $user['id_pengguna'],
            'mahasiswa',
            (string) $idMahasiswa,
            'INSERT',
            null,
            [
                'npm'              => $input['npm'],
                'nama_mahasiswa'   => $input['nama_mahasiswa'],
                'id_program_studi' => $input['id_program_studi'],
                'status_mahasiswa' => $input['status_mahasiswa'],
                'buat_akun'        => $input['buat_akun'] === 1,
                'id_pengguna'      => $idPengguna,
            ]
        );

        $msg = 'Data mahasiswa "' . $input['nama_mahasiswa'] . '" berhasil ditambahkan.';
        if ($input['buat_akun'] === 1) {
            $msg .= ' Akun login dibuat (username: ' . $input['npm'] . ', password default: ' . $input['npm'] . ').';
        }
        set_flash_message('success', $msg);
        redirect(url('admin/mahasiswa/index.php'));
        exit;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('[MAHASISWA CREATE] ' . $e->getMessage());

        // Deteksi duplicate key
        if ((int) $e->getCode() === 23000 && stripos($e->getMessage(), 'npm') !== false) {
            $msg = 'NPM "' . $input['npm'] . '" sudah terdaftar. Gunakan NPM lain.';
        } elseif ((int) $e->getCode() === 23000 && stripos($e->getMessage(), 'username') !== false) {
            $msg = 'Username "' . $input['npm'] . '" sudah digunakan pada tabel pengguna.';
        } else {
            $msg = 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage();
        }

        keep_old($input);
        set_flash_message('danger', $msg);
        redirect(url('admin/mahasiswa/create.php'));
        exit;

    } catch (RuntimeException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        keep_old($input);
        set_flash_message('danger', $e->getMessage());
        redirect(url('admin/mahasiswa/create.php'));
        exit;
    }
}

// ==========================================================
//  ACTION: UPDATE
// ==========================================================
if ($action === 'UPDATE') {

    $idMahasiswa = isset($_POST['id_mahasiswa']) ? (int) $_POST['id_mahasiswa'] : 0;
    if ($idMahasiswa <= 0) {
        set_flash_message('danger', 'ID mahasiswa tidak valid.');
        redirect(url('admin/mahasiswa/index.php'));
        exit;
    }

    // Data lama (untuk audit)
    $stmtOld = $pdo->prepare("SELECT * FROM mahasiswa WHERE id_mahasiswa = :id");
    $stmtOld->execute([':id' => $idMahasiswa]);
    $oldRow = $stmtOld->fetch();

    if ($oldRow === false) {
        set_flash_message('danger', 'Data mahasiswa tidak ditemukan.');
        redirect(url('admin/mahasiswa/index.php'));
        exit;
    }

    // Operator: hanya prodi sendiri
    if ($idRole === 2) {
        $prodiUser = (int) ($user['id_program_studi'] ?? 0);
        if ($prodiUser <= 0 || (int) $oldRow['id_program_studi'] !== $prodiUser) {
            set_flash_message('danger', 'Anda hanya dapat mengedit data mahasiswa pada program studi Anda.');
            redirect(url('admin/mahasiswa/index.php'));
            exit;
        }
    }

    // Input baru
    $input = [
        'npm'              => sanitize($_POST['npm'] ?? ''),
        'nama_mahasiswa'   => sanitize($_POST['nama_mahasiswa'] ?? ''),
        'jenis_kelamin'    => isset($_POST['jenis_kelamin']) ? (string) $_POST['jenis_kelamin'] : '',
        'tempat_lahir'     => sanitize($_POST['tempat_lahir'] ?? ''),
        'tanggal_lahir'    => isset($_POST['tanggal_lahir']) ? (string) $_POST['tanggal_lahir'] : '',
        'tanggal_masuk'    => isset($_POST['tanggal_masuk']) ? (string) $_POST['tanggal_masuk'] : '',
        'alamat'           => sanitize($_POST['alamat'] ?? ''),
        'status_mahasiswa' => isset($_POST['status_mahasiswa']) ? (string) $_POST['status_mahasiswa'] : '',
        'id_program_studi' => isset($_POST['id_program_studi']) ? (int) $_POST['id_program_studi'] : 0,
    ];

    $errors = [];
    if ($input['npm'] === '') {
        $errors[] = 'NPM wajib diisi.';
    }
    if ($input['nama_mahasiswa'] === '') {
        $errors[] = 'Nama mahasiswa wajib diisi.';
    }
    if (!in_array($input['jenis_kelamin'], ['L', 'P'], true)) {
        $errors[] = 'Jenis kelamin tidak valid.';
    }
    if ($input['tanggal_masuk'] === '') {
        $errors[] = 'Tanggal masuk wajib diisi.';
    }
    if (!in_array($input['status_mahasiswa'], ['Aktif', 'Cuti', 'Lulus', 'Mengundurkan Diri', 'Drop Out', 'Tidak Aktif'], true)) {
        $errors[] = 'Status mahasiswa tidak valid.';
    }
    if ($input['id_program_studi'] <= 0) {
        $errors[] = 'Program studi wajib dipilih.';
    }

    // Operator: prodi harus miliknya
    if ($idRole === 2 && $input['id_program_studi'] !== (int) ($user['id_program_studi'] ?? 0)) {
        $errors[] = 'Anda hanya dapat mengubah data ke program studi Anda sendiri.';
    }

    // NPM unik (kecuali dirinya sendiri)
    if ($input['npm'] !== '' && npm_exists($pdo, $input['npm'], $idMahasiswa)) {
        $errors[] = 'NPM "' . $input['npm'] . '" sudah digunakan mahasiswa lain.';
    }

    if (!empty($errors)) {
        keep_old($input + ['id_mahasiswa' => $idMahasiswa]);
        set_flash_message('danger', implode(' ', $errors));
        redirect(url('admin/mahasiswa/edit.php?id=' . $idMahasiswa));
        exit;
    }

    try {
        $pdo->beginTransaction();

        $sqlUpd = "UPDATE mahasiswa SET
                    id_program_studi = :id_program_studi,
                    npm = :npm,
                    nama_mahasiswa = :nama_mahasiswa,
                    jenis_kelamin = :jenis_kelamin,
                    tempat_lahir = :tempat_lahir,
                    tanggal_lahir = :tanggal_lahir,
                    tanggal_masuk = :tanggal_masuk,
                    alamat = :alamat,
                    status_mahasiswa = :status_mahasiswa
                   WHERE id_mahasiswa = :id_mahasiswa";
        $stmtUpd = $pdo->prepare($sqlUpd);
        $stmtUpd->execute([
            ':id_program_studi' => $input['id_program_studi'],
            ':npm'              => $input['npm'],
            ':nama_mahasiswa'   => $input['nama_mahasiswa'],
            ':jenis_kelamin'    => $input['jenis_kelamin'],
            ':tempat_lahir'     => $input['tempat_lahir'] !== '' ? $input['tempat_lahir'] : null,
            ':tanggal_lahir'    => $input['tanggal_lahir'] !== '' ? $input['tanggal_lahir'] : null,
            ':tanggal_masuk'    => $input['tanggal_masuk'],
            ':alamat'           => $input['alamat'] !== '' ? $input['alamat'] : null,
            ':status_mahasiswa' => $input['status_mahasiswa'],
            ':id_mahasiswa'     => $idMahasiswa,
        ]);

        // Jika NPM berubah & ada akun pengguna terkait, sinkronkan username
        // (opsional — hanya jika username lama == NPM lama)
        if ($input['npm'] !== $oldRow['npm'] && !empty($oldRow['id_pengguna'])) {
            $stmtSync = $pdo->prepare("UPDATE pengguna SET username = :new_u WHERE id_pengguna = :id AND username = :old_u");
            $stmtSync->execute([
                ':new_u' => $input['npm'],
                ':id'    => (int) $oldRow['id_pengguna'],
                ':old_u' => $oldRow['npm'],
            ]);
        }

        $pdo->commit();

        // Audit: bandingkan data lama vs baru (field penting saja)
        $dataBaru = [
            'npm'              => $input['npm'],
            'nama_mahasiswa'   => $input['nama_mahasiswa'],
            'jenis_kelamin'    => $input['jenis_kelamin'],
            'status_mahasiswa' => $input['status_mahasiswa'],
            'id_program_studi' => $input['id_program_studi'],
        ];
        $dataLama = [
            'npm'              => $oldRow['npm'],
            'nama_mahasiswa'   => $oldRow['nama_mahasiswa'],
            'jenis_kelamin'    => $oldRow['jenis_kelamin'],
            'status_mahasiswa' => $oldRow['status_mahasiswa'],
            'id_program_studi' => (int) $oldRow['id_program_studi'],
        ];

        log_activity(
            $pdo,
            (int) $user['id_pengguna'],
            'mahasiswa',
            (string) $idMahasiswa,
            'UPDATE',
            $dataLama,
            $dataBaru
        );

        set_flash_message('success', 'Data mahasiswa "' . $input['nama_mahasiswa'] . '" berhasil diperbarui.');
        redirect(url('admin/mahasiswa/index.php'));
        exit;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('[MAHASISWA UPDATE] ' . $e->getMessage());

        if ((int) $e->getCode() === 23000 && stripos($e->getMessage(), 'npm') !== false) {
            $msg = 'NPM "' . $input['npm'] . '" sudah digunakan mahasiswa lain.';
        } else {
            $msg = 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage();
        }

        keep_old($input + ['id_mahasiswa' => $idMahasiswa]);
        set_flash_message('danger', $msg);
        redirect(url('admin/mahasiswa/edit.php?id=' . $idMahasiswa));
        exit;
    }
}

// ==========================================================
//  ACTION: DELETE
// ==========================================================
if ($action === 'DELETE') {

    $idMahasiswa = isset($_POST['id_mahasiswa']) ? (int) $_POST['id_mahasiswa'] : 0;
    if ($idMahasiswa <= 0) {
        set_flash_message('danger', 'ID mahasiswa tidak valid.');
        redirect(url('admin/mahasiswa/index.php'));
        exit;
    }

    // Ambil data lama
    $stmtOld = $pdo->prepare("SELECT * FROM mahasiswa WHERE id_mahasiswa = :id");
    $stmtOld->execute([':id' => $idMahasiswa]);
    $oldRow = $stmtOld->fetch();

    if ($oldRow === false) {
        set_flash_message('danger', 'Data mahasiswa tidak ditemukan.');
        redirect(url('admin/mahasiswa/index.php'));
        exit;
    }

    // Operator: hanya prodi sendiri
    if ($idRole === 2) {
        $prodiUser = (int) ($user['id_program_studi'] ?? 0);
        if ($prodiUser <= 0 || (int) $oldRow['id_program_studi'] !== $prodiUser) {
            set_flash_message('danger', 'Anda hanya dapat menghapus data mahasiswa pada program studi Anda.');
            redirect(url('admin/mahasiswa/index.php'));
            exit;
        }
    }

    try {
        $pdo->beginTransaction();

        // Jika ada akun pengguna terkait dengan role MAHASISWA (5),
        // hapus juga akun tersebut agar NPM bisa dipakai ulang.
        $hapusAkun = false;
        if (!empty($oldRow['id_pengguna'])) {
            $stmtRole = $pdo->prepare("SELECT id_role FROM pengguna WHERE id_pengguna = :id");
            $stmtRole->execute([':id' => (int) $oldRow['id_pengguna']]);
            $rolePengguna = (int) ($stmtRole->fetchColumn() ?: 0);

            if ($rolePengguna === 5) {
                $stmtDelUser = $pdo->prepare("DELETE FROM pengguna WHERE id_pengguna = :id");
                $stmtDelUser->execute([':id' => (int) $oldRow['id_pengguna']]);
                $hapusAkun = true;
            } else {
                // Putuskan relasi saja (akun non-mahasiswa tetap ada)
                $stmtNull = $pdo->prepare("UPDATE mahasiswa SET id_pengguna = NULL WHERE id_mahasiswa = :id");
                // (row akan dihapus sebentar lagi — tidak wajib, tapi aman)
                $stmtNull->execute([':id' => $idMahasiswa]);
            }
        }

        // Hapus mahasiswa
        $stmtDel = $pdo->prepare("DELETE FROM mahasiswa WHERE id_mahasiswa = :id");
        $stmtDel->execute([':id' => $idMahasiswa]);

        $pdo->commit();

        // Audit
        log_activity(
            $pdo,
            (int) $user['id_pengguna'],
            'mahasiswa',
            (string) $idMahasiswa,
            'DELETE',
            [
                'npm'              => $oldRow['npm'],
                'nama_mahasiswa'   => $oldRow['nama_mahasiswa'],
                'id_program_studi' => (int) $oldRow['id_program_studi'],
                'status_mahasiswa' => $oldRow['status_mahasiswa'],
                'akun_dihapus'     => $hapusAkun,
            ],
            null
        );

        $msg = 'Data mahasiswa "' . $oldRow['nama_mahasiswa'] . '" (' . $oldRow['npm'] . ') berhasil dihapus.';
        if ($hapusAkun) {
            $msg .= ' Akun login terkait juga telah dihapus.';
        }
        set_flash_message('success', $msg);
        redirect(url('admin/mahasiswa/index.php'));
        exit;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('[MAHASISWA DELETE] ' . $e->getMessage());
        set_flash_message('danger', 'Gagal menghapus data mahasiswa. ' . $e->getMessage());
        redirect(url('admin/mahasiswa/index.php'));
        exit;
    }
}

// ==========================================================
//  ACTION tidak dikenal
// ==========================================================
set_flash_message('warning', 'Aksi tidak dikenal.');
redirect(url('admin/mahasiswa/index.php'));
exit;

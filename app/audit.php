<?php
/**
 * ============================================================
 *  app/audit.php — Pencatatan otomatis ke tabel audit_log
 * ============================================================
 *  Setiap aksi penting (LOGIN, LOGOUT, INSERT, UPDATE, DELETE, VIEW)
 *  dicatat beserta IP address dan user agent pengguna.
 */

/**
 * Catat aktivitas pengguna ke tabel `audit_log`.
 *
 * @param PDO         $pdo         Koneksi PDO aktif
 * @param int|null    $id_pengguna ID pengguna (null untuk aksi sistem/anonim)
 * @param string      $tabel_nama  Nama tabel yang disentuh (mis. 'pengguna', 'mahasiswa')
 * @param string|null $record_id   ID record yang disentuh (opsional)
 * @param string      $aksi        LOGIN | LOGOUT | INSERT | UPDATE | DELETE | VIEW
 * @param mixed       $data_lama   Data lama (array/JSON) — untuk UPDATE/DELETE
 * @param mixed       $data_baru   Data baru (array/JSON) — untuk INSERT/UPDATE
 *
 * @return bool  true jika log tersimpan, false jika gagal (tidak melempar exception)
 */
function log_activity(
    PDO $pdo,
    ?int $id_pengguna,
    string $tabel_nama,
    ?string $record_id,
    string $aksi,
    $data_lama = null,
    $data_baru = null
): bool {
    try {
        // Ambil IP address
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        // Ambil user agent (potong jika terlalu panjang)
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if ($user_agent !== '' && strlen($user_agent) > 500) {
            $user_agent = substr($user_agent, 0, 500);
        }

        // Serialisasi data ke JSON jika berupa array/objek
        $data_lama_json = null;
        if ($data_lama !== null) {
            $data_lama_json = is_string($data_lama)
                ? $data_lama
                : json_encode($data_lama, JSON_UNESCAPED_UNICODE);
        }

        $data_baru_json = null;
        if ($data_baru !== null) {
            $data_baru_json = is_string($data_baru)
                ? $data_baru
                : json_encode($data_baru, JSON_UNESCAPED_UNICODE);
        }

        // Waktu sekarang (zona waktu app sudah diset di config/app.php)
        $waktu = date('Y-m-d H:i:s');

        $sql = "INSERT INTO audit_log
                    (id_pengguna, tabel_nama, record_id, aksi, data_lama, data_baru, ip_address, user_agent, waktu)
                VALUES
                    (:id_pengguna, :tabel_nama, :record_id, :aksi, :data_lama, :data_baru, :ip_address, :user_agent, :waktu)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_pengguna' => $id_pengguna,
            ':tabel_nama'  => $tabel_nama,
            ':record_id'   => $record_id,
            ':aksi'        => $aksi,
            ':data_lama'   => $data_lama_json,
            ':data_baru'   => $data_baru_json,
            ':ip_address'  => $ip_address,
            ':user_agent'  => $user_agent,
            ':waktu'       => $waktu,
        ]);

        return true;
    } catch (PDOException $e) {
        // Log audit tidak boleh menggagalkan proses utama
        error_log('[AUDIT ERROR] Gagal menulis audit_log: ' . $e->getMessage());
        return false;
    }
}

/**
 * Helper tambahan: ambil data aman pengguna dari session
 * untuk disertakan dalam log (tanpa password_hash).
 *
 * @param  array|null $user Data pengguna dari $_SESSION['user']
 * @return array|null Array data aman atau null
 */
function audit_safe_user(?array $user): ?array
{
    if ($user === null) {
        return null;
    }
    return [
        'id_pengguna'   => $user['id_pengguna'] ?? null,
        'username'      => $user['username'] ?? null,
        'nama_lengkap'  => $user['nama_lengkap'] ?? null,
        'id_role'       => $user['id_role'] ?? null,
        'kode_role'     => $user['kode_role'] ?? null,
    ];
}

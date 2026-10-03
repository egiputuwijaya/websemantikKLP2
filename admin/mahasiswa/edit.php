<?php
/**
 * ============================================================
 *  admin/mahasiswa/edit.php — Form Edit Data Mahasiswa
 * ============================================================
 *  Akses: Admin (1) & Operator Prodi (2).
 *  Parameter: ?id=<id_mahasiswa>
 */

require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/audit.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';

require_role([1, 2]);

$user   = get_user_login();
$idRole = (int) ($user['id_role'] ?? 0);

$idMahasiswa = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($idMahasiswa <= 0) {
    set_flash_message('danger', 'ID mahasiswa tidak valid.');
    redirect(url('admin/mahasiswa/index.php'));
    exit;
}

// Ambil data mahasiswa dari tabel asli (untuk edit)
$stmt = $pdo->prepare("SELECT m.*, ps.nama_program_studi, ps.jenjang, ps.id_fakultas, f.nama_fakultas
                       FROM mahasiswa m
                       JOIN program_studi ps ON ps.id_program_studi = m.id_program_studi
                       JOIN fakultas f ON f.id_fakultas = ps.id_fakultas
                       WHERE m.id_mahasiswa = :id");
$stmt->execute([':id' => $idMahasiswa]);
$mahasiswa = $stmt->fetch();

if ($mahasiswa === false) {
    set_flash_message('danger', 'Data mahasiswa tidak ditemukan.');
    redirect(url('admin/mahasiswa/index.php'));
    exit;
}

// Operator Prodi: hanya boleh edit data prodi miliknya
$lockedProdiId = null;
$lockedProdiName = '';
if ($idRole === 2) {
    if (empty($user['id_program_studi']) || (int) $user['id_program_studi'] !== (int) $mahasiswa['id_program_studi']) {
        set_flash_message('danger', 'Anda hanya dapat mengedit data mahasiswa pada program studi Anda sendiri.');
        redirect(url('admin/mahasiswa/index.php'));
        exit;
    }
    $lockedProdiId = (int) $user['id_program_studi'];
    $lockedProdiName = $mahasiswa['nama_program_studi'] ?? 'Prodi Saya';
}

// Daftar prodi
$prodiList = [];
try {
    if ($lockedProdiId) {
        $stmt = $pdo->prepare("SELECT id_program_studi, nama_program_studi, jenjang FROM program_studi WHERE id_program_studi = :id");
        $stmt->execute([':id' => $lockedProdiId]);
    } else {
        $stmt = $pdo->query("SELECT ps.id_program_studi, ps.nama_program_studi, ps.jenjang, f.nama_fakultas
                             FROM program_studi ps
                             JOIN fakultas f ON f.id_fakultas = ps.id_fakultas
                             WHERE ps.status_aktif = 'Aktif'
                             ORDER BY f.nama_fakultas, ps.nama_program_studi");
    }
    $prodiList = $stmt->fetchAll();
} catch (PDOException $e) {
    $prodiList = [];
}

// Data awal (override dengan session old jika ada)
$old = $_SESSION['old_mahasiswa'] ?? null;
unset($_SESSION['old_mahasiswa']);
if ($old === null) {
    $old = [
        'npm'               => $mahasiswa['npm'],
        'nama_mahasiswa'    => $mahasiswa['nama_mahasiswa'],
        'jenis_kelamin'     => $mahasiswa['jenis_kelamin'],
        'tempat_lahir'      => $mahasiswa['tempat_lahir'],
        'tanggal_lahir'     => $mahasiswa['tanggal_lahir'],
        'tanggal_masuk'     => $mahasiswa['tanggal_masuk'],
        'alamat'            => $mahasiswa['alamat'],
        'status_mahasiswa'  => $mahasiswa['status_mahasiswa'],
        'id_program_studi'  => $mahasiswa['id_program_studi'],
    ];
}

$pageTitle  = 'Edit Mahasiswa';
$activeMenu = 'mahasiswa';
?>

<?php require_once dirname(__DIR__, 2) . '/layouts/admin/header.php'; ?>

<!-- Breadcrumb -->
<nav class="mb-5" aria-label="Breadcrumb">
    <ol class="flex items-center gap-2 text-xs text-slate-500 flex-wrap">
        <li>
            <a href="<?= url('admin/dashboard/index.php') ?>" class="hover:text-academic-700 font-medium">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>
        </li>
        <li aria-hidden="true"><i class="fa-solid fa-chevron-right text-[9px]"></i></li>
        <li>
            <a href="<?= url('admin/mahasiswa/index.php') ?>" class="hover:text-academic-700 font-medium">Data Mahasiswa</a>
        </li>
        <li aria-hidden="true"><i class="fa-solid fa-chevron-right text-[9px]"></i></li>
        <li class="text-slate-900 font-semibold">Edit</li>
    </ol>
</nav>

<div class="flex items-center justify-between gap-4 mb-6 flex-wrap">
    <div>
        <h2 class="font-heading font-bold text-xl text-slate-900">Edit Data Mahasiswa</h2>
        <p class="text-sm text-slate-500 mt-1">
            NPM:
            <span class="font-mono font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded">
                <?= htmlspecialchars($mahasiswa['npm'], ENT_QUOTES, 'UTF-8') ?>
            </span>
        </p>
    </div>
    <div class="flex gap-2">
        <a href="<?= url('admin/mahasiswa/detail.php?id=' . $idMahasiswa) ?>"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-md border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-semibold transition-colors">
            <i class="fa-solid fa-eye"></i>
            Detail
        </a>
        <a href="<?= url('admin/mahasiswa/index.php') ?>"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-md border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-semibold transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            Kembali
        </a>
    </div>
</div>

<!-- Form -->
<div class="bg-white rounded-md shadow-sm border border-slate-200">
    <form action="<?= url('admin/mahasiswa/process.php') ?>" method="POST" class="p-6 sm:p-8">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="UPDATE">
        <input type="hidden" name="id_mahasiswa" value="<?= (int) $mahasiswa['id_mahasiswa'] ?>">

        <!-- Identitas -->
        <fieldset class="mb-8">
            <legend class="font-heading font-bold text-slate-900 flex items-center gap-2 mb-5">
                <span class="w-8 h-8 rounded-lg bg-academic-100 text-academic-700 flex items-center justify-center">
                    <i class="fa-solid fa-id-card text-sm"></i>
                </span>
                Identitas Mahasiswa
            </legend>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label for="npm" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        NPM <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="npm" id="npm" required maxlength="30"
                           value="<?= htmlspecialchars($old['npm'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full px-4 py-2.5 rounded-md border border-slate-200 bg-slate-50 text-sm focus:border-academic-500 focus:bg-white focus:ring-2 focus:ring-academic-100 transition-colors">
                </div>

                <div>
                    <label for="nama_mahasiswa" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_mahasiswa" id="nama_mahasiswa" required maxlength="200"
                           value="<?= htmlspecialchars($old['nama_mahasiswa'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full px-4 py-2.5 rounded-md border border-slate-200 bg-slate-50 text-sm focus:border-academic-500 focus:bg-white focus:ring-2 focus:ring-academic-100 transition-colors">
                </div>

                <div>
                    <label for="jenis_kelamin" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Jenis Kelamin <span class="text-red-500">*</span>
                    </label>
                    <select name="jenis_kelamin" id="jenis_kelamin" required
                            class="w-full px-4 py-2.5 rounded-md border border-slate-200 bg-slate-50 text-sm focus:border-academic-500 focus:bg-white focus:ring-2 focus:ring-academic-100 transition-colors">
                        <option value="L" <?= ($old['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="P" <?= ($old['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>

                <div>
                    <label for="status_mahasiswa" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Status Mahasiswa <span class="text-red-500">*</span>
                    </label>
                    <select name="status_mahasiswa" id="status_mahasiswa" required
                            class="w-full px-4 py-2.5 rounded-md border border-slate-200 bg-slate-50 text-sm focus:border-academic-500 focus:bg-white focus:ring-2 focus:ring-academic-100 transition-colors">
                        <?php foreach (['Aktif', 'Cuti', 'Lulus', 'Mengundurkan Diri', 'Drop Out', 'Tidak Aktif'] as $s): ?>
                            <option value="<?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?>"
                                <?= (($old['status_mahasiswa'] ?? '') === $s) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>
        </fieldset>

        <!-- Kelahiran -->
        <fieldset class="mb-8">
            <legend class="font-heading font-bold text-slate-900 flex items-center gap-2 mb-5">
                <span class="w-8 h-8 rounded-lg bg-elegant-100 text-elegant-700 flex items-center justify-center">
                    <i class="fa-solid fa-cake-candles text-sm"></i>
                </span>
                Data Kelahiran
            </legend>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="tempat_lahir" class="block text-sm font-semibold text-slate-700 mb-1.5">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" id="tempat_lahir" maxlength="100"
                           value="<?= htmlspecialchars($old['tempat_lahir'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full px-4 py-2.5 rounded-md border border-slate-200 bg-slate-50 text-sm focus:border-academic-500 focus:bg-white focus:ring-2 focus:ring-academic-100 transition-colors">
                </div>
                <div>
                    <label for="tanggal_lahir" class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" id="tanggal_lahir"
                           value="<?= htmlspecialchars($old['tanggal_lahir'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full px-4 py-2.5 rounded-md border border-slate-200 bg-slate-50 text-sm focus:border-academic-500 focus:bg-white focus:ring-2 focus:ring-academic-100 transition-colors">
                </div>
            </div>
        </fieldset>

        <!-- Akademik -->
        <fieldset class="mb-8">
            <legend class="font-heading font-bold text-slate-900 flex items-center gap-2 mb-5">
                <span class="w-8 h-8 rounded-lg bg-slate-900 text-white flex items-center justify-center">
                    <i class="fa-solid fa-graduation-cap text-sm"></i>
                </span>
                Data Akademik
            </legend>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="tanggal_masuk" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Tanggal Masuk <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal_masuk" id="tanggal_masuk" required
                           value="<?= htmlspecialchars($old['tanggal_masuk'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full px-4 py-2.5 rounded-md border border-slate-200 bg-slate-50 text-sm focus:border-academic-500 focus:bg-white focus:ring-2 focus:ring-academic-100 transition-colors">
                </div>

                <div>
                    <label for="id_program_studi" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Program Studi <span class="text-red-500">*</span>
                    </label>
                    <?php if ($lockedProdiId): ?>
                        <input type="text" value="<?= htmlspecialchars($lockedProdiName, ENT_QUOTES, 'UTF-8') ?>" disabled
                               class="w-full px-4 py-2.5 rounded-md border border-slate-200 bg-slate-100 text-sm text-slate-500">
                        <input type="hidden" name="id_program_studi" value="<?= $lockedProdiId ?>">
                    <?php else: ?>
                        <select name="id_program_studi" id="id_program_studi" required
                                class="w-full px-4 py-2.5 rounded-md border border-slate-200 bg-slate-50 text-sm focus:border-academic-500 focus:bg-white focus:ring-2 focus:ring-academic-100 transition-colors">
                            <?php foreach ($prodiList as $p): ?>
                                <option value="<?= (int) $p['id_program_studi'] ?>"
                                    <?= ((int) ($old['id_program_studi'] ?? 0)) === (int) $p['id_program_studi'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nama_program_studi'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                    (<?= htmlspecialchars($p['jenjang'] ?? '', ENT_QUOTES, 'UTF-8') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-5">
                <label for="alamat" class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat</label>
                <textarea name="alamat" id="alamat" rows="3" maxlength="1000"
                          class="w-full px-4 py-2.5 rounded-md border border-slate-200 bg-slate-50 text-sm focus:border-academic-500 focus:bg-white focus:ring-2 focus:ring-academic-100 transition-colors resize-y"><?= htmlspecialchars($old['alamat'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </fieldset>

        <!-- Info akun -->
        <div class="mb-6 p-4 rounded-md bg-slate-50 border border-slate-200 flex items-start gap-3">
            <i class="fa-solid fa-circle-info text-academic-600 mt-0.5"></i>
            <p class="text-xs text-slate-600 leading-relaxed">
                Perubahan NPM tidak memperbarui username akun login yang sudah ada secara otomatis.
                Jika akun login perlu disesuaikan, lakukan melalui modul <strong>Data Pengguna</strong> (Admin).
            </p>
        </div>

        <!-- Tombol aksi -->
        <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
            <button type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3 rounded-md bg-academic-600 hover:bg-academic-700 text-white font-bold text-sm transition-colors focus:outline-none focus:ring-4 focus:ring-academic-100">
                <i class="fa-solid fa-floppy-disk"></i>
                Simpan Perubahan
            </button>
            <a href="<?= url('admin/mahasiswa/index.php') ?>"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3 rounded-md border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-sm transition-colors">
                <i class="fa-solid fa-xmark"></i>
                Batal
            </a>
        </div>

    </form>
</div>

<?php require_once dirname(__DIR__, 2) . '/layouts/admin/footer.php'; ?>

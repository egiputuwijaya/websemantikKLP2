<?php
/**
 * ============================================================
 *  admin/mahasiswa/detail.php — Profil Lengkap Mahasiswa
 * ============================================================
 *  Data dari view v_profil_mahasiswa.
 *  Akses: semua role internal (1-5), dengan pembatasan data
 *  untuk mahasiswa (hanya miliknya sendiri).
 */

require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/audit.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';

check_auth();

$user   = get_user_login();
$idRole = (int) ($user['id_role'] ?? 0);
$canManage = in_array($idRole, [1, 2], true);

$idMahasiswa = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($idMahasiswa <= 0) {
    set_flash_message('danger', 'ID mahasiswa tidak valid.');
    redirect(url('admin/mahasiswa/index.php'));
    exit;
}

// Ambil profil dari VIEW
$stmt = $pdo->prepare("SELECT * FROM v_profil_mahasiswa WHERE id_mahasiswa = :id");
$stmt->execute([':id' => $idMahasiswa]);
$mhs = $stmt->fetch();

if ($mhs === false) {
    set_flash_message('danger', 'Data mahasiswa tidak ditemukan.');
    redirect(url('admin/mahasiswa/index.php'));
    exit;
}

// Mahasiswa (role 5): hanya boleh lihat datanya sendiri
if ($idRole === 5) {
    $npmUser = $user['username'] ?? '';
    if ($mhs['npm'] !== $npmUser) {
        set_flash_message('danger', 'Anda hanya dapat melihat data profil sendiri.');
        redirect(url('admin/dashboard/index.php'));
        exit;
    }
}

// Operator Prodi: hanya prodi sendiri
if ($idRole === 2 && !empty($user['id_program_studi'])) {
    // Cek via join ke mahasiswa untuk id_program_studi
    $stmtCek = $pdo->prepare("SELECT id_program_studi FROM mahasiswa WHERE id_mahasiswa = :id");
    $stmtCek->execute([':id' => $idMahasiswa]);
    $idProdiMhs = (int) ($stmtCek->fetchColumn() ?: 0);
    if ($idProdiMhs !== (int) $user['id_program_studi']) {
        set_flash_message('danger', 'Data di luar program studi Anda tidak dapat diakses.');
        redirect(url('admin/mahasiswa/index.php'));
        exit;
    }
}

// Info akun login terkait (jika ada)
$akun = null;
try {
    $stmtAkun = $pdo->prepare("SELECT p.id_pengguna, p.username, p.email, p.status_aktif, p.last_login, r.nama_role
                               FROM pengguna p
                               LEFT JOIN roles r ON r.id_role = p.id_role
                               JOIN mahasiswa m ON m.id_pengguna = p.id_pengguna
                               WHERE m.id_mahasiswa = :id");
    $stmtAkun->execute([':id' => $idMahasiswa]);
    $akun = $stmtAkun->fetch() ?: null;
} catch (PDOException $e) {
    $akun = null;
}

// Helper format
function format_tanggal(?string $tgl): string
{
    if (!$tgl || $tgl === '' || $tgl === '0000-00-00') {
        return '—';
    }
    $ts = strtotime($tgl);
    return $ts ? date('d F Y', $ts) : '—';
}

function status_badge(string $status): string
{
    return match ($status) {
        'Aktif'             => 'bg-elegant-100 text-elegant-800 border-elegant-200',
        'Cuti'              => 'bg-amber-100 text-amber-800 border-amber-200',
        'Lulus'             => 'bg-academic-100 text-academic-800 border-academic-200',
        'Mengundurkan Diri' => 'bg-orange-100 text-orange-800 border-orange-200',
        'Drop Out'          => 'bg-red-100 text-red-800 border-red-200',
        'Tidak Aktif'       => 'bg-slate-100 text-slate-700 border-slate-200',
        default             => 'bg-slate-100 text-slate-700 border-slate-200',
    };
}

$initials = '';
$parts = preg_split('/\s+/', trim($mhs['nama_mahasiswa'] ?? 'M'));
foreach (array_slice($parts, 0, 2) as $p) {
    $initials .= mb_strtoupper(mb_substr($p, 0, 1));
}

$pageTitle  = 'Detail Mahasiswa';
$activeMenu = ($idRole === 2) ? 'mahasiswa' : ($idRole === 5 ? 'profil' : 'mahasiswa');
if (in_array($idRole, [3, 4], true)) {
    $activeMenu = 'laporan';
}
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
        <li class="text-slate-900 font-semibold">Detail</li>
    </ol>
</nav>

<div class="flex items-center justify-between gap-4 mb-6 flex-wrap">
    <div>
        <h2 class="font-heading font-bold text-xl text-slate-900">Profil Mahasiswa</h2>
        <p class="text-sm text-slate-500 mt-1">Informasi lengkap data akademik mahasiswa.</p>
    </div>
    <div class="flex gap-2 flex-wrap">
        <?php if ($canManage): ?>
            <a href="<?= url('admin/mahasiswa/edit.php?id=' . $idMahasiswa) ?>"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold shadow-sm transition-colors">
                <i class="fa-solid fa-pen-to-square"></i>
                Edit
            </a>
        <?php endif; ?>
        <a href="<?= url('admin/mahasiswa/index.php') ?>"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-semibold transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            Kembali
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- ===== Kartu Profil ===== -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden">
            <!-- Banner -->
            <div class="h-24 bg-gradient-to-r from-academic-700 to-elegant-700 relative">
                <div class="absolute -bottom-10 left-1/2 -translate-x-1/2">
                    <div class="w-24 h-24 rounded-2xl bg-white shadow-soft flex items-center justify-center border-4 border-white">
                        <span class="font-heading font-extrabold text-2xl text-academic-700">
                            <?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="pt-14 pb-6 px-6 text-center">
                <h3 class="font-heading font-bold text-lg text-slate-900 leading-snug">
                    <?= htmlspecialchars($mhs['nama_mahasiswa'], ENT_QUOTES, 'UTF-8') ?>
                </h3>
                <p class="font-mono text-sm text-academic-700 font-semibold mt-1">
                    <?= htmlspecialchars($mhs['npm'], ENT_QUOTES, 'UTF-8') ?>
                </p>
                <div class="mt-3 flex items-center justify-center gap-2 flex-wrap">
                    <span class="inline-flex px-3 py-1 rounded-full border text-xs font-bold <?= htmlspecialchars(status_badge($mhs['status_mahasiswa']), ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($mhs['status_mahasiswa'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <span class="inline-flex px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold">
                        <?= htmlspecialchars($mhs['jenjang'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
            </div>

            <!-- Ringkas prodi -->
            <div class="px-6 pb-6">
                <div class="p-4 rounded-xl bg-gradient-to-br from-academic-50 to-elegant-50 border border-academic-100">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mb-1">Program Studi</p>
                    <p class="font-heading font-bold text-slate-900 text-sm">
                        <?= htmlspecialchars($mhs['nama_program_studi'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                    </p>
                    <p class="text-xs text-academic-700 font-semibold mt-0.5">
                        <?= htmlspecialchars($mhs['kode_program_studi'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        · <?= htmlspecialchars($mhs['jenjang'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </p>
                    <p class="text-xs text-slate-500 mt-2">
                        <?= htmlspecialchars($mhs['nama_fakultas'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== Detail Data ===== -->
    <div class="lg:col-span-2 space-y-6">

        <!-- Data diri -->
        <div class="bg-white rounded-2xl shadow-card border border-slate-100 p-6">
            <h4 class="font-heading font-bold text-slate-900 flex items-center gap-2 mb-5">
                <span class="w-8 h-8 rounded-lg bg-academic-100 text-academic-700 flex items-center justify-center">
                    <i class="fa-solid fa-user text-sm"></i>
                </span>
                Data Diri
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-wide">NPM</p>
                    <p class="text-sm font-semibold text-slate-900 font-mono mt-0.5"><?= htmlspecialchars($mhs['npm'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="pb-3 border-b border-slate-100">
                    <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-wide">Nama Lengkap</p>
                    <p class="text-sm font-semibold text-slate-900 mt-0.5"><?= htmlspecialchars($mhs['nama_mahasiswa'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="pb-3 border-b border-slate-100">
                    <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-wide">Jenis Kelamin</p>
                    <p class="text-sm font-semibold text-slate-900 mt-0.5">
                        <?= htmlspecialchars($mhs['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan', ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>
                <div class="pb-3 border-b border-slate-100">
                    <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-wide">Status</p>
                    <p class="text-sm font-semibold text-slate-900 mt-0.5"><?= htmlspecialchars($mhs['status_mahasiswa'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="pb-3 border-b border-slate-100">
                    <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-wide">Tempat Lahir</p>
                    <p class="text-sm font-semibold text-slate-900 mt-0.5"><?= htmlspecialchars($mhs['tempat_lahir'] ?: '—', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="pb-3 border-b border-slate-100">
                    <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-wide">Tanggal Lahir</p>
                    <p class="text-sm font-semibold text-slate-900 mt-0.5"><?= htmlspecialchars(format_tanggal($mhs['tanggal_lahir']), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="pb-3 border-b border-slate-100 sm:col-span-2">
                    <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-wide">Alamat</p>
                    <p class="text-sm text-slate-700 mt-0.5 leading-relaxed"><?= htmlspecialchars($mhs['alamat'] ?: '—', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
        </div>

        <!-- Data akademik -->
        <div class="bg-white rounded-2xl shadow-card border border-slate-100 p-6">
            <h4 class="font-heading font-bold text-slate-900 flex items-center gap-2 mb-5">
                <span class="w-8 h-8 rounded-lg bg-elegant-100 text-elegant-700 flex items-center justify-center">
                    <i class="fa-solid fa-graduation-cap text-sm"></i>
                </span>
                Data Akademik
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-wide">Tanggal Masuk</p>
                    <p class="text-sm font-semibold text-slate-900 mt-0.5"><?= htmlspecialchars(format_tanggal($mhs['tanggal_masuk']), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="pb-3 border-b border-slate-100">
                    <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-wide">Jenjang</p>
                    <p class="text-sm font-semibold text-slate-900 mt-0.5"><?= htmlspecialchars($mhs['jenjang'] ?? '—', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="pb-3 border-b border-slate-100">
                    <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-wide">Kode Prodi</p>
                    <p class="text-sm font-semibold text-slate-900 mt-0.5"><?= htmlspecialchars($mhs['kode_program_studi'] ?? '—', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="pb-3 border-b border-slate-100">
                    <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-wide">Program Studi</p>
                    <p class="text-sm font-semibold text-slate-900 mt-0.5"><?= htmlspecialchars($mhs['nama_program_studi'] ?? '—', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="pb-3 border-b border-slate-100 sm:col-span-2">
                    <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-wide">Fakultas / Universitas</p>
                    <p class="text-sm font-semibold text-slate-900 mt-0.5">
                        <?= htmlspecialchars($mhs['nama_fakultas'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                        <span class="text-slate-400 font-normal"> — </span>
                        <?= htmlspecialchars($mhs['nama_universitas'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Akun login -->
        <div class="bg-white rounded-2xl shadow-card border border-slate-100 p-6">
            <h4 class="font-heading font-bold text-slate-900 flex items-center gap-2 mb-5">
                <span class="w-8 h-8 rounded-lg bg-slate-900 text-white flex items-center justify-center">
                    <i class="fa-solid fa-key text-sm"></i>
                </span>
                Akun Login Terkait
            </h4>

            <?php if ($akun): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div class="pb-3 border-b border-slate-100">
                        <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-wide">Username</p>
                        <p class="text-sm font-semibold text-slate-900 font-mono mt-0.5"><?= htmlspecialchars($akun['username'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="pb-3 border-b border-slate-100">
                        <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-wide">Role</p>
                        <p class="text-sm font-semibold text-slate-900 mt-0.5"><?= htmlspecialchars($akun['nama_role'] ?? '—', ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="pb-3 border-b border-slate-100">
                        <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-wide">Email</p>
                        <p class="text-sm font-semibold text-slate-900 mt-0.5"><?= htmlspecialchars($akun['email'] ?: '—', ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="pb-3 border-b border-slate-100">
                        <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-wide">Status Akun</p>
                        <p class="text-sm font-semibold mt-0.5">
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold <?= $akun['status_aktif'] === 'Aktif' ? 'bg-elegant-100 text-elegant-800' : 'bg-red-100 text-red-800' ?>">
                                <?= htmlspecialchars($akun['status_aktif'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-wide">Login Terakhir</p>
                        <p class="text-sm text-slate-700 mt-0.5"><?= htmlspecialchars($akun['last_login'] ?: 'Belum pernah login', ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div class="flex items-center gap-3 p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <i class="fa-solid fa-user-slash text-slate-400"></i>
                    <p class="text-sm text-slate-500">Belum ada akun login yang terhubung dengan mahasiswa ini.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/layouts/admin/footer.php'; ?>

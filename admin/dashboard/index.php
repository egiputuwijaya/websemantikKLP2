<?php
/**
 * ============================================================
 *  admin/dashboard/index.php — Dashboard dinamis sesuai role
 * ============================================================
 */

require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/audit.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';

check_auth();

$user     = get_user_login();
$idRole   = (int) ($user['id_role'] ?? 0);
$namaUser = $user['nama_lengkap'] ?? 'Pengguna';
$namaRole = $user['nama_role'] ?? '-';

// ----------------------------------------------------------
// Statistik dinamis via PDO
// ----------------------------------------------------------
function count_where(PDO $pdo, string $sql, array $params = []): int
{
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log('[DASHBOARD] ' . $e->getMessage());
        return 0;
    }
}

$totalAktif = count_where($pdo, "SELECT COUNT(*) FROM mahasiswa WHERE status_mahasiswa = 'Aktif'");
$totalCuti  = count_where($pdo, "SELECT COUNT(*) FROM mahasiswa WHERE status_mahasiswa = 'Cuti'");
$totalLulus = count_where($pdo, "SELECT COUNT(*) FROM mahasiswa WHERE status_mahasiswa = 'Lulus'");
$totalProdi = count_where($pdo, "SELECT COUNT(*) FROM program_studi WHERE status_aktif = 'Aktif'");
$totalMhs   = count_where($pdo, "SELECT COUNT(*) FROM mahasiswa");
$totalFak   = count_where($pdo, "SELECT COUNT(*) FROM fakultas");

// Operator Prodi: tambah statistik prodi miliknya
$prodiSaya = null;
if ($idRole === 2 && !empty($user['id_program_studi'])) {
    $stmt = $pdo->prepare("SELECT nama_program_studi, kode_program_studi FROM program_studi WHERE id_program_studi = :id");
    $stmt->execute([':id' => (int) $user['id_program_studi']]);
    $prodiSaya = $stmt->fetch() ?: null;

    $aktifProdiSaya = count_where(
        $pdo,
        "SELECT COUNT(*) FROM mahasiswa WHERE id_program_studi = :pid AND status_mahasiswa = 'Aktif'",
        [':pid' => (int) $user['id_program_studi']]
    );
} else {
    $aktifProdiSaya = null;
}

// Mahasiswa terbaru (untuk semua role yang bisa lihat data)
$recentMahasiswa = [];
try {
    $limit = ($idRole === 5) ? 5 : 8;
    $sqlRecent = "SELECT id_mahasiswa, npm, nama_mahasiswa, status_mahasiswa, nama_program_studi, tanggal_masuk
                  FROM v_mahasiswa_per_prodi";
    $params = [];
    if ($idRole === 2 && !empty($user['id_program_studi'])) {
        $sqlRecent .= " WHERE id_program_studi = :pid";
        $params[':pid'] = (int) $user['id_program_studi'];
    } elseif ($idRole === 5 && !empty($user['id_program_studi'])) {
        // Mahasiswa hanya lihat datanya sendiri via NPM/username
        $sqlRecent .= " WHERE npm = :npm";
        $params[':npm'] = $user['username'] ?? '';
    }
    $sqlRecent .= " ORDER BY id_mahasiswa DESC LIMIT " . $limit;
    $stmt = $pdo->prepare($sqlRecent);
    $stmt->execute($params);
    $recentMahasiswa = $stmt->fetchAll();
} catch (PDOException $e) {
    $recentMahasiswa = [];
}

// Sapaan berdasarkan waktu
$jam = (int) date('G');
if ($jam < 11) {
    $sapaan = 'Selamat Pagi';
} elseif ($jam < 15) {
    $sapaan = 'Selamat Siang';
} elseif ($jam < 18) {
    $sapaan = 'Selamat Sore';
} else {
    $sapaan = 'Selamat Malam';
}

$pageTitle  = 'Dashboard';
$activeMenu = 'dashboard';
?>

<?php require_once dirname(__DIR__, 2) . '/layouts/admin/header.php'; ?>

<!-- ============================================================
     WELCOME BANNER
     ============================================================ -->
<section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-academic-800 via-academic-700 to-elegant-800 shadow-soft mb-8">
    <div class="absolute -top-16 -right-16 w-64 h-64 rounded-full bg-white/10 blur-3xl"></div>
    <div class="absolute -bottom-20 -left-10 w-72 h-72 rounded-full bg-elegant-500/20 blur-3xl"></div>
    <div class="relative px-6 sm:px-8 py-8 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div>
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-elegant-300 text-xs font-semibold mb-3">
                <i class="fa-solid fa-calendar-day"></i>
                <?= htmlspecialchars(date('d F Y', strtotime('now')), ENT_QUOTES, 'UTF-8') ?>
            </span>
            <h2 class="font-heading font-extrabold text-white text-2xl sm:text-3xl mb-2">
                <?= htmlspecialchars($sapaan, ENT_QUOTES, 'UTF-8') ?>,
                <span class="text-elegant-300"><?= htmlspecialchars($namaUser, ENT_QUOTES, 'UTF-8') ?></span>!
            </h2>
            <p class="text-slate-200/90 text-sm max-w-xl">
                Anda masuk dengan role
                <span class="font-semibold text-white"><?= htmlspecialchars($namaRole, ENT_QUOTES, 'UTF-8') ?></span>.
                Semoga hari Anda menyenangkan dan produktif.
            </p>
        </div>
        <div class="glass rounded-2xl px-6 py-5 text-center shrink-0 border border-white/15 bg-white/10 backdrop-blur">
            <i class="fa-solid fa-shield-halved text-elegant-300 text-2xl mb-2"></i>
            <p class="text-white font-heading font-bold text-sm">Sesi Aktif</p>
            <p class="text-[11px] text-slate-300 mt-0.5">Login terakhir</p>
            <p class="text-elegant-300 text-xs font-semibold mt-1">
                <?= htmlspecialchars($user['last_login'] ?? date('Y-m-d H:i:s'), ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>
    </div>
</section>

<!-- ============================================================
     KARTU STATISTIK
     ============================================================ -->
<section class="mb-8">
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 md:gap-6">

        <!-- Mahasiswa Aktif -->
        <div class="card-hover bg-white rounded-2xl shadow-card border border-slate-100 p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-academic-100 text-academic-700 flex items-center justify-center">
                    <i class="fa-solid fa-user-check text-lg"></i>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-academic-50 text-academic-700 text-[11px] font-bold">Aktif</span>
            </div>
            <p class="font-heading font-extrabold text-3xl text-slate-900 leading-none"><?= number_format($totalAktif) ?></p>
            <p class="text-sm text-slate-500 mt-2 font-medium">Mahasiswa Aktif</p>
        </div>

        <!-- Mahasiswa Cuti -->
        <div class="card-hover bg-white rounded-2xl shadow-card border border-slate-100 p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                    <i class="fa-solid fa-clock text-lg"></i>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 text-[11px] font-bold">Cuti</span>
            </div>
            <p class="font-heading font-extrabold text-3xl text-slate-900 leading-none"><?= number_format($totalCuti) ?></p>
            <p class="text-sm text-slate-500 mt-2 font-medium">Mahasiswa Cuti</p>
        </div>

        <!-- Mahasiswa Lulus -->
        <div class="card-hover bg-white rounded-2xl shadow-card border border-slate-100 p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-elegant-100 text-elegant-700 flex items-center justify-center">
                    <i class="fa-solid fa-graduation-cap text-lg"></i>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-elegant-50 text-elegant-700 text-[11px] font-bold">Lulus</span>
            </div>
            <p class="font-heading font-extrabold text-3xl text-slate-900 leading-none"><?= number_format($totalLulus) ?></p>
            <p class="text-sm text-slate-500 mt-2 font-medium">Mahasiswa Lulus</p>
        </div>

        <!-- Total Prodi -->
        <div class="card-hover bg-white rounded-2xl shadow-card border border-slate-100 p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center">
                    <i class="fa-solid fa-book-open-reader text-lg"></i>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-[11px] font-bold">Prodi</span>
            </div>
            <p class="font-heading font-extrabold text-3xl text-slate-900 leading-none"><?= number_format($totalProdi) ?></p>
            <p class="text-sm text-slate-500 mt-2 font-medium">Program Studi Aktif</p>
        </div>

    </div>
</section>

<!-- ============================================================
     RINGKASAN TAMBAHAN + PRODI OPERATOR
     ============================================================ -->
<section class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    <!-- Ringkasan umum -->
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-card border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-heading font-bold text-slate-900 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-academic-100 text-academic-700 flex items-center justify-center">
                    <i class="fa-solid fa-chart-pie text-sm"></i>
                </span>
                Ringkasan Data
            </h3>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-center">
                <p class="font-heading font-bold text-2xl text-slate-900"><?= number_format($totalMhs) ?></p>
                <p class="text-xs text-slate-500 mt-1">Total Mahasiswa</p>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-center">
                <p class="font-heading font-bold text-2xl text-slate-900"><?= number_format($totalFak) ?></p>
                <p class="text-xs text-slate-500 mt-1">Fakultas</p>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-center">
                <p class="font-heading font-bold text-2xl text-slate-900"><?= number_format($totalProdi) ?></p>
                <p class="text-xs text-slate-500 mt-1">Program Studi</p>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-center">
                <p class="font-heading font-bold text-2xl text-slate-900"><?= number_format($totalAktif) ?></p>
                <p class="text-xs text-slate-500 mt-1">Status Aktif</p>
            </div>
        </div>
    </div>

    <!-- Info prodi (khusus operator) atau aksi cepat -->
    <div class="bg-white rounded-2xl shadow-card border border-slate-100 p-6">
        <h3 class="font-heading font-bold text-slate-900 flex items-center gap-2 mb-5">
            <span class="w-8 h-8 rounded-lg bg-elegant-100 text-elegant-700 flex items-center justify-center">
                <i class="fa-solid fa-bolt text-sm"></i>
            </span>
            <?= $idRole === 2 ? 'Prodi Saya' : 'Aksi Cepat' ?>
        </h3>

        <?php if ($idRole === 2 && $prodiSaya): ?>
            <div class="p-4 rounded-xl bg-gradient-to-br from-elegant-50 to-academic-50 border border-elegant-100 mb-4">
                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wide mb-1">Program Studi</p>
                <p class="font-heading font-bold text-slate-900"><?= htmlspecialchars($prodiSaya['nama_program_studi'], ENT_QUOTES, 'UTF-8') ?></p>
                <p class="text-xs text-academic-700 font-semibold mt-0.5">
                    Kode: <?= htmlspecialchars($prodiSaya['kode_program_studi'], ENT_QUOTES, 'UTF-8') ?>
                </p>
                <div class="mt-3 pt-3 border-t border-elegant-200/60">
                    <p class="text-xs text-slate-500">Mahasiswa Aktif di Prodi</p>
                    <p class="font-heading font-extrabold text-2xl text-elegant-700"><?= number_format((int) $aktifProdiSaya) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="space-y-2">
            <?php if (in_array($idRole, [1, 2], true)): ?>
                <a href="<?= url('admin/mahasiswa/index.php') ?>"
                   class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-academic-300 hover:bg-academic-50 transition-colors group">
                    <span class="w-9 h-9 rounded-lg bg-academic-100 text-academic-700 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-list"></i>
                    </span>
                    <span class="text-sm font-semibold text-slate-700">Kelola Data Mahasiswa</span>
                </a>
                <a href="<?= url('admin/mahasiswa/create.php') ?>"
                   class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-elegant-300 hover:bg-elegant-50 transition-colors group">
                    <span class="w-9 h-9 rounded-lg bg-elegant-100 text-elegant-700 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-user-plus"></i>
                    </span>
                    <span class="text-sm font-semibold text-slate-700">Tambah Mahasiswa Baru</span>
                </a>
            <?php elseif ($idRole === 5): ?>
                <a href="<?= url('admin/profil-saya/index.php') ?>"
                   class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-academic-300 hover:bg-academic-50 transition-colors group">
                    <span class="w-9 h-9 rounded-lg bg-academic-100 text-academic-700 flex items-center justify-center">
                        <i class="fa-solid fa-id-card"></i>
                    </span>
                    <span class="text-sm font-semibold text-slate-700">Lihat Profil Saya</span>
                </a>
            <?php else: ?>
                <a href="<?= url('admin/mahasiswa/index.php') ?>"
                   class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-academic-300 hover:bg-academic-50 transition-colors group">
                    <span class="w-9 h-9 rounded-lg bg-academic-100 text-academic-700 flex items-center justify-center">
                        <i class="fa-solid fa-file-lines"></i>
                    </span>
                    <span class="text-sm font-semibold text-slate-700">Lihat Data / Laporan</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

</section>

<!-- ============================================================
     MAHASISWA TERBARU
     ============================================================ -->
<section class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between gap-4 flex-wrap">
        <h3 class="font-heading font-bold text-slate-900 flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg bg-academic-100 text-academic-700 flex items-center justify-center">
                <i class="fa-solid fa-clock-rotate-left text-sm"></i>
            </span>
            Data Mahasiswa Terbaru
        </h3>
        <?php if (in_array($idRole, [1, 2, 3, 4], true)): ?>
            <a href="<?= url('admin/mahasiswa/index.php') ?>"
               class="text-sm font-semibold text-academic-700 hover:text-academic-800 inline-flex items-center gap-1.5">
                Lihat Semua
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        <?php endif; ?>
    </div>

    <?php if (empty($recentMahasiswa)): ?>
        <div class="p-10 text-center">
            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-inbox text-2xl"></i>
            </div>
            <p class="text-slate-500 text-sm">Belum ada data mahasiswa untuk ditampilkan.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-hover">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="text-left font-semibold px-6 py-3">NPM</th>
                        <th class="text-left font-semibold px-6 py-3">Nama Mahasiswa</th>
                        <th class="text-left font-semibold px-6 py-3">Program Studi</th>
                        <th class="text-left font-semibold px-6 py-3">Status</th>
                        <th class="text-left font-semibold px-6 py-3">Tgl Masuk</th>
                        <?php if (in_array($idRole, [1, 2], true)): ?>
                            <th class="text-right font-semibold px-6 py-3">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($recentMahasiswa as $m):
                        $status = $m['status_mahasiswa'] ?? '-';
                        $badge = match ($status) {
                            'Aktif'     => 'bg-elegant-100 text-elegant-800',
                            'Cuti'      => 'bg-amber-100 text-amber-800',
                            'Lulus'     => 'bg-academic-100 text-academic-800',
                            'Drop Out'  => 'bg-red-100 text-red-800',
                            default     => 'bg-slate-100 text-slate-700',
                        };
                    ?>
                        <tr>
                            <td class="px-6 py-4 font-mono text-xs text-slate-600"><?= htmlspecialchars($m['npm'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-6 py-4 font-semibold text-slate-900"><?= htmlspecialchars($m['nama_mahasiswa'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($m['nama_program_studi'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-2.5 py-1 rounded-lg text-[11px] font-bold <?= htmlspecialchars($badge, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-xs">
                                <?= htmlspecialchars($m['tanggal_masuk'] ? date('d/m/Y', strtotime($m['tanggal_masuk'])) : '-', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <?php if (in_array($idRole, [1, 2], true)): ?>
                                <td class="px-6 py-4 text-right">
                                    <a href="<?= url('admin/mahasiswa/detail.php?id=' . (int) $m['id_mahasiswa']) ?>"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-academic-50 text-academic-700 text-xs font-semibold hover:bg-academic-100 transition-colors">
                                        <i class="fa-solid fa-eye"></i> Detail
                                    </a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require_once dirname(__DIR__, 2) . '/layouts/admin/footer.php'; ?>

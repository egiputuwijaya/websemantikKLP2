<?php
/**
 * ============================================================
 *  admin/mahasiswa/index.php — Daftar Data Mahasiswa (CRUD list)
 * ============================================================
 *  Akses: Admin (1), Operator Prodi (2), Dekanat (3), Rektorat (4).
 *  Data dari view v_mahasiswa_per_prodi.
 */

require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/audit.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';

// Role 1 & 2 boleh akses (lihat, tambah, edit, hapus).
require_role([1, 2]);

$user   = get_user_login();
$idRole = (int) ($user['id_role'] ?? 0);
$canManage = in_array($idRole, [1, 2], true);

// Operator Prodi: filter kunci ke prodi miliknya
$lockedProdiId = null;
$lockedProdiName = '';
if ($idRole === 2 && !empty($user['id_program_studi'])) {
    $lockedProdiId = (int) $user['id_program_studi'];
    $stmt = $pdo->prepare("SELECT nama_program_studi FROM program_studi WHERE id_program_studi = :id");
    $stmt->execute([':id' => $lockedProdiId]);
    $lockedProdiName = $stmt->fetchColumn() ?: 'Prodi Saya';
}

// ----------------------------------------------------------
// Parameter pencarian & filter
// ----------------------------------------------------------
$q       = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$q       = mb_substr($q, 0, 100);
$fProdi  = isset($_GET['prodi']) ? (int) $_GET['prodi'] : 0;
$fStatus = isset($_GET['status']) ? (string) $_GET['status'] : '';

if ($idRole === 2) {
    $fProdi = $lockedProdiId ?? 0;
}

// ----------------------------------------------------------
// Dropdown program studi (untuk filter)
// ----------------------------------------------------------
$prodiList = [];
try {
    if ($idRole === 2 && $lockedProdiId) {
        $stmt = $pdo->prepare("SELECT id_program_studi, nama_program_studi FROM program_studi WHERE id_program_studi = :id ORDER BY nama_program_studi");
        $stmt->execute([':id' => $lockedProdiId]);
    } else {
        $stmt = $pdo->query("SELECT id_program_studi, nama_program_studi FROM program_studi WHERE status_aktif = 'Aktif' ORDER BY nama_program_studi");
    }
    $prodiList = $stmt->fetchAll();
} catch (PDOException $e) {
    $prodiList = [];
}

// ----------------------------------------------------------
// Query data mahasiswa dari VIEW + filter + pencarian
// ----------------------------------------------------------
$where  = [];
$params = [];

if ($lockedProdiId) {
    $where[] = "id_program_studi = :locked_prodi";
    $params[':locked_prodi'] = $lockedProdiId;
} elseif ($fProdi > 0) {
    $where[] = "id_program_studi = :prodi";
    $params[':prodi'] = $fProdi;
}

if ($q !== '') {
    // Parameter berbeda untuk tiap kolom (native prepares tidak boleh reuse nama param)
    $where[] = "(npm LIKE :q1 OR nama_mahasiswa LIKE :q2)";
    $params[':q1'] = '%' . $q . '%';
    $params[':q2'] = '%' . $q . '%';
}

if ($fStatus !== '' && in_array($fStatus, ['Aktif', 'Cuti', 'Lulus', 'Mengundurkan Diri', 'Drop Out', 'Tidak Aktif'], true)) {
    $where[] = "status_mahasiswa = :status";
    $params[':status'] = $fStatus;
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Hitung total untuk pagination sederhana
$totalRows = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM v_mahasiswa_per_prodi $whereSql");
    $stmt->execute($params);
    $totalRows = (int) $stmt->fetchColumn();
} catch (PDOException $e) {
    $totalRows = 0;
    error_log('[MAHASISWA INDEX] ' . $e->getMessage());
}

// Pagination
$perPage = 15;
$totalPages = max(1, (int) ceil($totalRows / $perPage));
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$page = max(1, min($page, $totalPages));
$offset = ($page - 1) * $perPage;

// Ambil data
$mahasiswaList = [];
try {
    $sql = "SELECT *
            FROM v_mahasiswa_per_prodi
            $whereSql
            ORDER BY nama_mahasiswa ASC
            LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $mahasiswaList = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('[MAHASISWA INDEX] ' . $e->getMessage());
    $mahasiswaList = [];
}

// Badge status
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

$pageTitle  = 'Data Mahasiswa';
$activeMenu = 'mahasiswa';
?>

<?php require_once dirname(__DIR__, 2) . '/layouts/admin/header.php'; ?>

<!-- Header halaman -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h2 class="font-heading font-bold text-xl text-slate-900">
            <?= $idRole === 2 ? 'Data Mahasiswa Program Studi' : ($idRole === 5 ? 'Data Mahasiswa' : 'Data Mahasiswa') ?>
        </h2>
        <p class="text-sm text-slate-500 mt-1">
            <?php if ($lockedProdiId): ?>
                Terkunci pada: <span class="font-semibold text-academic-700"><?= htmlspecialchars($lockedProdiName, ENT_QUOTES, 'UTF-8') ?></span>
            <?php else: ?>
                Total <span class="font-semibold text-slate-700"><?= number_format($totalRows) ?></span> data ditemukan
            <?php endif; ?>
        </p>
    </div>
    <?php if ($canManage): ?>
        <a href="<?= url('admin/mahasiswa/create.php') ?>"
           class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-academic-600 text-white text-sm font-semibold rounded-md hover:bg-academic-700 transition-colors focus:ring-4 focus:ring-academic-100">
            <i class="fa-solid fa-user-plus"></i>
            Tambah Mahasiswa
        </a>
    <?php endif; ?>
</div>

<!-- Kartu filter & pencarian -->
<div class="bg-white rounded-md shadow-sm border border-slate-200 p-4 sm:p-5 mb-6">
    <form action="<?= url('admin/mahasiswa/index.php') ?>" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">

        <!-- Pencarian -->
        <div class="md:col-span-5">
            <label for="q" class="block text-xs font-semibold text-slate-600 mb-1.5">Cari (Nama / NPM)</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </span>
                <input type="text" name="q" id="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="Ketik nama atau NPM..."
                       class="w-full pl-9 pr-3 py-2.5 rounded-md border border-slate-200 bg-slate-50 text-sm focus:border-academic-500 focus:bg-white focus:ring-2 focus:ring-academic-100 transition-colors">
            </div>
        </div>

        <!-- Filter Prodi -->
        <div class="md:col-span-3">
            <label for="prodi" class="block text-xs font-semibold text-slate-600 mb-1.5">Program Studi</label>
            <?php if ($lockedProdiId): ?>
                <input type="text" value="<?= htmlspecialchars($lockedProdiName, ENT_QUOTES, 'UTF-8') ?>" disabled
                       class="w-full px-3 py-2.5 rounded-md border border-slate-200 bg-slate-100 text-sm text-slate-500">
                <input type="hidden" name="prodi" value="<?= (int) $lockedProdiId ?>">
            <?php else: ?>
                <select name="prodi" id="prodi"
                        class="w-full px-3 py-2.5 rounded-md border border-slate-200 bg-slate-50 text-sm focus:border-academic-500 focus:bg-white focus:ring-2 focus:ring-academic-100 transition-colors">
                    <option value="0">— Semua Prodi —</option>
                    <?php foreach ($prodiList as $p): ?>
                        <option value="<?= (int) $p['id_program_studi'] ?>"
                            <?= $fProdi === (int) $p['id_program_studi'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nama_program_studi'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>

        <!-- Filter Status -->
        <div class="md:col-span-2">
            <label for="status" class="block text-xs font-semibold text-slate-600 mb-1.5">Status</label>
            <select name="status" id="status"
                    class="w-full px-3 py-2.5 rounded-md border border-slate-200 bg-slate-50 text-sm focus:border-academic-500 focus:bg-white focus:ring-2 focus:ring-academic-100 transition-colors">
                <option value="">— Semua —</option>
                <?php foreach (['Aktif', 'Cuti', 'Lulus', 'Mengundurkan Diri', 'Drop Out', 'Tidak Aktif'] as $s): ?>
                    <option value="<?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?>" <?= $fStatus === $s ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Tombol aksi filter -->
        <div class="md:col-span-2 flex gap-2">
            <button type="submit"
                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md bg-academic-600 hover:bg-academic-700 text-white text-sm font-semibold transition-colors focus:ring-4 focus:ring-academic-100">
                <i class="fa-solid fa-filter"></i>
                Filter
            </button>
            <a href="<?= url('admin/mahasiswa/index.php') ?>"
               class="inline-flex items-center justify-center px-3 py-2.5 rounded-md border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-semibold transition-colors"
               title="Reset filter">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        </div>
    </form>
</div>

<!-- Tabel data -->
<div class="bg-white rounded-md shadow-sm border border-slate-200 overflow-hidden">
    <?php if (empty($mahasiswaList)): ?>
        <div class="p-12 text-center">
            <div class="w-20 h-20 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-5">
                <i class="fa-solid fa-user-graduate text-3xl"></i>
            </div>
            <h3 class="font-heading font-bold text-slate-900 mb-2">Data Tidak Ditemukan</h3>
            <p class="text-slate-500 text-sm max-w-md mx-auto mb-6">
                Belum ada data mahasiswa yang cocok dengan kriteria pencarian Anda.
                Coba ubah kata kunci atau filter, atau tambahkan data baru.
            </p>
            <?php if ($canManage): ?>
                <a href="<?= url('admin/mahasiswa/create.php') ?>"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-academic-700 text-white text-sm font-bold hover:bg-academic-800 transition-colors">
                    <i class="fa-solid fa-user-plus"></i>
                    Tambah Mahasiswa
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left font-semibold text-slate-600 px-5 py-3.5">NPM</th>
                        <th class="text-left font-semibold text-slate-600 px-5 py-3.5">Nama Mahasiswa</th>
                        <th class="text-left font-semibold text-slate-600 px-5 py-3.5">Jenis Kelamin</th>
                        <th class="text-left font-semibold text-slate-600 px-5 py-3.5">Program Studi</th>
                        <th class="text-left font-semibold text-slate-600 px-5 py-3.5">Jenjang</th>
                        <th class="text-left font-semibold text-slate-600 px-5 py-3.5">Status</th>
                        <th class="text-left font-semibold text-slate-600 px-5 py-3.5">Tgl Masuk</th>
                        <th class="text-right font-semibold text-slate-600 px-5 py-3.5">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($mahasiswaList as $m):
                        $idM = (int) $m['id_mahasiswa'];
                        $status = $m['status_mahasiswa'] ?? '-';
                    ?>
                        <tr class="table-hover">
                            <td class="px-5 py-4">
                                <span class="font-mono text-xs text-slate-700 bg-slate-100 px-2 py-1 rounded-md">
                                    <?= htmlspecialchars($m['npm'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-semibold text-slate-900"><?= htmlspecialchars($m['nama_mahasiswa'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                                <p class="text-[11px] text-slate-400 mt-0.5"><?= htmlspecialchars($m['nama_fakultas'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                            </td>
                            <td class="px-5 py-4 text-slate-600"><?= htmlspecialchars($m['jenis_kelamin_text'] ?? $m['jenis_kelamin'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-5 py-4 text-slate-600"><?= htmlspecialchars($m['nama_program_studi'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-5 py-4">
                                <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] font-bold">
                                    <?= htmlspecialchars($m['jenjang'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex px-2.5 py-1 rounded-lg border text-[11px] font-bold <?= htmlspecialchars(status_badge($status), ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-500 text-xs">
                                <?= htmlspecialchars($m['tanggal_masuk'] ? date('d/m/Y', strtotime($m['tanggal_masuk'])) : '-', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1.5 flex-wrap">

                                    <!-- Detail -->
                                    <a href="<?= url('admin/mahasiswa/detail.php?id=' . $idM) ?>"
                                       class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-academic-50 text-academic-700 text-xs font-semibold hover:bg-academic-100 transition-colors"
                                       title="Lihat detail">
                                        <i class="fa-solid fa-eye"></i>
                                        Detail
                                    </a>

                                    <?php if ($canManage): ?>
                                        <!-- Edit -->
                                        <a href="<?= url('admin/mahasiswa/edit.php?id=' . $idM) ?>"
                                           class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-amber-50 text-amber-700 text-xs font-semibold hover:bg-amber-100 transition-colors"
                                           title="Edit data">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                            Edit
                                        </a>

                                        <!-- Hapus -->
                                        <form action="<?= url('admin/mahasiswa/process.php') ?>" method="POST"
                                              class="inline">
                                            <input type="hidden" name="action" value="DELETE">
                                            <input type="hidden" name="id_mahasiswa" value="<?= $idM ?>">
                                            <button type="submit"
                                                    data-confirm="Yakin hapus data mahasiswa <?= htmlspecialchars(addslashes($m['nama_mahasiswa'] ?? ''), ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars(addslashes($m['npm'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)?"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-red-50 text-red-700 text-xs font-semibold hover:bg-red-100 transition-colors"
                                                    title="Hapus data">
                                                <i class="fa-solid fa-trash-can"></i>
                                                Hapus
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="px-5 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-slate-500">
                    Halaman <span class="font-semibold text-slate-700"><?= $page ?></span> dari
                    <span class="font-semibold text-slate-700"><?= $totalPages ?></span>
                    (<?= number_format($totalRows) ?> data)
                </p>
                <nav class="flex items-center gap-1" aria-label="Navigasi halaman">
                    <?php
                    // Bangun query string mempertahankan filter
                    $qs = $_GET;
                    unset($qs['page']);
                    $baseQs = http_build_query($qs);
                    $sep = $baseQs !== '' ? '&' : '';
                    ?>

                    <?php if ($page > 1): ?>
                        <a href="<?= url('admin/mahasiswa/index.php?' . $baseQs . $sep . 'page=' . ($page - 1)) ?>"
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">
                            <i class="fa-solid fa-chevron-left"></i> Prev
                        </a>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <a href="<?= url('admin/mahasiswa/index.php?' . $baseQs . $sep . 'page=' . $i) ?>"
                           class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-xs font-bold <?= $i === $page ? 'bg-academic-700 text-white' : 'border border-slate-200 text-slate-600 hover:bg-slate-50' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= url('admin/mahasiswa/index.php?' . $baseQs . $sep . 'page=' . ($page + 1)) ?>"
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">
                            Next <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__, 2) . '/layouts/admin/footer.php'; ?>

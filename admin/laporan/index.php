<?php
/**
 * admin/laporan/index.php — Rekapitulasi Laporan Mahasiswa
 */
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';

// Bisa diakses Admin, Operator Prodi, Dekanat, Rektorat
require_role([1, 2, 3, 4]);

$userActive = get_user_login();
$idRole     = (int)$userActive['id_role'];
$userFak    = (int)($userActive['id_fakultas'] ?? 0);
$userProdi  = (int)($userActive['id_program_studi'] ?? 0);

// Filter dari URL
$fStatus = $_GET['status'] ?? '';
$fTahun  = $_GET['tahun'] ?? '';
$fProdi  = $_GET['id_prodi'] ?? '';

// Build Query base
$where = ["1=1"];
$params = [];

// Terapkan limitasi Role
if ($idRole === 3) {
    // Dekanat: Hanya fakultasnya
    $where[] = "pr.id_fakultas = :user_fak";
    $params[':user_fak'] = $userFak;
} elseif ($idRole === 2) {
    // Operator Prodi: Hanya prodinya
    $where[] = "m.id_program_studi = :user_prodi";
    $params[':user_prodi'] = $userProdi;
}

// Terapkan Filter User
if ($fStatus !== '') {
    $where[] = "m.status_mahasiswa = :status";
    $params[':status'] = $fStatus;
}
if ($fTahun !== '') {
    $where[] = "YEAR(m.tanggal_masuk) = :tahun";
    $params[':tahun'] = $fTahun;
}
if ($fProdi !== '' && $idRole !== 2) {
    $where[] = "m.id_program_studi = :f_prodi";
    $params[':f_prodi'] = $fProdi;
}

$whereSql = "WHERE " . implode(" AND ", $where);

try {
    // 1. Ambil Data Mahasiswa (tabel)
    $sqlData = "SELECT m.*, pr.nama_program_studi, f.nama_fakultas 
                FROM mahasiswa m 
                JOIN program_studi pr ON m.id_program_studi = pr.id_program_studi 
                JOIN fakultas f ON pr.id_fakultas = f.id_fakultas 
                $whereSql 
                ORDER BY m.tanggal_masuk DESC, m.nama_mahasiswa ASC";
    $stmtData = $pdo->prepare($sqlData);
    $stmtData->execute($params);
    $mahasiswa = $stmtData->fetchAll();

    // 2. Ambil Statistik Status untuk Grafik
    $sqlStats = "SELECT m.status_mahasiswa, COUNT(m.id_mahasiswa) as total 
                 FROM mahasiswa m 
                 JOIN program_studi pr ON m.id_program_studi = pr.id_program_studi 
                 $whereSql 
                 GROUP BY m.status_mahasiswa";
    $stmtStats = $pdo->prepare($sqlStats);
    $stmtStats->execute($params);
    $stats = $stmtStats->fetchAll(PDO::FETCH_KEY_PAIR);

    // Siapkan list Prodi untuk dropdown filter
    $prodiList = [];
    if ($idRole !== 2) {
        $qProdi = "SELECT id_program_studi, nama_program_studi FROM program_studi";
        if ($idRole === 3) {
            $qProdi .= " WHERE id_fakultas = " . (int)$userFak;
        }
        $prodiList = $pdo->query($qProdi)->fetchAll();
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $mahasiswa = [];
    $stats = [];
    $prodiList = [];
}

$pageTitle  = 'Laporan Data Mahasiswa';
$activeMenu = 'laporan';
?>
<?php require_once dirname(__DIR__, 2) . '/layouts/admin/header.php'; ?>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<section class="mb-8">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="font-heading font-bold text-2xl text-slate-900">Rekapitulasi Mahasiswa</h2>
            <p class="text-sm text-slate-500 mt-1">Laporan analitik, grafik, dan cetak data akademik.</p>
        </div>
        <div class="flex gap-2">
            <!-- Ekspor URL di-build dengan parameter yang sama -->
            <?php 
                $qs = http_build_query($_GET);
                $urlExcel = url('admin/laporan/export-excel.php?' . $qs);
                $urlPdf   = url('admin/laporan/cetak-pdf.php?' . $qs);
            ?>
            <a href="<?= $urlExcel ?>" target="_blank" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-md transition-colors focus:ring-4 focus:ring-emerald-200 inline-flex items-center gap-2">
                <i class="fa-solid fa-file-csv"></i> Export CSV
            </a>
            <a href="<?= $urlPdf ?>" target="_blank" class="px-4 py-2 bg-rose-500 hover:bg-rose-600 text-white text-sm font-semibold rounded-md transition-colors focus:ring-4 focus:ring-rose-200 inline-flex items-center gap-2">
                <i class="fa-solid fa-print"></i> Cetak PDF
            </a>
        </div>
    </div>

    <!-- Ringkasan Statistik -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <?php 
            $stsData = [
                'Aktif' => ['bg' => 'bg-emerald-50 text-emerald-600', 'ic' => 'fa-user-check'],
                'Cuti'  => ['bg' => 'bg-amber-50 text-amber-600', 'ic' => 'fa-pause'],
                'Lulus' => ['bg' => 'bg-blue-50 text-blue-600', 'ic' => 'fa-graduation-cap'],
                'Drop Out' => ['bg' => 'bg-red-50 text-red-600', 'ic' => 'fa-user-xmark'],
            ];
            foreach ($stsData as $st => $cfg): 
                $tot = $stats[$st] ?? 0;
        ?>
        <div class="bg-white rounded-md shadow-sm border border-slate-100 p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider"><?= $st ?></p>
                <h4 class="font-heading font-bold text-2xl text-slate-800 mt-1"><?= number_format($tot) ?></h4>
            </div>
            <div class="w-10 h-10 rounded-full flex items-center justify-center <?= $cfg['bg'] ?>">
                <i class="fa-solid <?= $cfg['ic'] ?> text-lg"></i>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Panel Grafik -->
        <div class="bg-white rounded-md shadow-sm border border-slate-100 p-6 flex flex-col">
            <h3 class="font-bold text-slate-800 mb-4">Distribusi Status Mahasiswa</h3>
            <div class="flex-1 min-h-[250px] relative w-full flex justify-center items-center">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Tabel Filter & Data -->
        <div class="lg:col-span-2 bg-white rounded-md shadow-sm border border-slate-100 flex flex-col overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50">
                <form action="" method="GET" class="flex flex-wrap items-end gap-3">
                    <?php if ($idRole !== 2): ?>
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Prodi</label>
                        <select name="id_prodi" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-md text-sm focus:ring-2 focus:ring-academic-100 focus:border-academic-500">
                            <option value="">Semua Prodi</option>
                            <?php foreach ($prodiList as $pl): ?>
                                <option value="<?= $pl['id_program_studi'] ?>" <?= $fProdi == $pl['id_program_studi'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($pl['nama_program_studi'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="w-32">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-md text-sm focus:ring-2 focus:ring-academic-100 focus:border-academic-500">
                            <option value="">Semua</option>
                            <option value="Aktif" <?= $fStatus === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="Cuti" <?= $fStatus === 'Cuti' ? 'selected' : '' ?>>Cuti</option>
                            <option value="Lulus" <?= $fStatus === 'Lulus' ? 'selected' : '' ?>>Lulus</option>
                            <option value="Drop Out" <?= $fStatus === 'Drop Out' ? 'selected' : '' ?>>Drop Out</option>
                        </select>
                    </div>
                    <div class="w-28">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Thn. Masuk</label>
                        <input type="number" name="tahun" value="<?= htmlspecialchars($fTahun) ?>" placeholder="Contoh: 2026" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-md text-sm focus:ring-2 focus:ring-academic-100 focus:border-academic-500">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-academic-600 hover:bg-academic-700 text-white text-sm font-semibold rounded-md transition-colors focus:ring-2 focus:ring-academic-200">
                            Filter
                        </button>
                        <?php if (!empty($_GET)): ?>
                            <a href="<?= url('admin/laporan/index.php') ?>" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-semibold rounded-md transition-colors focus:ring-2 focus:ring-slate-200" title="Reset">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <div class="overflow-y-auto h-full max-h-[400px]">
                <table class="w-full text-sm table-hover border-b border-slate-100">
                    <thead class="bg-slate-50 text-slate-600 sticky top-0 shadow-sm z-10">
                        <tr>
                            <th class="text-center font-semibold px-4 py-2 w-16">No</th>
                            <th class="text-left font-semibold px-4 py-2">NPM & Nama</th>
                            <th class="text-left font-semibold px-4 py-2">Program Studi</th>
                            <th class="text-center font-semibold px-4 py-2">Tahun Masuk</th>
                            <th class="text-center font-semibold px-4 py-2 w-24">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($mahasiswa)): ?>
                            <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">Tidak ada data ditemukan.</td></tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($mahasiswa as $m): ?>
                                <tr>
                                    <td class="px-4 py-3 text-center text-slate-500"><?= $no++ ?></td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-slate-900"><?= htmlspecialchars($m['nama_mahasiswa'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="text-xs font-mono text-slate-500 mt-0.5"><?= htmlspecialchars($m['npm'], ENT_QUOTES, 'UTF-8') ?></div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 text-xs font-semibold uppercase"><?= htmlspecialchars($m['nama_program_studi'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="px-4 py-3 text-center text-slate-600 font-mono"><?= date('Y', strtotime($m['tanggal_masuk'])) ?></td>
                                    <td class="px-4 py-3 text-center">
                                        <?php 
                                            $st = $m['status_mahasiswa'];
                                            $bBg = 'bg-slate-100 text-slate-600 border-slate-200';
                                            if ($st === 'Aktif') $bBg = 'bg-green-100 text-green-700 border-green-200';
                                            elseif ($st === 'Lulus') $bBg = 'bg-blue-100 text-blue-700 border-blue-200';
                                            elseif ($st === 'Cuti') $bBg = 'bg-amber-100 text-amber-700 border-amber-200';
                                            elseif ($st === 'Drop Out') $bBg = 'bg-red-100 text-red-700 border-red-200';
                                        ?>
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold border <?= $bBg ?>">
                                            <?= $st ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('statusChart').getContext('2d');
    const dataVals = [
        <?= $stats['Aktif'] ?? 0 ?>,
        <?= $stats['Cuti'] ?? 0 ?>,
        <?= $stats['Lulus'] ?? 0 ?>,
        <?= $stats['Drop Out'] ?? 0 ?>
    ];
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Aktif', 'Cuti', 'Lulus', 'Drop Out'],
            datasets: [{
                data: dataVals,
                backgroundColor: ['#10b981', '#f59e0b', '#3b82f6', '#ef4444'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11, family: "'Inter', sans-serif" } } }
            }
        }
    });
});
</script>

<?php require_once dirname(__DIR__, 2) . '/layouts/admin/footer.php'; ?>

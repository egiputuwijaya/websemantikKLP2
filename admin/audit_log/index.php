<?php
/**
 * admin/audit_log/index.php — Modul Audit Log System
 */
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';

// Akses eksklusif untuk Admin
require_role([1]);

// Tangkap filter
$filterAksi     = $_GET['aksi'] ?? '';
$filterStart    = $_GET['start_date'] ?? '';
$filterEnd      = $_GET['end_date'] ?? '';
$filterUsername = sanitize($_GET['username'] ?? '');

$params = [];
$whereClauses = [];

if ($filterAksi !== '') {
    $whereClauses[] = "a.aksi = :aksi";
    $params[':aksi'] = $filterAksi;
}
if ($filterStart !== '') {
    $whereClauses[] = "DATE(a.waktu) >= :start";
    $params[':start'] = $filterStart;
}
if ($filterEnd !== '') {
    $whereClauses[] = "DATE(a.waktu) <= :end";
    $params[':end'] = $filterEnd;
}
if ($filterUsername !== '') {
    $whereClauses[] = "p.username LIKE :uname";
    $params[':uname'] = "%" . $filterUsername . "%";
}

$whereSql = '';
if (!empty($whereClauses)) {
    $whereSql = "WHERE " . implode(" AND ", $whereClauses);
}

try {
    $sql = "SELECT a.*, p.username, p.nama_lengkap 
            FROM audit_log a 
            LEFT JOIN pengguna p ON a.id_pengguna = p.id_pengguna 
            $whereSql 
            ORDER BY a.waktu DESC LIMIT 500"; // Batasi 500 log terakhir agar tidak terlalu berat
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $logs = [];
}

$pageTitle  = 'Audit Log System';
$activeMenu = 'audit_log';
?>
<?php require_once dirname(__DIR__, 2) . '/layouts/admin/header.php'; ?>

<section class="mb-8">
    <div class="mb-6">
        <h2 class="font-heading font-bold text-2xl text-slate-900">Audit Log</h2>
        <p class="text-sm text-slate-500 mt-1">Pantau seluruh riwayat aktivitas yang terjadi pada sistem.</p>
    </div>

    <?= render_flash_alert() ?>

    <div class="bg-white rounded-md shadow-sm border border-slate-200 overflow-hidden mb-6">
        <!-- Filter Form -->
        <div class="p-5 border-b border-slate-100 bg-slate-50/50">
            <form action="" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                <div>
                    <label for="start_date" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Mulai Tanggal</label>
                    <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($filterStart) ?>" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-md text-sm focus:ring-2 focus:ring-academic-100 focus:border-academic-500">
                </div>
                <div>
                    <label for="end_date" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Sampai Tanggal</label>
                    <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($filterEnd) ?>" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-md text-sm focus:ring-2 focus:ring-academic-100 focus:border-academic-500">
                </div>
                <div>
                    <label for="aksi" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Jenis Aksi</label>
                    <select id="aksi" name="aksi" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-md text-sm focus:ring-2 focus:ring-academic-100 focus:border-academic-500">
                        <option value="">Semua Aksi</option>
                        <option value="LOGIN" <?= $filterAksi === 'LOGIN' ? 'selected' : '' ?>>LOGIN</option>
                        <option value="LOGOUT" <?= $filterAksi === 'LOGOUT' ? 'selected' : '' ?>>LOGOUT</option>
                        <option value="INSERT" <?= $filterAksi === 'INSERT' ? 'selected' : '' ?>>INSERT</option>
                        <option value="UPDATE" <?= $filterAksi === 'UPDATE' ? 'selected' : '' ?>>UPDATE</option>
                        <option value="DELETE" <?= $filterAksi === 'DELETE' ? 'selected' : '' ?>>DELETE</option>
                        <option value="UPDATE_PASSWORD" <?= $filterAksi === 'UPDATE_PASSWORD' ? 'selected' : '' ?>>UPDATE_PASSWORD</option>
                    </select>
                </div>
                <div>
                    <label for="username" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Username Pelaku</label>
                    <input type="text" id="username" name="username" placeholder="Cari username..." value="<?= htmlspecialchars($filterUsername) ?>" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-md text-sm focus:ring-2 focus:ring-academic-100 focus:border-academic-500">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-academic-600 hover:bg-academic-700 text-white text-sm font-semibold rounded-md transition-colors focus:ring-2 focus:ring-academic-200">
                        <i class="fa-solid fa-filter mr-1"></i> Filter
                    </button>
                    <?php if (!empty($_GET)): ?>
                        <a href="<?= url('admin/audit_log/index.php') ?>" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-semibold rounded-md transition-colors focus:ring-2 focus:ring-slate-200" title="Reset">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm table-hover">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-100">
                    <tr>
                        <th class="text-left font-semibold px-4 py-3 w-40">Waktu</th>
                        <th class="text-left font-semibold px-4 py-3 w-48">Pelaku</th>
                        <th class="text-center font-semibold px-4 py-3 w-28">Aksi</th>
                        <th class="text-left font-semibold px-4 py-3 w-40">Target</th>
                        <th class="text-left font-semibold px-4 py-3">IP & Client</th>
                        <th class="text-center font-semibold px-4 py-3 w-24">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-500">
                                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                                    <i class="fa-solid fa-file-shield text-xl text-slate-400"></i>
                                </div>
                                Tidak ada catatan log yang sesuai.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $row): 
                            $bgAksi = 'bg-slate-100 text-slate-600';
                            $aksi = $row['aksi'];
                            if ($aksi === 'LOGIN') $bgAksi = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                            elseif ($aksi === 'LOGOUT') $bgAksi = 'bg-slate-100 text-slate-700 border-slate-200';
                            elseif ($aksi === 'INSERT') $bgAksi = 'bg-blue-100 text-blue-700 border-blue-200';
                            elseif ($aksi === 'UPDATE') $bgAksi = 'bg-amber-100 text-amber-700 border-amber-200';
                            elseif ($aksi === 'DELETE') $bgAksi = 'bg-red-100 text-red-700 border-red-200';
                            elseif ($aksi === 'UPDATE_PASSWORD') $bgAksi = 'bg-purple-100 text-purple-700 border-purple-200';
                        ?>
                            <tr>
                                <td class="px-4 py-3 align-top whitespace-nowrap">
                                    <span class="block font-semibold text-slate-700"><?= date('d M Y', strtotime($row['waktu'])) ?></span>
                                    <span class="text-xs text-slate-500 font-mono"><?= date('H:i:s', strtotime($row['waktu'])) ?></span>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <?php if ($row['id_pengguna']): ?>
                                        <div class="font-semibold text-slate-800"><?= htmlspecialchars($row['nama_lengkap'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="text-xs font-mono text-slate-500">@<?= htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php else: ?>
                                        <span class="text-slate-400 italic">Sistem / Guest</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 align-top text-center">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold border <?= $bgAksi ?>">
                                        <?= $aksi ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <?php if ($row['tabel_nama']): ?>
                                        <div class="text-xs font-semibold uppercase text-slate-700"><?= htmlspecialchars($row['tabel_nama'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="text-xs font-mono text-slate-500">ID: <?= htmlspecialchars($row['record_id'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php else: ?>
                                        <span class="text-slate-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <div class="text-xs font-mono text-slate-600"><?= htmlspecialchars($row['ip_address'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="text-[10px] text-slate-400 truncate max-w-[200px] mt-0.5" title="<?= htmlspecialchars($row['user_agent'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($row['user_agent'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3 align-top text-center">
                                    <?php if ($row['data_lama'] || $row['data_baru']): ?>
                                        <button type="button" 
                                                onclick='openDetailModal(<?= json_encode($row['data_lama']) ?>, <?= json_encode($row['data_baru']) ?>, "<?= htmlspecialchars($row['tabel_nama']) ?>")'
                                                class="w-7 h-7 rounded bg-slate-100 text-slate-500 hover:bg-academic-100 hover:text-academic-700 transition-colors inline-flex items-center justify-center">
                                            <i class="fa-solid fa-eye text-xs"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-slate-300">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Modal Detail JSON -->
<div id="modal-detail" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeDetailModal()"></div>
    <!-- Modal Content -->
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden animate-in zoom-in-95 duration-200">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="font-heading font-bold text-lg text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-magnifying-glass-chart text-academic-600"></i> Detail Perubahan Data <span id="modal-table-badge" class="text-xs bg-slate-200 text-slate-600 px-2 py-0.5 rounded ml-2 uppercase"></span>
                </h3>
                <button type="button" onclick="closeDetailModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:bg-slate-200 hover:text-slate-700 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="p-6 flex-1 overflow-y-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span> Data Lama (Sebelumnya)
                        </h4>
                        <pre id="json-old" class="bg-slate-800 text-slate-300 p-4 rounded-xl text-xs font-mono overflow-x-auto whitespace-pre-wrap min-h-[150px] shadow-inner"></pre>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-academic-500"></span> Data Baru (Setelahnya)
                        </h4>
                        <pre id="json-new" class="bg-slate-800 text-emerald-400 p-4 rounded-xl text-xs font-mono overflow-x-auto whitespace-pre-wrap min-h-[150px] shadow-inner"></pre>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex justify-end">
                <button type="button" onclick="closeDetailModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-bold rounded-xl transition-colors">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function openDetailModal(oldDataStr, newDataStr, tableTarget) {
    const modal = document.getElementById('modal-detail');
    const jsonOld = document.getElementById('json-old');
    const jsonNew = document.getElementById('json-new');
    const badge = document.getElementById('modal-table-badge');
    
    badge.innerText = tableTarget;

    try {
        let oldObj = oldDataStr ? JSON.parse(oldDataStr) : null;
        jsonOld.innerText = oldObj ? JSON.stringify(oldObj, null, 4) : '(Tidak ada data)';
    } catch(e) { jsonOld.innerText = oldDataStr || '(Tidak ada data)'; }

    try {
        let newObj = newDataStr ? JSON.parse(newDataStr) : null;
        jsonNew.innerText = newObj ? JSON.stringify(newObj, null, 4) : '(Tidak ada data)';
    } catch(e) { jsonNew.innerText = newDataStr || '(Tidak ada data)'; }

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDetailModal() {
    document.getElementById('modal-detail').classList.add('hidden');
    document.body.style.overflow = '';
}
</script>

<?php require_once dirname(__DIR__, 2) . '/layouts/admin/footer.php'; ?>

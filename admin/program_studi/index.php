<?php
/**
 * admin/program_studi/index.php — Daftar Program Studi
 */
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';

require_role([1]);

// Filter Fakultas
$filterFakultas = (int)($_GET['fakultas'] ?? 0);

try {
    // Ambil daftar fakultas untuk dropdown filter
    $stmtFak = $pdo->query("SELECT id_fakultas, nama_fakultas FROM fakultas ORDER BY nama_fakultas ASC");
    $listFakultas = $stmtFak->fetchAll();

    // Query daftar prodi
    $sql = "SELECT p.*, f.nama_fakultas 
            FROM program_studi p 
            JOIN fakultas f ON p.id_fakultas = f.id_fakultas ";
    $params = [];
    
    if ($filterFakultas > 0) {
        $sql .= " WHERE p.id_fakultas = :id_fakultas ";
        $params[':id_fakultas'] = $filterFakultas;
    }
    
    $sql .= " ORDER BY f.nama_fakultas ASC, p.nama_program_studi ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $prodiData = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $listFakultas = [];
    $prodiData = [];
}

$pageTitle  = 'Kelola Program Studi';
$activeMenu = 'prodi';
?>
<?php require_once dirname(__DIR__, 2) . '/layouts/admin/header.php'; ?>

<section class="mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="font-heading font-bold text-2xl text-slate-900">Daftar Program Studi</h2>
            <p class="text-sm text-slate-500 mt-1">Kelola data seluruh program studi di universitas.</p>
        </div>
        <a href="<?= url('admin/program_studi/create.php') ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-academic-600 text-white text-sm font-semibold rounded-md hover:bg-academic-700 transition-colors focus:ring-4 focus:ring-academic-100">
            <i class="fa-solid fa-plus"></i> Tambah Prodi
        </a>
    </div>

    <?= render_flash_alert() ?>

    <div class="bg-white rounded-md shadow-sm border border-slate-200 mb-6">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 rounded-t-md flex items-center justify-between flex-wrap gap-4">
            <form action="" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                <label for="fakultas" class="text-sm font-semibold text-slate-700 hidden sm:block">Filter Fakultas:</label>
                <select name="fakultas" id="fakultas" onchange="this.form.submit()" class="w-full sm:w-64 px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-academic-100 focus:border-academic-500">
                    <option value="0">-- Semua Fakultas --</option>
                    <?php foreach ($listFakultas as $fak): ?>
                        <option value="<?= $fak['id_fakultas'] ?>" <?= $filterFakultas == $fak['id_fakultas'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($fak['nama_fakultas'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($filterFakultas > 0): ?>
                    <a href="<?= url('admin/program_studi/index.php') ?>" class="px-3 py-2 text-sm text-slate-500 hover:text-slate-700" title="Reset Filter"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm table-hover">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-100">
                    <tr>
                        <th class="text-center font-semibold px-6 py-3 w-16">No</th>
                        <th class="text-left font-semibold px-6 py-3">Fakultas</th>
                        <th class="text-left font-semibold px-6 py-3 w-32">Kode Prodi</th>
                        <th class="text-left font-semibold px-6 py-3">Nama Program Studi</th>
                        <th class="text-center font-semibold px-6 py-3 w-24">Jenjang</th>
                        <th class="text-center font-semibold px-6 py-3 w-28">Status</th>
                        <th class="text-right font-semibold px-6 py-3 w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($prodiData)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500">
                                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                                    <i class="fa-solid fa-book-open-reader text-xl text-slate-400"></i>
                                </div>
                                Belum ada data program studi yang sesuai.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($prodiData as $row): 
                            $status = $row['status_aktif'];
                            $badge  = $status === 'Aktif' ? 'bg-elegant-100 text-elegant-800' : 'bg-slate-200 text-slate-600';
                        ?>
                            <tr>
                                <td class="px-6 py-4 text-center text-slate-500"><?= $no++ ?></td>
                                <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($row['nama_fakultas'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-6 py-4 font-mono font-bold text-academic-700"><?= htmlspecialchars($row['kode_program_studi'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-6 py-4 font-semibold text-slate-900"><?= htmlspecialchars($row['nama_program_studi'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold border border-slate-200 bg-slate-50 text-slate-700">
                                        <?= htmlspecialchars($row['jenjang'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg text-[11px] font-bold <?= $badge ?>">
                                        <?= $status ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="<?= url('admin/program_studi/edit.php?id=' . $row['id_program_studi']) ?>" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-100 transition-colors" title="Edit">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </a>
                                        <form action="<?= url('admin/program_studi/process.php') ?>" method="POST" class="inline-block">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id_program_studi" value="<?= $row['id_program_studi'] ?>">
                                            <button type="submit" data-confirm="Peringatan: Menghapus Program Studi mungkin akan gagal jika ada mahasiswa yang terdaftar di dalamnya. Lanjutkan?" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition-colors" title="Hapus">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require_once dirname(__DIR__, 2) . '/layouts/admin/footer.php'; ?>

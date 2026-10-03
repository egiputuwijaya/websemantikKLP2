<?php
/**
 * admin/fakultas/index.php — Daftar Fakultas
 */
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';

require_role([1]);

try {
    $sql = "SELECT f.id_fakultas, f.kode_fakultas, f.nama_fakultas, 
                   (SELECT COUNT(*) FROM program_studi p WHERE p.id_fakultas = f.id_fakultas) AS jumlah_prodi
            FROM fakultas f
            ORDER BY f.kode_fakultas ASC";
    $stmt = $pdo->query($sql);
    $fakultasData = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $fakultasData = [];
}

$pageTitle  = 'Kelola Fakultas';
$activeMenu = 'fakultas';
?>
<?php require_once dirname(__DIR__, 2) . '/layouts/admin/header.php'; ?>

<section class="mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="font-heading font-bold text-2xl text-slate-900">Daftar Fakultas</h2>
            <p class="text-sm text-slate-500 mt-1">Kelola data fakultas yang ada di lingkungan universitas.</p>
        </div>
        <a href="<?= url('admin/fakultas/create.php') ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-academic-600 text-white text-sm font-semibold rounded-md hover:bg-academic-700 transition-colors focus:ring-4 focus:ring-academic-100">
            <i class="fa-solid fa-plus"></i> Tambah Fakultas
        </a>
    </div>

    <?= render_flash_alert() ?>

    <div class="bg-white rounded-md shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-hover">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-100">
                    <tr>
                        <th class="text-center font-semibold px-6 py-3 w-16">No</th>
                        <th class="text-left font-semibold px-6 py-3 w-32">Kode</th>
                        <th class="text-left font-semibold px-6 py-3">Nama Fakultas</th>
                        <th class="text-center font-semibold px-6 py-3 w-32">Jumlah Prodi</th>
                        <th class="text-right font-semibold px-6 py-3 w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($fakultasData)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                                    <i class="fa-solid fa-building-columns text-xl text-slate-400"></i>
                                </div>
                                Belum ada data fakultas.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($fakultasData as $row): ?>
                            <tr>
                                <td class="px-6 py-4 text-center text-slate-500"><?= $no++ ?></td>
                                <td class="px-6 py-4 font-mono font-bold text-academic-700"><?= htmlspecialchars($row['kode_fakultas'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-6 py-4 font-semibold text-slate-900"><?= htmlspecialchars($row['nama_fakultas'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[2rem] px-2 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold">
                                        <?= (int) $row['jumlah_prodi'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="<?= url('admin/fakultas/edit.php?id=' . $row['id_fakultas']) ?>" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-100 transition-colors" title="Edit">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </a>
                                        <!-- Form Delete -->
                                        <?php if ($row['jumlah_prodi'] == 0): ?>
                                        <form action="<?= url('admin/fakultas/process.php') ?>" method="POST" class="inline-block">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id_fakultas" value="<?= $row['id_fakultas'] ?>">
                                            <button type="submit" data-confirm="Apakah Anda yakin ingin menghapus fakultas ini?" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition-colors" title="Hapus">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </form>
                                        <?php else: ?>
                                        <button type="button" onclick="alert('Tidak dapat menghapus fakultas yang masih memiliki Program Studi. Hapus atau pindahkan Prodi terlebih dahulu.')" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-400 flex items-center justify-center cursor-not-allowed" title="Hapus (Terkunci)">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                        <?php endif; ?>
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

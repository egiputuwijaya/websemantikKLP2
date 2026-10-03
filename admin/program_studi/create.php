<?php
/**
 * admin/program_studi/create.php — Form tambah prodi
 */
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';

require_role([1]);

try {
    $stmt = $pdo->query("SELECT id_fakultas, nama_fakultas FROM fakultas ORDER BY nama_fakultas ASC");
    $listFakultas = $stmt->fetchAll();
} catch (PDOException $e) {
    $listFakultas = [];
}

if (empty($listFakultas)) {
    set_flash_message('warning', 'Anda harus memiliki minimal 1 Fakultas sebelum menambahkan Program Studi.');
    redirect(url('admin/fakultas/create.php'));
    exit;
}

$pageTitle  = 'Tambah Program Studi';
$activeMenu = 'prodi';
?>
<?php require_once dirname(__DIR__, 2) . '/layouts/admin/header.php'; ?>

<section class="max-w-3xl mb-8">
    <div class="mb-6 flex items-center gap-3">
        <a href="<?= url('admin/program_studi/index.php') ?>" class="w-10 h-10 rounded-md bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="font-heading font-bold text-2xl text-slate-900">Tambah Program Studi</h2>
            <p class="text-sm text-slate-500 mt-0.5">Daftarkan prodi baru ke dalam fakultas.</p>
        </div>
    </div>

    <?= render_flash_alert() ?>

    <div class="bg-white rounded-md shadow-sm border border-slate-200">
        <form action="<?= url('admin/program_studi/process.php') ?>" method="POST" class="p-6 sm:p-8">
            <input type="hidden" name="action" value="create">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                <!-- Fakultas -->
                <div class="sm:col-span-2">
                    <label for="id_fakultas" class="block text-sm font-semibold text-slate-700 mb-1.5">Pilih Fakultas <span class="text-red-500">*</span></label>
                    <select name="id_fakultas" id="id_fakultas" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                        <option value="">-- Pilih Fakultas --</option>
                        <?php foreach ($listFakultas as $fak): ?>
                            <option value="<?= $fak['id_fakultas'] ?>">
                                <?= htmlspecialchars($fak['nama_fakultas'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Kode Prodi -->
                <div>
                    <label for="kode_program_studi" class="block text-sm font-semibold text-slate-700 mb-1.5">Kode Prodi <span class="text-red-500">*</span></label>
                    <input type="text" id="kode_program_studi" name="kode_program_studi" required maxlength="20" placeholder="Contoh: TI, SI, AKT"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors uppercase">
                    <p class="text-[11px] text-slate-500 mt-1">Harus unik di dalam fakultas yang sama.</p>
                </div>

                <!-- Jenjang -->
                <div>
                    <label for="jenjang" class="block text-sm font-semibold text-slate-700 mb-1.5">Jenjang <span class="text-red-500">*</span></label>
                    <select name="jenjang" id="jenjang" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                        <option value="D3">Diploma 3 (D3)</option>
                        <option value="D4">Diploma 4 (D4)</option>
                        <option value="S1" selected>Strata 1 (S1)</option>
                        <option value="S2">Strata 2 (S2)</option>
                        <option value="S3">Strata 3 (S3)</option>
                    </select>
                </div>

                <!-- Nama Prodi -->
                <div class="sm:col-span-2">
                    <label for="nama_program_studi" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Program Studi <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_program_studi" name="nama_program_studi" required maxlength="200" placeholder="Contoh: Teknik Informatika"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                </div>

                <!-- Status Aktif -->
                <div class="sm:col-span-2">
                    <label for="status_aktif" class="block text-sm font-semibold text-slate-700 mb-1.5">Status <span class="text-red-500">*</span></label>
                    <select name="status_aktif" id="status_aktif" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                        <option value="Aktif">Aktif</option>
                        <option value="Tidak Aktif">Tidak Aktif</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-academic-600 rounded-md hover:bg-academic-700 transition-colors focus:ring-4 focus:ring-academic-100">
                    <i class="fa-solid fa-save mr-1.5"></i> Simpan Data
                </button>
            </div>
        </form>
    </div>
</section>

<?php require_once dirname(__DIR__, 2) . '/layouts/admin/footer.php'; ?>

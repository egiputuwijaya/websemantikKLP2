<?php
/**
 * admin/fakultas/create.php — Form tambah fakultas
 */
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';

require_role([1]);

// Ambil ID Universitas default (karena sistem ini single-university)
$stmtUniv = $pdo->query("SELECT id_universitas, nama_universitas FROM universitas ORDER BY id_universitas ASC LIMIT 1");
$univ = $stmtUniv->fetch();

if (!$univ) {
    set_flash_message('warning', 'Harap isi Profil Universitas terlebih dahulu sebelum menambah Fakultas.');
    redirect(url('admin/universitas/edit.php'));
    exit;
}

$pageTitle  = 'Tambah Fakultas';
$activeMenu = 'fakultas';
?>
<?php require_once dirname(__DIR__, 2) . '/layouts/admin/header.php'; ?>

<section class="max-w-2xl mb-8">
    <div class="mb-6 flex items-center gap-3">
        <a href="<?= url('admin/fakultas/index.php') ?>" class="w-10 h-10 rounded-md bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="font-heading font-bold text-2xl text-slate-900">Tambah Fakultas Baru</h2>
            <p class="text-sm text-slate-500 mt-0.5">Masukkan kode dan nama fakultas.</p>
        </div>
    </div>

    <?= render_flash_alert() ?>

    <div class="bg-white rounded-md shadow-sm border border-slate-200">
        <form action="<?= url('admin/fakultas/process.php') ?>" method="POST" class="p-6 sm:p-8">
            <input type="hidden" name="action" value="create">
            <!-- Hidden field for universitas -->
            <input type="hidden" name="id_universitas" value="<?= (int) $univ['id_universitas'] ?>">

            <div class="space-y-6">
                <!-- Info Universitas Readonly -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Universitas</label>
                    <input type="text" disabled value="<?= htmlspecialchars($univ['nama_universitas'], ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-md text-sm text-slate-500 cursor-not-allowed">
                </div>

                <div>
                    <label for="kode_fakultas" class="block text-sm font-semibold text-slate-700 mb-1.5">Kode Fakultas <span class="text-red-500">*</span></label>
                    <input type="text" id="kode_fakultas" name="kode_fakultas" required maxlength="20" placeholder="Contoh: FT, FKIP, FEB"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors uppercase">
                    <p class="text-[11px] text-slate-500 mt-1">Kode harus unik dan maksimal 20 karakter.</p>
                </div>

                <div>
                    <label for="nama_fakultas" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Fakultas <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_fakultas" name="nama_fakultas" required maxlength="200" placeholder="Contoh: Fakultas Teknik"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                </div>
            </div>

            <div class="mt-8 pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-academic-600 rounded-md hover:bg-academic-700 transition-colors focus:ring-4 focus:ring-academic-100">
                    <i class="fa-solid fa-plus mr-1.5"></i> Tambah Data
                </button>
            </div>
        </form>
    </div>
</section>

<?php require_once dirname(__DIR__, 2) . '/layouts/admin/footer.php'; ?>

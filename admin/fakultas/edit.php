<?php
/**
 * admin/fakultas/edit.php — Form edit fakultas
 */
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';

require_role([1]);

$id = (int)($_GET['id'] ?? 0);

try {
    $stmt = $pdo->prepare("SELECT * FROM fakultas WHERE id_fakultas = :id");
    $stmt->execute([':id' => $id]);
    $fakultas = $stmt->fetch();
} catch (PDOException $e) {
    $fakultas = null;
}

if (!$fakultas) {
    set_flash_message('danger', 'Data fakultas tidak ditemukan.');
    redirect(url('admin/fakultas/index.php'));
    exit;
}

$pageTitle  = 'Edit Fakultas';
$activeMenu = 'fakultas';
?>
<?php require_once dirname(__DIR__, 2) . '/layouts/admin/header.php'; ?>

<section class="max-w-2xl mb-8">
    <div class="mb-6 flex items-center gap-3">
        <a href="<?= url('admin/fakultas/index.php') ?>" class="w-10 h-10 rounded-md bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="font-heading font-bold text-2xl text-slate-900">Edit Data Fakultas</h2>
            <p class="text-sm text-slate-500 mt-0.5">Perbarui kode atau nama fakultas.</p>
        </div>
    </div>

    <?= render_flash_alert() ?>

    <div class="bg-white rounded-md shadow-sm border border-slate-200">
        <form action="<?= url('admin/fakultas/process.php') ?>" method="POST" class="p-6 sm:p-8">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id_fakultas" value="<?= $id ?>">
            
            <div class="space-y-6">
                <div>
                    <label for="kode_fakultas" class="block text-sm font-semibold text-slate-700 mb-1.5">Kode Fakultas <span class="text-red-500">*</span></label>
                    <input type="text" id="kode_fakultas" name="kode_fakultas" required maxlength="20"
                           value="<?= htmlspecialchars($fakultas['kode_fakultas'], ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors uppercase">
                </div>

                <div>
                    <label for="nama_fakultas" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Fakultas <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_fakultas" name="nama_fakultas" required maxlength="200"
                           value="<?= htmlspecialchars($fakultas['nama_fakultas'], ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                </div>
            </div>

            <div class="mt-8 pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-academic-600 rounded-md hover:bg-academic-700 transition-colors focus:ring-4 focus:ring-academic-100">
                    <i class="fa-solid fa-save mr-1.5"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</section>

<?php require_once dirname(__DIR__, 2) . '/layouts/admin/footer.php'; ?>

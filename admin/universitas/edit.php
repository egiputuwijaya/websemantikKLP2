<?php
/**
 * admin/universitas/edit.php — Form edit profil universitas
 */
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';

require_role([1]); // Akses hanya untuk Admin

try {
    $stmt = $pdo->prepare("SELECT * FROM universitas LIMIT 1");
    $stmt->execute();
    $univ = $stmt->fetch();
} catch (PDOException $e) {
    $univ = null;
}

if (!$univ) {
    set_flash_message('danger', 'Data Universitas tidak ditemukan.');
    redirect(url('admin/universitas/index.php'));
    exit;
}

$pageTitle  = 'Edit Profil Universitas';
$activeMenu = 'universitas';
?>

<?php require_once dirname(__DIR__, 2) . '/layouts/admin/header.php'; ?>

<section class="bg-white rounded-md shadow-sm border border-slate-200 overflow-hidden mb-8 max-w-3xl">
    <div class="px-6 py-5 border-b border-slate-100">
        <h3 class="font-heading font-bold text-lg text-slate-900">Perbarui Profil Universitas</h3>
        <p class="text-sm text-slate-500 mt-1">Pastikan informasi di bawah ini akurat dan valid karena akan tampil di halaman publik.</p>
    </div>

    <form action="<?= url('admin/universitas/process.php') ?>" method="POST" class="p-6">
        <?= csrf_field() ?>
        
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id_universitas" value="<?= (int) $univ['id_universitas'] ?>">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
            <!-- Kode -->
            <div>
                <label for="kode_universitas" class="block text-sm font-semibold text-slate-700 mb-1.5">Kode / Singkatan <span class="text-red-500">*</span></label>
                <input type="text" id="kode_universitas" name="kode_universitas" required maxlength="20"
                       value="<?= htmlspecialchars($univ['kode_universitas'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
            </div>

            <!-- Nama -->
            <div>
                <label for="nama_universitas" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Universitas <span class="text-red-500">*</span></label>
                <input type="text" id="nama_universitas" name="nama_universitas" required maxlength="200"
                       value="<?= htmlspecialchars($univ['nama_universitas'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
            </div>

            <!-- Slogan -->
            <div class="sm:col-span-2">
                <label for="slogan" class="block text-sm font-semibold text-slate-700 mb-1.5">Slogan / Motto</label>
                <input type="text" id="slogan" name="slogan" maxlength="255"
                       value="<?= htmlspecialchars($univ['slogan'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
            </div>

            <!-- Alamat -->
            <div class="sm:col-span-2">
                <label for="alamat" class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat Jalan</label>
                <textarea id="alamat" name="alamat" rows="2"
                          class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors"><?= htmlspecialchars($univ['alamat'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <!-- Kota & Provinsi -->
            <div>
                <label for="kota" class="block text-sm font-semibold text-slate-700 mb-1.5">Kota / Kabupaten</label>
                <input type="text" id="kota" name="kota" maxlength="100"
                       value="<?= htmlspecialchars($univ['kota'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
            </div>
            <div>
                <label for="provinsi" class="block text-sm font-semibold text-slate-700 mb-1.5">Provinsi</label>
                <input type="text" id="provinsi" name="provinsi" maxlength="100"
                       value="<?= htmlspecialchars($univ['provinsi'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
            </div>

            <!-- Kode Pos & Telepon -->
            <div>
                <label for="kode_pos" class="block text-sm font-semibold text-slate-700 mb-1.5">Kode Pos</label>
                <input type="text" id="kode_pos" name="kode_pos" maxlength="10"
                       value="<?= htmlspecialchars($univ['kode_pos'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
            </div>
            <div>
                <label for="telepon" class="block text-sm font-semibold text-slate-700 mb-1.5">Telepon</label>
                <input type="text" id="telepon" name="telepon" maxlength="50"
                       value="<?= htmlspecialchars($univ['telepon'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
            </div>

            <!-- Email & Website -->
            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Email Resmi</label>
                <input type="email" id="email" name="email" maxlength="150"
                       value="<?= htmlspecialchars($univ['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
            </div>
            <div>
                <label for="website" class="block text-sm font-semibold text-slate-700 mb-1.5">Website Utama</label>
                <input type="url" id="website" name="website" maxlength="255" placeholder="https://..."
                       value="<?= htmlspecialchars($univ['website'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 mt-2">
            <a href="<?= url('admin/universitas/index.php') ?>" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 rounded-md hover:bg-slate-200 transition-colors">
                Batal
            </a>
            <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-academic-600 rounded-md hover:bg-academic-700 transition-colors focus:ring-4 focus:ring-academic-100">
                <i class="fa-solid fa-save mr-1.5"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</section>

<?php require_once dirname(__DIR__, 2) . '/layouts/admin/footer.php'; ?>

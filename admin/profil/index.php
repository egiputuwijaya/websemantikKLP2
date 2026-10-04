<?php
/**
 * admin/profil/index.php — Form Edit Profil Pribadi
 */
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';

// Semua role berhak mengubah profil mereka sendiri
require_role([1, 2, 3, 4, 5]);

$userActive = get_user_login();
$id = (int)$userActive['id_pengguna'];

try {
    $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE id_pengguna = :id");
    $stmt->execute([':id' => $id]);
    $profil = $stmt->fetch();
} catch (PDOException $e) {
    $profil = null;
}

if (!$profil) {
    set_flash_message('danger', 'Data akun tidak ditemukan.');
    redirect(url('auth/logout.php'));
    exit;
}

$pageTitle  = 'Pengaturan Profil';
$activeMenu = 'profil';
?>
<?php require_once dirname(__DIR__, 2) . '/layouts/admin/header.php'; ?>

<section class="max-w-3xl mb-8">
    <div class="mb-6">
        <h2 class="font-heading font-bold text-2xl text-slate-900">Pengaturan Akun</h2>
        <p class="text-sm text-slate-500 mt-1">Perbarui informasi dasar profil Anda.</p>
    </div>

    <!-- Tab Navigasi -->
    <div class="flex items-center gap-4 border-b border-slate-200 mb-6">
        <a href="<?= url('admin/profil/index.php') ?>" class="px-4 py-3 text-sm font-bold text-academic-600 border-b-2 border-academic-600">
            Profil Dasar
        </a>
        <a href="<?= url('admin/profil/password.php') ?>" class="px-4 py-3 text-sm font-semibold text-slate-500 hover:text-slate-700 transition-colors">
            Ubah Password
        </a>
    </div>

    <?= render_flash_alert() ?>

    <div class="bg-white rounded-md shadow-sm border border-slate-200 overflow-hidden">
        <form action="<?= url('admin/profil/process.php') ?>" method="POST" class="p-6 sm:p-8">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update_profile">
            
            <div class="space-y-6">
                <!-- Info Username (Readonly) -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Username (Read-Only)</label>
                    <input type="text" disabled value="<?= htmlspecialchars($profil['username'], ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-md text-sm text-slate-500 cursor-not-allowed">
                    <p class="text-[11px] text-slate-500 mt-1">Username tidak dapat diubah sendiri. Hubungi Administrator jika terdapat kesalahan.</p>
                </div>

                <div>
                    <label for="nama_lengkap" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" required maxlength="200"
                           value="<?= htmlspecialchars($profil['nama_lengkap'], ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat Email</label>
                    <input type="email" id="email" name="email" maxlength="150"
                           value="<?= htmlspecialchars($profil['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
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

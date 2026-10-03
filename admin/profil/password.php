<?php
/**
 * admin/profil/password.php — Form Ubah Password
 */
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';

require_role([1, 2, 3, 4, 5]);

$pageTitle  = 'Ubah Password';
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
        <a href="<?= url('admin/profil/index.php') ?>" class="px-4 py-3 text-sm font-semibold text-slate-500 hover:text-slate-700 transition-colors">
            Profil Dasar
        </a>
        <a href="<?= url('admin/profil/password.php') ?>" class="px-4 py-3 text-sm font-bold text-academic-600 border-b-2 border-academic-600">
            Ubah Password
        </a>
    </div>

    <?= render_flash_alert() ?>

    <div class="bg-white rounded-md shadow-sm border border-slate-200 overflow-hidden">
        <form action="<?= url('admin/profil/process.php') ?>" method="POST" class="p-6 sm:p-8">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update_password">
            
            <div class="space-y-6">
                <div>
                    <label for="password_lama" class="block text-sm font-semibold text-slate-700 mb-1.5">Kata Sandi Saat Ini <span class="text-red-500">*</span></label>
                    <input type="password" id="password_lama" name="password_lama" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                </div>

                <hr class="border-slate-100">

                <div>
                    <label for="password_baru" class="block text-sm font-semibold text-slate-700 mb-1.5">Kata Sandi Baru <span class="text-red-500">*</span></label>
                    <input type="password" id="password_baru" name="password_baru" required minlength="6"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                    <p class="text-[11px] text-slate-500 mt-1">Minimal 6 karakter.</p>
                </div>

                <div>
                    <label for="password_konfirmasi" class="block text-sm font-semibold text-slate-700 mb-1.5">Ulangi Kata Sandi Baru <span class="text-red-500">*</span></label>
                    <input type="password" id="password_konfirmasi" name="password_konfirmasi" required minlength="6"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                </div>
            </div>

            <div class="mt-8 pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-academic-600 rounded-md hover:bg-academic-700 transition-colors focus:ring-4 focus:ring-academic-100">
                    <i class="fa-solid fa-key mr-1.5"></i> Perbarui Kata Sandi
                </button>
            </div>
        </form>
    </div>
</section>

<?php require_once dirname(__DIR__, 2) . '/layouts/admin/footer.php'; ?>

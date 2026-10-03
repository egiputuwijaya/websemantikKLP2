<?php
/**
 * admin/profil-saya/edit.php — Form edit kontak/pribadi oleh mahasiswa
 */
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';

require_role([5]);

$userActive = get_user_login();
$idPengguna = (int)$userActive['id_pengguna'];

try {
    $sql = "SELECT m.*, p.email, p.no_hp 
            FROM mahasiswa m 
            JOIN pengguna p ON m.id_pengguna = p.id_pengguna 
            WHERE m.id_pengguna = :id_pengguna";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_pengguna' => $idPengguna]);
    $mhs = $stmt->fetch();
} catch (PDOException $e) {
    $mhs = null;
}

if (!$mhs) {
    set_flash_message('danger', 'Data mahasiswa tidak ditemukan.');
    redirect(url('admin/profil-saya/index.php'));
    exit;
}

$pageTitle  = 'Perbarui Data Pribadi';
$activeMenu = 'profil_saya';
?>
<?php require_once dirname(__DIR__, 2) . '/layouts/admin/header.php'; ?>

<section class="max-w-3xl mb-8">
    <div class="mb-6 flex items-center gap-3">
        <a href="<?= url('admin/profil-saya/index.php') ?>" class="w-10 h-10 rounded-md bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="font-heading font-bold text-2xl text-slate-900">Perbarui Data Diri</h2>
            <p class="text-sm text-slate-500 mt-0.5">Ubah informasi alamat dan kontak Anda.</p>
        </div>
    </div>

    <?= render_flash_alert() ?>

    <div class="bg-blue-50 border border-blue-200 text-blue-800 text-sm p-4 rounded-md mb-6 flex gap-3">
        <i class="fa-solid fa-circle-info mt-0.5 text-blue-600"></i>
        <p>Anda hanya diizinkan untuk mengubah data kontak (Email, No HP, Alamat) dan Tempat/Tanggal Lahir. Untuk mengubah NPM, Nama Lengkap, atau Program Studi, silakan hubungi <b>Operator Program Studi</b> Anda.</p>
    </div>

    <div class="bg-white rounded-md shadow-card border border-slate-100 overflow-hidden">
        <form action="<?= url('admin/profil-saya/process.php') ?>" method="POST" class="p-6 sm:p-8">
            <input type="hidden" name="action" value="update">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                <!-- Info Terkunci -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">NPM (Read-Only)</label>
                    <input type="text" disabled value="<?= htmlspecialchars($mhs['npm'], ENT_QUOTES, 'UTF-8') ?>" class="w-full px-4 py-2 bg-slate-100 border border-slate-200 rounded-lg text-sm text-slate-500 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap (Read-Only)</label>
                    <input type="text" disabled value="<?= htmlspecialchars($mhs['nama_mahasiswa'], ENT_QUOTES, 'UTF-8') ?>" class="w-full px-4 py-2 bg-slate-100 border border-slate-200 rounded-lg text-sm text-slate-500 cursor-not-allowed">
                </div>

                <div class="sm:col-span-2 border-t border-slate-100 my-2"></div>

                <!-- Data Bisa Diubah -->
                <div>
                    <label for="tempat_lahir" class="block text-sm font-semibold text-slate-700 mb-1.5">Tempat Lahir</label>
                    <input type="text" id="tempat_lahir" name="tempat_lahir" maxlength="100"
                           value="<?= htmlspecialchars($mhs['tempat_lahir'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                </div>
                <div>
                    <label for="tanggal_lahir" class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal Lahir</label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir"
                           value="<?= htmlspecialchars($mhs['tanggal_lahir'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
                    <input type="email" id="email" name="email" maxlength="150"
                           value="<?= htmlspecialchars($mhs['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                </div>
                <div>
                    <label for="no_hp" class="block text-sm font-semibold text-slate-700 mb-1.5">No Handphone (WhatsApp)</label>
                    <input type="text" id="no_hp" name="no_hp" maxlength="30" placeholder="08..."
                           value="<?= htmlspecialchars($mhs['no_hp'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                </div>

                <div class="sm:col-span-2">
                    <label for="alamat" class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat Tempat Tinggal</label>
                    <textarea id="alamat" name="alamat" rows="3"
                              class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors"><?= htmlspecialchars($mhs['alamat'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
            </div>

            <div class="mt-4 pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-academic-600 rounded-md hover:bg-academic-700 transition-colors focus:ring-4 focus:ring-academic-100">
                    <i class="fa-solid fa-save mr-1.5"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</section>

<?php require_once dirname(__DIR__, 2) . '/layouts/admin/footer.php'; ?>

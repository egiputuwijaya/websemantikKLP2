<?php
/**
 * admin/universitas/index.php — Tampilan profil universitas
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
    error_log($e->getMessage());
    $univ = null;
}

$pageTitle  = 'Profil Universitas';
$activeMenu = 'universitas';
?>

<?php require_once dirname(__DIR__, 2) . '/layouts/admin/header.php'; ?>

<section class="bg-white rounded-md shadow-sm border border-slate-200 p-6 mb-8">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
        <h3 class="font-heading font-bold text-xl text-slate-900 flex items-center gap-2">
            <span class="w-10 h-10 rounded-lg bg-elegant-100 text-elegant-700 flex items-center justify-center">
                <i class="fa-solid fa-building text-lg"></i>
            </span>
            Profil Institusi
        </h3>
        <a href="<?= url('admin/universitas/edit.php') ?>"
           class="inline-flex items-center gap-2 px-4 py-2 bg-academic-600 text-white text-sm font-semibold rounded-md hover:bg-academic-700 focus:ring-4 focus:ring-academic-100 transition-all">
            <i class="fa-solid fa-pen-to-square"></i> Edit Data
        </a>
    </div>

    <?php if ($univ): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Info Utama -->
            <div>
                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-1">Kode / Singkatan</p>
                <p class="font-semibold text-slate-900 mb-4"><?= htmlspecialchars($univ['kode_universitas'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                
                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-1">Nama Universitas</p>
                <p class="font-bold text-lg text-slate-900 mb-4"><?= htmlspecialchars($univ['nama_universitas'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>

                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-1">Slogan / Motto</p>
                <p class="text-slate-700 italic mb-4"><?= htmlspecialchars($univ['slogan'] ?? 'Belum ada slogan', ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <!-- Info Kontak & Lokasi -->
            <div>
                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-1">Alamat Lengkap</p>
                <p class="text-slate-700 mb-1"><?= htmlspecialchars($univ['alamat'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                <p class="text-slate-700 mb-4">
                    <?= htmlspecialchars($univ['kota'] ?? '-', ENT_QUOTES, 'UTF-8') ?>, 
                    <?= htmlspecialchars($univ['provinsi'] ?? '-', ENT_QUOTES, 'UTF-8') ?> 
                    <?= htmlspecialchars($univ['kode_pos'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-1">Telepon</p>
                        <p class="font-semibold text-slate-900"><?= htmlspecialchars($univ['telepon'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-1">Email</p>
                        <p class="font-semibold text-slate-900"><?= htmlspecialchars($univ['email'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-1">Website</p>
                        <p class="font-semibold text-elegant-600">
                            <?php if (!empty($univ['website'])): ?>
                                <a href="<?= htmlspecialchars($univ['website'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="hover:underline">
                                    <?= htmlspecialchars($univ['website'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-8 pt-4 border-t border-slate-100 text-xs text-slate-400">
            Terakhir diubah: <?= htmlspecialchars($univ['updated_at'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php else: ?>
        <div class="p-6 text-center text-slate-500">
            Data Universitas belum tersedia di database.
        </div>
    <?php endif; ?>

</section>

<?php require_once dirname(__DIR__, 2) . '/layouts/admin/footer.php'; ?>

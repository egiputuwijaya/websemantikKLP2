<?php
/**
 * admin/pengguna/create.php — Form tambah pengguna
 */
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';

require_role([1]);

try {
    $stmtRoles = $pdo->query("SELECT * FROM roles ORDER BY id_role ASC");
    $listRoles = $stmtRoles->fetchAll();

    $stmtUniv = $pdo->query("SELECT * FROM universitas LIMIT 1");
    $univ = $stmtUniv->fetch();

    $stmtFak = $pdo->query("SELECT * FROM fakultas ORDER BY nama_fakultas ASC");
    $listFakultas = $stmtFak->fetchAll();

    $stmtProdi = $pdo->query("SELECT * FROM program_studi ORDER BY nama_program_studi ASC");
    $listProdi = $stmtProdi->fetchAll();
} catch (PDOException $e) {
    $listRoles = $listFakultas = $listProdi = [];
    $univ = null;
}

$pageTitle  = 'Tambah Pengguna';
$activeMenu = 'pengguna';
?>
<?php require_once dirname(__DIR__, 2) . '/layouts/admin/header.php'; ?>

<section class="max-w-4xl mb-8">
    <div class="mb-6 flex items-center gap-3">
        <a href="<?= url('admin/pengguna/index.php') ?>" class="w-10 h-10 rounded-md bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="font-heading font-bold text-2xl text-slate-900">Tambah Pengguna</h2>
            <p class="text-sm text-slate-500 mt-0.5">Buat akun baru untuk staf atau mahasiswa.</p>
        </div>
    </div>

    <?= render_flash_alert() ?>

    <div class="bg-white rounded-md shadow-sm border border-slate-200">
        <form action="<?= url('admin/pengguna/process.php') ?>" method="POST" class="p-6 sm:p-8">
            <input type="hidden" name="action" value="create">
            
            <?php if($univ): ?>
                <input type="hidden" name="id_universitas" value="<?= $univ['id_universitas'] ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Data Akun -->
                <div class="md:col-span-2">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4 border-b border-slate-100 pb-2">Informasi Akun</h3>
                </div>

                <div>
                    <label for="username" class="block text-sm font-semibold text-slate-700 mb-1.5">Username <span class="text-red-500">*</span></label>
                    <input type="text" id="username" name="username" required maxlength="50"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Password <span class="text-red-500">*</span></label>
                    <input type="password" id="password" name="password" required minlength="6"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                </div>

                <!-- Profil -->
                <div class="md:col-span-2 mt-4">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4 border-b border-slate-100 pb-2">Profil Pengguna</h3>
                </div>

                <div>
                    <label for="nama_lengkap" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" required maxlength="200"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
                    <input type="email" id="email" name="email" maxlength="150"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                </div>

                <!-- Hak Akses -->
                <div class="md:col-span-2 mt-4">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4 border-b border-slate-100 pb-2">Hak Akses & Penempatan</h3>
                </div>

                <div>
                    <label for="id_role" class="block text-sm font-semibold text-slate-700 mb-1.5">Role / Tingkat Akses <span class="text-red-500">*</span></label>
                    <select name="id_role" id="id_role" required onchange="togglePenempatan()" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                        <option value="">-- Pilih Role --</option>
                        <?php foreach ($listRoles as $r): ?>
                            <option value="<?= $r['id_role'] ?>" data-kode="<?= $r['kode_role'] ?>">
                                <?= htmlspecialchars($r['nama_role'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="status_aktif" class="block text-sm font-semibold text-slate-700 mb-1.5">Status <span class="text-red-500">*</span></label>
                    <select name="status_aktif" id="status_aktif" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                        <option value="Aktif">Aktif</option>
                        <option value="Tidak Aktif">Tidak Aktif</option>
                    </select>
                </div>

                <!-- Penempatan Fakultatif -->
                <div id="wrap_fakultas" class="hidden">
                    <label for="id_fakultas" class="block text-sm font-semibold text-slate-700 mb-1.5">Fakultas</label>
                    <select name="id_fakultas" id="id_fakultas" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                        <option value="">-- Kosong / Seluruh Fakultas --</option>
                        <?php foreach ($listFakultas as $fak): ?>
                            <option value="<?= $fak['id_fakultas'] ?>">
                                <?= htmlspecialchars($fak['nama_fakultas'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="wrap_prodi" class="hidden">
                    <label for="id_program_studi" class="block text-sm font-semibold text-slate-700 mb-1.5">Program Studi</label>
                    <select name="id_program_studi" id="id_program_studi" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                        <option value="">-- Kosong / Seluruh Prodi --</option>
                        <?php foreach ($listProdi as $p): ?>
                            <option value="<?= $p['id_program_studi'] ?>" data-fak="<?= $p['id_fakultas'] ?>">
                                <?= htmlspecialchars($p['nama_program_studi'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mt-8 pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-academic-600 rounded-md hover:bg-academic-700 transition-colors focus:ring-4 focus:ring-academic-100">
                    <i class="fa-solid fa-save mr-1.5"></i> Simpan Data
                </button>
            </div>
        </form>
    </div>
</section>

<script>
function togglePenempatan() {
    const selRole = document.getElementById('id_role');
    const roleOption = selRole.options[selRole.selectedIndex];
    const kodeRole = roleOption ? roleOption.getAttribute('data-kode') : '';

    const wrapFak = document.getElementById('wrap_fakultas');
    const wrapProdi = document.getElementById('wrap_prodi');

    wrapFak.classList.add('hidden');
    wrapProdi.classList.add('hidden');

    if (kodeRole === 'DEKANAT') {
        wrapFak.classList.remove('hidden');
    } else if (kodeRole === 'OPERATOR_PRODI' || kodeRole === 'MAHASISWA') {
        wrapFak.classList.remove('hidden');
        wrapProdi.classList.remove('hidden');
    }
}
</script>

<?php require_once dirname(__DIR__, 2) . '/layouts/admin/footer.php'; ?>

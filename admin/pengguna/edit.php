<?php
/**
 * admin/pengguna/edit.php — Form edit pengguna
 */
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';

require_role([1]);

$id = (int)($_GET['id'] ?? 0);

try {
    $stmtUser = $pdo->prepare("SELECT * FROM pengguna WHERE id_pengguna = :id");
    $stmtUser->execute([':id' => $id]);
    $pengguna = $stmtUser->fetch();

    $stmtRoles = $pdo->query("SELECT * FROM roles ORDER BY id_role ASC");
    $listRoles = $stmtRoles->fetchAll();

    $stmtFak = $pdo->query("SELECT * FROM fakultas ORDER BY nama_fakultas ASC");
    $listFakultas = $stmtFak->fetchAll();

    $stmtProdi = $pdo->query("SELECT * FROM program_studi ORDER BY nama_program_studi ASC");
    $listProdi = $stmtProdi->fetchAll();
} catch (PDOException $e) {
    $pengguna = null;
    $listRoles = $listFakultas = $listProdi = [];
}

if (!$pengguna) {
    set_flash_message('danger', 'Data Pengguna tidak ditemukan.');
    redirect(url('admin/pengguna/index.php'));
    exit;
}

$pageTitle  = 'Edit Pengguna';
$activeMenu = 'pengguna';
?>
<?php require_once dirname(__DIR__, 2) . '/layouts/admin/header.php'; ?>

<section class="max-w-4xl mb-8">
    <div class="mb-6 flex items-center gap-3">
        <a href="<?= url('admin/pengguna/index.php') ?>" class="w-10 h-10 rounded-md bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="font-heading font-bold text-2xl text-slate-900">Edit Pengguna</h2>
            <p class="text-sm text-slate-500 mt-0.5">Perbarui profil dan akses pengguna.</p>
        </div>
    </div>

    <?= render_flash_alert() ?>

    <div class="bg-white rounded-md shadow-sm border border-slate-200">
        <form action="<?= url('admin/pengguna/process.php') ?>" method="POST" class="p-6 sm:p-8">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id_pengguna" value="<?= $id ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Data Akun -->
                <div class="md:col-span-2">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4 border-b border-slate-100 pb-2">Informasi Akun</h3>
                </div>

                <div>
                    <label for="username" class="block text-sm font-semibold text-slate-700 mb-1.5">Username <span class="text-red-500">*</span></label>
                    <input type="text" id="username" name="username" required maxlength="50"
                           value="<?= htmlspecialchars($pengguna['username'], ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Password <span class="font-normal text-slate-400 text-xs ml-1">(Kosongkan jika tidak diubah)</span></label>
                    <input type="password" id="password" name="password" minlength="6" placeholder="******"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                </div>

                <!-- Profil -->
                <div class="md:col-span-2 mt-4">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4 border-b border-slate-100 pb-2">Profil Pengguna</h3>
                </div>

                <div>
                    <label for="nama_lengkap" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" required maxlength="200"
                           value="<?= htmlspecialchars($pengguna['nama_lengkap'], ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
                    <input type="email" id="email" name="email" maxlength="150"
                           value="<?= htmlspecialchars($pengguna['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
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
                            <option value="<?= $r['id_role'] ?>" data-kode="<?= $r['kode_role'] ?>" <?= $pengguna['id_role'] == $r['id_role'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($r['nama_role'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="status_aktif" class="block text-sm font-semibold text-slate-700 mb-1.5">Status <span class="text-red-500">*</span></label>
                    <select name="status_aktif" id="status_aktif" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                        <option value="Aktif" <?= $pengguna['status_aktif'] === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="Tidak Aktif" <?= $pengguna['status_aktif'] === 'Tidak Aktif' ? 'selected' : '' ?>>Tidak Aktif</option>
                    </select>
                </div>

                <!-- Penempatan Fakultatif -->
                <div id="wrap_fakultas" class="hidden">
                    <label for="id_fakultas" class="block text-sm font-semibold text-slate-700 mb-1.5">Fakultas</label>
                    <select name="id_fakultas" id="id_fakultas" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-md text-sm focus:bg-white focus:ring-2 focus:ring-academic-100 focus:border-academic-500 transition-colors">
                        <option value="">-- Kosong / Seluruh Fakultas --</option>
                        <?php foreach ($listFakultas as $fak): ?>
                            <option value="<?= $fak['id_fakultas'] ?>" <?= $pengguna['id_fakultas'] == $fak['id_fakultas'] ? 'selected' : '' ?>>
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
                            <option value="<?= $p['id_program_studi'] ?>" data-fak="<?= $p['id_fakultas'] ?>" <?= $pengguna['id_program_studi'] == $p['id_program_studi'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nama_program_studi'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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
// Run on load
document.addEventListener('DOMContentLoaded', togglePenempatan);
</script>

<?php require_once dirname(__DIR__, 2) . '/layouts/admin/footer.php'; ?>

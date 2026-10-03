<?php
/**
 * admin/profil-saya/index.php — Profil Akademik Mahasiswa
 */
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';

// Khusus Mahasiswa
require_role([5]);

$userActive = get_user_login();
$idPengguna = (int)$userActive['id_pengguna'];

try {
    $sql = "SELECT m.*, p.email, p.no_hp, p.username,
                   pr.nama_program_studi, pr.jenjang, pr.kode_program_studi,
                   f.nama_fakultas, u.nama_universitas 
            FROM mahasiswa m 
            JOIN pengguna p ON m.id_pengguna = p.id_pengguna 
            JOIN program_studi pr ON m.id_program_studi = pr.id_program_studi 
            JOIN fakultas f ON pr.id_fakultas = f.id_fakultas 
            JOIN universitas u ON f.id_universitas = u.id_universitas 
            WHERE m.id_pengguna = :id_pengguna";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_pengguna' => $idPengguna]);
    $mahasiswa = $stmt->fetch();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $mahasiswa = null;
}

if (!$mahasiswa) {
    set_flash_message('warning', 'Data akademik Anda belum lengkap. Silakan hubungi Operator Prodi.');
}

$pageTitle  = 'KTM / Profil Mahasiswa';
$activeMenu = 'profil_saya';
?>
<?php require_once dirname(__DIR__, 2) . '/layouts/admin/header.php'; ?>

<section class="mb-8 max-w-4xl">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="font-heading font-bold text-2xl text-slate-900">Kartu Tanda Mahasiswa (KTM)</h2>
            <p class="text-sm text-slate-500 mt-1">Informasi Data Pribadi dan Akademik Mahasiswa.</p>
        </div>
        <?php if ($mahasiswa): ?>
            <a href="<?= url('admin/profil-saya/edit.php') ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-academic-600 text-white text-sm font-semibold rounded-md hover:bg-academic-700 transition-colors focus:ring-4 focus:ring-academic-100">
                <i class="fa-solid fa-pen-to-square"></i> Perbarui Data
            </a>
        <?php endif; ?>
    </div>

    <?= render_flash_alert() ?>

    <?php if ($mahasiswa): ?>
    <div class="bg-white rounded-md shadow-card border border-slate-100 overflow-hidden relative">
        <!-- Dekorasi Background -->
        <div class="h-32 bg-gradient-to-r from-academic-700 to-elegant-700 relative">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 20px 20px;"></div>
        </div>
        
        <div class="px-6 sm:px-10 pb-10">
            <!-- Avatar / Foto -->
            <div class="relative -mt-16 mb-6 flex justify-between items-end">
                <div class="w-32 h-32 rounded-md bg-white p-1.5 shadow-md">
                    <div class="w-full h-full rounded-md bg-slate-100 flex items-center justify-center text-4xl text-slate-300">
                        <i class="fa-solid fa-user"></i>
                    </div>
                </div>
                <!-- Status Badge -->
                <?php 
                    $status = $mahasiswa['status_mahasiswa'];
                    $sBg = 'bg-slate-100 text-slate-600';
                    if ($status === 'Aktif') $sBg = 'bg-green-100 text-green-700 border-green-200';
                    elseif ($status === 'Lulus') $sBg = 'bg-blue-100 text-blue-700 border-blue-200';
                    elseif ($status === 'Cuti') $sBg = 'bg-amber-100 text-amber-700 border-amber-200';
                ?>
                <div class="px-4 py-1.5 rounded-lg border font-bold text-sm <?= $sBg ?>">
                    Status: <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>

            <!-- Nama & NPM -->
            <div class="mb-8">
                <h3 class="font-heading font-bold text-3xl text-slate-900"><?= htmlspecialchars($mahasiswa['nama_mahasiswa'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="text-academic-600 font-mono font-bold mt-1 text-lg">NPM. <?= htmlspecialchars($mahasiswa['npm'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                <!-- Kolom Kiri: Akademik -->
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Informasi Akademik</h4>
                    
                    <dl class="space-y-3 text-sm">
                        <div class="grid grid-cols-3 gap-2">
                            <dt class="text-slate-500">Universitas</dt>
                            <dd class="col-span-2 font-semibold text-slate-900"><?= htmlspecialchars($mahasiswa['nama_universitas'], ENT_QUOTES, 'UTF-8') ?></dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <dt class="text-slate-500">Fakultas</dt>
                            <dd class="col-span-2 font-semibold text-slate-900"><?= htmlspecialchars($mahasiswa['nama_fakultas'], ENT_QUOTES, 'UTF-8') ?></dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <dt class="text-slate-500">Program Studi</dt>
                            <dd class="col-span-2 font-semibold text-slate-900"><?= htmlspecialchars($mahasiswa['nama_program_studi'], ENT_QUOTES, 'UTF-8') ?> (<?= $mahasiswa['jenjang'] ?>)</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <dt class="text-slate-500">Tgl. Masuk</dt>
                            <dd class="col-span-2 font-semibold text-slate-900"><?= date('d F Y', strtotime($mahasiswa['tanggal_masuk'])) ?></dd>
                        </div>
                    </dl>
                </div>

                <!-- Kolom Kanan: Pribadi -->
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Data Pribadi & Kontak</h4>
                    
                    <dl class="space-y-3 text-sm">
                        <div class="grid grid-cols-3 gap-2">
                            <dt class="text-slate-500">TTL</dt>
                            <dd class="col-span-2 font-semibold text-slate-900">
                                <?= htmlspecialchars($mahasiswa['tempat_lahir'] ?? '-', ENT_QUOTES, 'UTF-8') ?>, 
                                <?= $mahasiswa['tanggal_lahir'] ? date('d M Y', strtotime($mahasiswa['tanggal_lahir'])) : '-' ?>
                            </dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <dt class="text-slate-500">Gender</dt>
                            <dd class="col-span-2 font-semibold text-slate-900">
                                <?= $mahasiswa['jenis_kelamin'] === 'L' ? 'Laki-Laki' : ($mahasiswa['jenis_kelamin'] === 'P' ? 'Perempuan' : '-') ?>
                            </dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <dt class="text-slate-500">Email</dt>
                            <dd class="col-span-2 font-semibold text-slate-900"><?= htmlspecialchars($mahasiswa['email'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <dt class="text-slate-500">No. HP</dt>
                            <dd class="col-span-2 font-semibold text-slate-900"><?= htmlspecialchars($mahasiswa['no_hp'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
                        </div>
                        <div class="grid grid-cols-3 gap-2 pt-2">
                            <dt class="text-slate-500">Alamat</dt>
                            <dd class="col-span-2 text-slate-700 leading-relaxed"><?= nl2br(htmlspecialchars($mahasiswa['alamat'] ?? '-', ENT_QUOTES, 'UTF-8')) ?></dd>
                        </div>
                    </dl>
                </div>
            </div>
            
        </div>
    </div>
    <?php else: ?>
        <!-- Empty State jika data mahasiswa tidak ada -->
        <div class="bg-amber-50 border border-amber-200 rounded-md p-8 text-center max-w-lg mx-auto mt-10">
            <div class="w-16 h-16 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 class="text-lg font-bold text-amber-800 mb-2">Profil Akademik Belum Tertaut</h3>
            <p class="text-amber-700 text-sm">Akun pengguna Anda belum dihubungkan dengan data Mahasiswa Induk. Silakan minta Operator Program Studi Anda untuk melengkapi data mahasiswa Anda.</p>
        </div>
    <?php endif; ?>
</section>

<?php require_once dirname(__DIR__, 2) . '/layouts/admin/footer.php'; ?>

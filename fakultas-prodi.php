<?php
/**
 * ============================================================
 *  fakultas-prodi.php — Halaman Daftar Fakultas & Program Studi
 * ============================================================
 *  Menampilkan seluruh fakultas beserta program studi
 *  di bawahnya (JOIN + grouping) dalam bentuk card/accordion.
 *
 *  Halaman publik — dapat diakses tanpa login.
 */

// ---------- Konfigurasi & Koneksi ----------
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

// ---------- Data Universitas (untuk footer) ----------
$univData = [];
try {
    $stmtUniv = $pdo->query(
        "SELECT nama_universitas, slogan, alamat, kota, provinsi, website, email, telepon
         FROM universitas
         ORDER BY id_universitas ASC
         LIMIT 1"
    );
    $univData = $stmtUniv->fetch() ?: [];
} catch (PDOException $e) {
    $univData = [];
}

// ---------- Ambil seluruh Fakultas + Program Studi (JOIN) ----------
$fakultasList = [];
try {
    // Query utama: data fakultas
    $stmtFakultas = $pdo->query(
        "SELECT f.id_fakultas, f.kode_fakultas, f.nama_fakultas, u.nama_universitas
         FROM fakultas f
         LEFT JOIN universitas u ON u.id_universitas = f.id_universitas
         ORDER BY f.nama_fakultas ASC"
    );
    $fakultasRows = $stmtFakultas->fetchAll();

    // Ambil semua program studi dalam satu query, lalu grouping di PHP
    $stmtProdi = $pdo->query(
        "SELECT ps.id_program_studi,
                ps.kode_program_studi,
                ps.nama_program_studi,
                ps.jenjang,
                ps.id_fakultas
         FROM program_studi ps
         LEFT JOIN fakultas f ON f.id_fakultas = ps.id_fakultas
         ORDER BY f.nama_fakultas ASC, ps.nama_program_studi ASC"
    );
    $prodiRows = $stmtProdi->fetchAll();

    // Grouping prodi per fakultas
    $prodiPerFakultas = [];
    foreach ($prodiRows as $prodi) {
        $prodiPerFakultas[(int) $prodi['id_fakultas']][] = $prodi;
    }

    // Susun data akhir
    foreach ($fakultasRows as $fakultas) {
        $idFak = (int) $fakultas['id_fakultas'];
        $fakultas['program_studi'] = $prodiPerFakultas[$idFak] ?? [];
        $fakultasList[] = $fakultas;
    }
} catch (PDOException $e) {
    $fakultasList = [];
}

// ---------- Variabel halaman ----------
$pageTitle = 'Fakultas & Program Studi — SIM Mahasiswa UMB';
$pageDesc  = 'Daftar fakultas dan program studi Universitas Muhammadiyah Bengkulu lengkap dengan jenjang pendidikan (S1, D3, dll.).';
$activePage = 'fakultas';

// ---------- Statistik ringkas untuk hero halaman ----------
$totalFakultas = count($fakultasList);
$totalProdi = 0;
foreach ($fakultasList as $fak) {
    $totalProdi += count($fak['program_studi']);
}
?>

<!-- Layout: Header (buka HTML) → Navbar → Konten → Footer -->
<?php require_once __DIR__ . '/layouts/public/header.php'; ?>
<?php require_once __DIR__ . '/layouts/public/navbar.php'; ?>

<!-- ============================================================
     HERO HEADER HALAMAN
     ============================================================ -->
<section class="relative overflow-hidden bg-gradient-to-br from-academic-800 via-academic-700 to-elegant-800">
    <div class="absolute -top-20 -right-20 w-96 h-96 rounded-full bg-white/10 blur-3xl"></div>
    <div class="absolute bottom-0 -left-24 w-80 h-80 rounded-full bg-elegant-500/20 blur-3xl"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20">
        <!-- Breadcrumb -->
        <nav aria-label="Breadcrumb" class="mb-6">
            <ol class="flex items-center gap-2 text-xs md:text-sm text-slate-300 flex-wrap">
                <li>
                    <a href="<?= url('index.php') ?>" class="inline-flex items-center gap-1.5 hover:text-white transition-colors">
                        <i class="fa-solid fa-house"></i>
                        Home
                    </a>
                </li>
                <li aria-hidden="true"><i class="fa-solid fa-chevron-right text-[10px] text-slate-500"></i></li>
                <li class="text-white font-semibold">Fakultas &amp; Program Studi</li>
            </ol>
        </nav>

        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-8">
            <div class="max-w-2xl">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full glass text-elegant-300 text-xs font-semibold mb-4">
                    <i class="fa-solid fa-building-columns"></i>
                    Struktur Akademik
                </span>
                <h1 class="font-heading font-extrabold text-white text-3xl sm:text-4xl leading-tight mb-4">
                    Daftar Fakultas &amp; Program Studi
                </h1>
                <p class="text-slate-200/90 text-sm md:text-base leading-relaxed">
                    Jelajahi seluruh fakultas beserta program studi yang tersedia di
                    Universitas Muhammadiyah Bengkulu, lengkap dengan jenjang pendidiannya.
                </p>
            </div>

            <!-- Ringkasan angka -->
            <div class="flex gap-4 shrink-0">
                <div class="glass rounded-2xl px-6 py-5 text-center min-w-[7rem]">
                    <p class="font-heading font-extrabold text-3xl text-white"><?= number_format($totalFakultas) ?></p>
                    <p class="text-[11px] text-slate-300 uppercase tracking-wider mt-1">Fakultas</p>
                </div>
                <div class="glass rounded-2xl px-6 py-5 text-center min-w-[7rem]">
                    <p class="font-heading font-extrabold text-3xl text-white"><?= number_format($totalProdi) ?></p>
                    <p class="text-[11px] text-slate-300 uppercase tracking-wider mt-1">Program Studi</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     DAFTAR FAKULTAS + PRODI (CARD / ACCORDION)
     ============================================================ -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">

    <?php if (empty($fakultasList)): ?>

        <!-- State kosong -->
        <div class="bg-white rounded-2xl shadow-card border border-slate-100 p-12 text-center">
            <div class="w-20 h-20 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-6">
                <i class="fa-solid fa-inbox text-3xl"></i>
            </div>
            <h2 class="font-heading font-bold text-xl text-slate-900 mb-2">Belum Ada Data Fakultas</h2>
            <p class="text-slate-500 text-sm max-w-md mx-auto">
                Data fakultas dan program studi belum tersedia di basis data.
                Silakan hubungi administrator sistem.
            </p>
        </div>

    <?php else: ?>

        <div class="space-y-8 md:space-y-10">
            <?php foreach ($fakultasList as $index => $fakultas):
                $namaFakultas = $fakultas['nama_fakultas'] ?? '-';
                $kodeFakultas = $fakultas['kode_fakultas'] ?? '-';
                $prodiList    = $fakultas['program_studi'] ?? [];
                $jumlahProdi  = count($prodiList);
                $isOpen       = ($index === 0); // Fakultas pertama terbuka default
                $collapseId   = 'prodi-fakultas-' . (int) ($fakultas['id_fakultas'] ?? $index);
            ?>

                <!-- ===== CARD FAKULTAS ===== -->
                <article class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden">

                    <!-- Header Fakultas -->
                    <header class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4 p-5 md:p-6">
                            <!-- Ikon -->
                            <div class="flex items-center gap-4 flex-1 min-w-0">
                                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-academic-700 to-academic-500 text-white flex items-center justify-center shrink-0 shadow-soft">
                                    <i class="fa-solid fa-building-columns text-xl"></i>
                                </div>
                                <div class="min-w-0">
                                    <span class="inline-block px-2.5 py-0.5 rounded-md bg-academic-100 text-academic-800 text-[11px] font-bold tracking-wide mb-1.5">
                                        <?= htmlspecialchars($kodeFakultas, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                    <h2 class="font-heading font-bold text-lg md:text-xl text-slate-900 leading-snug truncate">
                                        <?= htmlspecialchars($namaFakultas, ENT_QUOTES, 'UTF-8') ?>
                                    </h2>
                                </div>
                            </div>

                            <!-- Badge jumlah prodi + tombol accordion -->
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-elegant-50 text-elegant-800 text-xs font-bold border border-elegant-100">
                                    <i class="fa-solid fa-book"></i>
                                    <?= number_format($jumlahProdi) ?> Program Studi
                                </span>

                                <!-- Tombol toggle (mobile-friendly, tetap bisa dipakai di desktop) -->
                                <button type="button"
                                        class="accordion-toggle inline-flex items-center justify-center w-11 h-11 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-academic-700 focus:outline-none focus:ring-2 focus:ring-academic-500 transition-colors"
                                        data-target="<?= htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8') ?>"
                                        aria-expanded="<?= $isOpen ? 'true' : 'false' ?>"
                                        aria-controls="<?= htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="fa-solid fa-chevron-down transition-transform duration-300 <?= $isOpen ? 'rotate-180' : '' ?>"></i>
                                </button>
                            </div>
                        </div>
                    </header>

                    <!-- Body: Daftar Program Studi -->
                    <div id="<?= htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8') ?>"
                         class="accordion-body <?= $isOpen ? '' : 'hidden' ?>">
                        <div class="p-5 md:p-6">

                            <?php if ($jumlahProdi === 0): ?>

                                <div class="flex items-center gap-3 p-4 rounded-xl bg-slate-50 border border-slate-100">
                                    <i class="fa-solid fa-circle-info text-slate-400"></i>
                                    <p class="text-sm text-slate-500">
                                        Belum ada program studi yang terdaftar untuk fakultas ini.
                                    </p>
                                </div>

                            <?php else: ?>

                                <!-- Grid program studi -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <?php foreach ($prodiList as $prodi):
                                        $namaProdi   = $prodi['nama_program_studi'] ?? '-';
                                        $kodeProdi   = $prodi['kode_program_studi'] ?? '-';
                                        $jenjang     = $prodi['jenjang'] ?? '-';
                                        // Warna badge jenjang
                                        $jenjangClass = match (strtoupper($jenjang)) {
                                            'S1'    => 'bg-academic-100 text-academic-800 border-academic-200',
                                            'S2'    => 'bg-purple-100 text-purple-800 border-purple-200',
                                            'S3'    => 'bg-amber-100 text-amber-800 border-amber-200',
                                            'D3'    => 'bg-elegant-100 text-elegant-800 border-elegant-200',
                                            'D4'    => 'bg-orange-100 text-orange-800 border-orange-200',
                                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                                        };
                                    ?>
                                        <div class="group relative p-5 rounded-xl border border-slate-200 bg-white hover:border-academic-300 hover:shadow-soft transition-all card-hover">
                                            <!-- Aksen pojok -->
                                            <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-bl from-academic-50 to-transparent rounded-bl-3xl opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>

                                            <div class="relative">
                                                <div class="flex items-start justify-between gap-3 mb-3">
                                                    <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[11px] font-bold tracking-wide">
                                                        <?= htmlspecialchars($kodeProdi, ENT_QUOTES, 'UTF-8') ?>
                                                    </span>
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border text-[11px] font-bold <?= htmlspecialchars($jenjangClass, ENT_QUOTES, 'UTF-8') ?>">
                                                        <i class="fa-solid fa-award"></i>
                                                        <?= htmlspecialchars($jenjang, ENT_QUOTES, 'UTF-8') ?>
                                                    </span>
                                                </div>

                                                <h3 class="font-heading font-bold text-slate-900 text-sm leading-snug mb-2">
                                                    <?= htmlspecialchars($namaProdi, ENT_QUOTES, 'UTF-8') ?>
                                                </h3>

                                                <div class="flex items-center gap-2 pt-3 border-t border-slate-100">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-elegant-500"></span>
                                                    <p class="text-[11px] text-slate-500 truncate">
                                                        <?= htmlspecialchars($namaFakultas, ENT_QUOTES, 'UTF-8') ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                            <?php endif; ?>

                        </div>
                    </div>

                </article>

            <?php endforeach; ?>
        </div>

    <?php endif; ?>

    <!-- Catatan / CTA bawah -->
    <div class="mt-12 md:mt-16 rounded-2xl bg-gradient-to-r from-slate-900 to-slate-800 p-6 md:p-8 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-start gap-4 text-center md:text-left">
            <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-circle-question text-elegant-300 text-lg"></i>
            </div>
            <div>
                <h3 class="font-heading font-bold text-white text-base md:text-lg mb-1">Butuh Informasi Lebih Lanjut?</h3>
                <p class="text-slate-300 text-sm leading-relaxed max-w-xl">
                    Masuk ke portal untuk mengelola data akademik Anda, atau hubungi
                    bagian akademik fakultas terkait.
                </p>
            </div>
        </div>
        <a href="<?= url('auth/login.php') ?>"
           class="shrink-0 inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-white text-slate-900 font-bold text-sm shadow-soft hover:shadow-lg hover:-translate-y-0.5 transition-all">
            <i class="fa-solid fa-right-to-bracket text-academic-700"></i>
            Login Sistem
        </a>
    </div>

</section>

<script>
/**
 * Accordion sederhana tanpa library eksternal.
 * Klik header / tombol chevron → toggle body prodi di bawahnya.
 */
(function () {
    var toggles = document.querySelectorAll('.accordion-toggle');

    toggles.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = btn.getAttribute('data-target');
            var body = document.getElementById(targetId);
            if (!body) return;

            var isOpen = !body.classList.contains('hidden');
            var icon = btn.querySelector('i');

            if (isOpen) {
                body.classList.add('hidden');
                btn.setAttribute('aria-expanded', 'false');
                if (icon) icon.classList.remove('rotate-180');
            } else {
                body.classList.remove('hidden');
                btn.setAttribute('aria-expanded', 'true');
                if (icon) icon.classList.add('rotate-180');
            }
        });
    });
})();
</script>

<?php
// ---------- Footer ----------
require_once __DIR__ . '/layouts/public/footer.php';
?>

<?php
/**
 * ============================================================
 *  index.php — Landing Page Utama (Portal Informasi Kampus)
 * ============================================================
 *  Sistem Informasi Manajemen (SIM) Mahasiswa
 *  Universitas Muhammadiyah Bengkulu (UMB)
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

// ---------- Statistik Dinamis ----------
// Jumlah Fakultas
$totalFakultas = 0;
try {
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM fakultas");
    $totalFakultas = (int) $stmt->fetchColumn();
} catch (PDOException $e) {
    $totalFakultas = 0;
}

// Jumlah Program Studi
$totalProdi = 0;
try {
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM program_studi");
    $totalProdi = (int) $stmt->fetchColumn();
} catch (PDOException $e) {
    $totalProdi = 0;
}

// Total Mahasiswa Aktif
$totalMahasiswaAktif = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM mahasiswa WHERE status_mahasiswa = 'Aktif'");
    $stmt->execute();
    $totalMahasiswaAktif = (int) $stmt->fetchColumn();
} catch (PDOException $e) {
    $totalMahasiswaAktif = 0;
}

// ---------- Variabel halaman ----------
$pageTitle = 'SIM Mahasiswa UMB — Portal Informasi Kampus';
$pageDesc  = 'Sistem Informasi Manajemen Mahasiswa Universitas Muhammadiyah Bengkulu. Akses akademik cepat, data transparan, dan teraudit.';
$activePage = 'home';
?>

<!-- Layout: Header (buka HTML) → Navbar → Konten → Footer -->
<?php require_once __DIR__ . '/layouts/public/header.php'; ?>
<?php require_once __DIR__ . '/layouts/public/navbar.php'; ?>

<!-- ============================================================
     HERO SECTION
     ============================================================ -->
<section class="hero-slider relative overflow-hidden">
    <!-- Overlay Transparan Hitam -->
    <div class="absolute inset-0 hero-overlay pointer-events-none"></div>

    <!-- Dekorasi background -->
    <div class="absolute inset-0 opacity-20 pointer-events-none">
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute bottom-0 -left-32 w-[28rem] h-[28rem] rounded-full bg-elegant-400/20 blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-0 min-h-[85vh] flex items-center">
        <div class="w-full grid lg:grid-cols-2 gap-12 items-center">

            <!-- Teks Hero -->
            <div class="text-center lg:text-left">


                <h1 class="font-heading font-extrabold text-white text-3xl sm:text-4xl lg:text-5xl leading-tight mb-6">
                    Selamat Datang di<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-elegant-300 to-white">
                        SIM Mahasiswa UMB
                    </span>
                </h1>

                <p class="text-slate-200/90 text-base md:text-lg leading-relaxed mb-8 max-w-xl mx-auto lg:mx-0">
                    Portal informasi akademik terintegrasi untuk kelola data mahasiswa, program studi,
                    dan fakultas cepat, transparan, serta tercatat dalam sistem audit.
                </p>

                <!-- Tombol CTA -->
                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                    <a href="<?= url('fakultas-prodi.php') ?>"
                       class="group w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl bg-white text-academic-800 font-bold text-sm shadow-soft hover:shadow-lg hover:-translate-y-0.5 transition-all">
                        <i class="fa-solid fa-building-columns group-hover:scale-110 transition-transform"></i>
                        Lihat Program Studi
                    </a>
                    <a href="<?= url('auth/login.php') ?>"
                       class="group w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl border-2 border-white/40 text-white font-bold text-sm glass hover:bg-white/15 hover:-translate-y-0.5 transition-all">
                        <i class="fa-solid fa-right-to-bracket group-hover:scale-110 transition-transform"></i>
                        Login Portal
                    </a>
                </div>
            </div>

            <!-- Ilustrasi / Panel Statistik Ringkas -->
            <div class="relative">
                <div class="glass rounded-2xl p-6 md:p-8 shadow-soft">
                    <div class="flex items-center gap-3 mb-6 pb-6 border-b border-white/10">
                        <div class="w-12 h-12 rounded-xl bg-elegant-500/20 flex items-center justify-center">
                            <i class="fa-solid fa-chart-line text-elegant-300 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-white font-heading font-bold text-sm">Ringkasan Akademik</p>
                            <p class="text-slate-300 text-xs">Data real-time dari basis data SIM</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="font-heading font-extrabold text-2xl md:text-3xl text-white"><?= number_format($totalFakultas) ?></p>
                            <p class="text-[11px] md:text-xs text-slate-300 mt-1 uppercase tracking-wide">Fakultas</p>
                        </div>
                        <div class="border-x border-white/10">
                            <p class="font-heading font-extrabold text-2xl md:text-3xl text-white"><?= number_format($totalProdi) ?></p>
                            <p class="text-[11px] md:text-xs text-slate-300 mt-1 uppercase tracking-wide">Prodi</p>
                        </div>
                        <div>
                            <p class="font-heading font-extrabold text-2xl md:text-3xl text-white"><?= number_format($totalMahasiswaAktif) ?></p>
                            <p class="text-[11px] md:text-xs text-slate-300 mt-1 uppercase tracking-wide">Mhs Aktif</p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     COUNTER STAT CARD (Dinamis dari Query PDO)
     ============================================================ -->
<section class="relative -mt-10 z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6">

        <!-- Kartu: Jumlah Fakultas -->
        <div class="card-hover bg-white rounded-2xl shadow-card border border-slate-100 p-8 flex flex-col items-start justify-start text-left h-full">
            <div class="text-slate-800 mb-6">
                <i class="fa-solid fa-building-columns text-2xl"></i>
            </div>
            <h3 class="font-heading font-extrabold text-2xl text-slate-900 mb-4 leading-tight">
                <?= number_format($totalFakultas) ?> Fakultas
            </h3>
            <p class="text-sm text-slate-500 leading-relaxed">
                Tersedia berbagai pilihan fakultas unggulan dengan fasilitas lengkap yang siap mendukung seluruh aktivitas akademik Anda.
            </p>
        </div>

        <!-- Kartu: Jumlah Program Studi -->
        <div class="card-hover bg-white rounded-2xl shadow-card border border-slate-100 p-8 flex flex-col items-start justify-start text-left h-full">
            <div class="text-slate-800 mb-6">
                <i class="fa-solid fa-book-open text-2xl"></i>
            </div>
            <h3 class="font-heading font-extrabold text-2xl text-slate-900 mb-4 leading-tight">
                <?= number_format($totalProdi) ?> Program Studi
            </h3>
            <p class="text-sm text-slate-500 leading-relaxed">
                Pilihan program studi yang beragam dan terspesialisasi, dirancang khusus untuk menjawab tantangan dunia kerja masa depan.
            </p>
        </div>

        <!-- Kartu: Total Mahasiswa Aktif -->
        <div class="card-hover bg-white rounded-2xl shadow-card border border-slate-100 p-8 flex flex-col items-start justify-start text-left h-full">
            <div class="text-slate-800 mb-6">
                <i class="fa-solid fa-user-graduate text-2xl"></i>
            </div>
            <h3 class="font-heading font-extrabold text-2xl text-slate-900 mb-4 leading-tight">
                <?= number_format($totalMahasiswaAktif) ?> Mahasiswa
            </h3>
            <p class="text-sm text-slate-500 leading-relaxed">
                Ribuan mahasiswa aktif yang tergabung dalam lingkungan akademik dinamis, inovatif, dan berprestasi tinggi.
            </p>
        </div>

    </div>
</section>

<!-- ============================================================
     CARD FITUR UTAMA SIM
     ============================================================ -->
<section id="fitur" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
    <div class="text-center mb-12 md:mb-16">
      
        <h2 class="font-heading font-extrabold text-2xl sm:text-3xl lg:text-4xl text-slate-900 mb-4">
            Fitur Utama SIM Mahasiswa
        </h2>
        <p class="text-slate-500 max-w-2xl mx-auto text-sm md:text-base leading-relaxed">
            Dibangun dengan pendekatan akademik yang modern untuk mendukung proses administrasi
            dan informasi kampus secara terstruktur.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">

        <!-- Fitur 1: Akses Akademik -->
        <article class="card-hover group bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden p-8 flex flex-col h-full">
            <div class="text-slate-800 mb-6 group-hover:scale-110 group-hover:text-academic-600 transition-all origin-left">
                <i class="fa-solid fa-laptop-code text-3xl"></i>
            </div>
            <h3 class="font-heading font-extrabold text-xl text-slate-900 mb-3">Akses Akademik</h3>
            <p class="text-sm text-slate-500 leading-relaxed mb-6">
                Akses terpadu untuk informasi fakultas, program studi, dan data kependudukan mahasiswa secara responsif.
            </p>
            <ul class="space-y-3 mt-auto">
                <li class="flex items-start gap-3 text-sm text-slate-600">
                    <i class="fa-solid fa-check text-academic-500 mt-1"></i>
                    Informasi terstruktur
                </li>
                <li class="flex items-start gap-3 text-sm text-slate-600">
                    <i class="fa-solid fa-check text-academic-500 mt-1"></i>
                    Data terverifikasi
                </li>
                <li class="flex items-start gap-3 text-sm text-slate-600">
                    <i class="fa-solid fa-check text-academic-500 mt-1"></i>
                    Akses multi-peran
                </li>
            </ul>
        </article>

        <!-- Fitur 2: Transparansi Data -->
        <article class="card-hover group bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden p-8 flex flex-col h-full">
            <div class="text-slate-800 mb-6 group-hover:scale-110 group-hover:text-elegant-600 transition-all origin-left">
                <i class="fa-solid fa-database text-3xl"></i>
            </div>
            <h3 class="font-heading font-extrabold text-xl text-slate-900 mb-3">Transparansi Data</h3>
            <p class="text-sm text-slate-500 leading-relaxed mb-6">
                Seluruh data disajikan secara akurat dan konsisten berdasarkan basis data yang dapat dipertanggungjawabkan.
            </p>
            <ul class="space-y-3 mt-auto">
                <li class="flex items-start gap-3 text-sm text-slate-600">
                    <i class="fa-solid fa-check text-academic-500 mt-1"></i>
                    Data real-time
                </li>
                <li class="flex items-start gap-3 text-sm text-slate-600">
                    <i class="fa-solid fa-check text-academic-500 mt-1"></i>
                    Konsistensi antar-unit
                </li>
                <li class="flex items-start gap-3 text-sm text-slate-600">
                    <i class="fa-solid fa-check text-academic-500 mt-1"></i>
                    Tampilan mudah dipahami
                </li>
            </ul>
        </article>

        <!-- Fitur 3: Audit System -->
        <article class="card-hover group bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden p-8 flex flex-col h-full">
            <div class="text-slate-800 mb-6 group-hover:scale-110 group-hover:text-slate-900 transition-all origin-left">
                <i class="fa-solid fa-shield-halved text-3xl"></i>
            </div>
            <h3 class="font-heading font-extrabold text-xl text-slate-900 mb-3">Audit System</h3>
            <p class="text-sm text-slate-500 leading-relaxed mb-6">
                Setiap aktivitas login dan proses administrasi dicatat otomatis untuk menjamin akuntabilitas sistem.
            </p>
            <ul class="space-y-3 mt-auto">
                <li class="flex items-start gap-3 text-sm text-slate-600">
                    <i class="fa-solid fa-check text-academic-500 mt-1"></i>
                    Log otomatis
                </li>
                <li class="flex items-start gap-3 text-sm text-slate-600">
                    <i class="fa-solid fa-check text-academic-500 mt-1"></i>
                    Jejak pengguna tersimpan
                </li>
                <li class="flex items-start gap-3 text-sm text-slate-600">
                    <i class="fa-solid fa-check text-academic-500 mt-1"></i>
                    Monitoring administrator
                </li>
            </ul>
        </article>

    </div>
</section>

<!-- ============================================================
     SECTION CTA AKHIR
     ============================================================ -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16 md:pb-24">
    <div class="rounded-2xl bg-slate-50 border border-slate-200 px-6 sm:px-10 py-12 md:py-16 text-center">
        <h2 class="font-heading font-extrabold text-slate-900 text-2xl sm:text-3xl lg:text-4xl mb-4">
            Siap Mengakses Portal Akademik?
        </h2>
        <p class="text-slate-500 max-w-2xl mx-auto mb-8 text-sm md:text-base leading-relaxed">
            Masuk ke SIM Mahasiswa UMB untuk mengelola data akademik secara resmi,
            atau telusuri daftar fakultas dan program studi kami terlebih dahulu.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="<?= url('fakultas-prodi.php') ?>"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-lg bg-white border border-slate-300 text-slate-700 font-bold text-sm hover:bg-slate-100 hover:text-slate-900 transition-colors">
                <i class="fa-solid fa-building-columns"></i>
                Jelajahi Program Studi
            </a>
            <a href="<?= url('auth/login.php') ?>"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-lg bg-academic-600 text-white font-bold text-sm hover:bg-academic-700 transition-colors focus:ring-4 focus:ring-academic-100">
                <i class="fa-solid fa-right-to-bracket"></i>
                Login Sekarang
            </a>
        </div>
    </div>
</section>

<?php
// ---------- Footer ----------
require_once __DIR__ . '/layouts/public/footer.php';
?>

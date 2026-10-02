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
<section class="hero-gradient relative overflow-hidden">
    <!-- Dekorasi background -->
    <div class="absolute inset-0 opacity-20 pointer-events-none">
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute bottom-0 -left-32 w-[28rem] h-[28rem] rounded-full bg-elegant-400/20 blur-3xl"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24 lg:py-28">
        <div class="grid lg:grid-cols-2 gap-12 items-center">

            <!-- Teks Hero -->
            <div class="text-center lg:text-left">
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass text-elegant-300 text-xs md:text-sm font-semibold mb-6">
                    <i class="fa-solid fa-university"></i>
                    Universitas Muhammadiyah Bengkulu
                </span>

                <h1 class="font-heading font-extrabold text-white text-3xl sm:text-4xl lg:text-5xl leading-tight mb-6">
                    Selamat Datang di<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-elegant-300 to-white">
                        SIM Mahasiswa UMB
                    </span>
                </h1>

                <p class="text-slate-200/90 text-base md:text-lg leading-relaxed mb-8 max-w-xl mx-auto lg:mx-0">
                    Portal informasi akademik terintegrasi untuk kelola data mahasiswa, program studi,
                    dan fakultas — cepat, transparan, serta tercatat dalam sistem audit.
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

                    <div class="mt-6 pt-6 border-t border-white/10 flex items-center gap-3">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-elegant-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-elegant-400"></span>
                        </span>
                        <p class="text-xs text-slate-300">Basis data aktif &amp; sinkron dengan sistem akademik</p>
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
        <div class="card-hover bg-white rounded-2xl shadow-card border border-slate-100 p-6 md:p-7 flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-academic-100 text-academic-700 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-building-columns text-xl"></i>
            </div>
            <div>
                <p class="font-heading font-extrabold text-3xl counter-gradient leading-none">
                    <?= number_format($totalFakultas) ?>
                </p>
                <p class="text-sm text-slate-500 font-medium mt-1.5">Jumlah Fakultas</p>
            </div>
        </div>

        <!-- Kartu: Jumlah Program Studi -->
        <div class="card-hover bg-white rounded-2xl shadow-card border border-slate-100 p-6 md:p-7 flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-elegant-100 text-elegant-700 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-book-open text-xl"></i>
            </div>
            <div>
                <p class="font-heading font-extrabold text-3xl counter-gradient leading-none">
                    <?= number_format($totalProdi) ?>
                </p>
                <p class="text-sm text-slate-500 font-medium mt-1.5">Program Studi</p>
            </div>
        </div>

        <!-- Kartu: Total Mahasiswa Aktif -->
        <div class="card-hover bg-white rounded-2xl shadow-card border border-slate-100 p-6 md:p-7 flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-slate-900 text-white flex items-center justify-center shrink-0">
                <i class="fa-solid fa-user-graduate text-xl"></i>
            </div>
            <div>
                <p class="font-heading font-extrabold text-3xl counter-gradient leading-none">
                    <?= number_format($totalMahasiswaAktif) ?>
                </p>
                <p class="text-sm text-slate-500 font-medium mt-1.5">Mahasiswa Aktif</p>
            </div>
        </div>

    </div>
</section>

<!-- ============================================================
     CARD FITUR UTAMA SIM
     ============================================================ -->
<section id="fitur" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
    <div class="text-center mb-12 md:mb-16">
        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-academic-50 text-academic-700 text-xs font-bold uppercase tracking-wider mb-4">
            <i class="fa-solid fa-sparkles"></i>
            Fitur Unggulan
        </span>
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
        <article class="card-hover group bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden">
            <div class="h-2 bg-gradient-to-r from-academic-600 to-academic-400"></div>
            <div class="p-7 md:p-8">
                <div class="w-14 h-14 rounded-2xl bg-academic-100 text-academic-700 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-laptop-code text-xl"></i>
                </div>
                <h3 class="font-heading font-bold text-lg text-slate-900 mb-3">Akses Akademik</h3>
                <p class="text-sm text-slate-600 leading-relaxed mb-5">
                    Akses terpadu untuk informasi fakultas, program studi, dan data kependudukan
                    mahasiswa — kapan pun dan di mana pun, melalui portal web yang responsif.
                </p>
                <ul class="space-y-2.5">
                    <li class="flex items-start gap-2.5 text-sm text-slate-600">
                        <i class="fa-solid fa-circle-check text-elegant-600 mt-0.5"></i>
                        Informasi prodi &amp; jenjang terstruktur
                    </li>
                    <li class="flex items-start gap-2.5 text-sm text-slate-600">
                        <i class="fa-solid fa-circle-check text-elegant-600 mt-0.5"></i>
                        Data mahasiswa terpusat dan terverifikasi
                    </li>
                    <li class="flex items-start gap-2.5 text-sm text-slate-600">
                        <i class="fa-solid fa-circle-check text-elegant-600 mt-0.5"></i>
                        Akses dashboard sesuai peran pengguna
                    </li>
                </ul>
            </div>
        </article>

        <!-- Fitur 2: Transparansi Data -->
        <article class="card-hover group bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden">
            <div class="h-2 bg-gradient-to-r from-elegant-600 to-elegant-400"></div>
            <div class="p-7 md:p-8">
                <div class="w-14 h-14 rounded-2xl bg-elegant-100 text-elegant-700 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-database text-xl"></i>
                </div>
                <h3 class="font-heading font-bold text-lg text-slate-900 mb-3">Transparansi Data</h3>
                <p class="text-sm text-slate-600 leading-relaxed mb-5">
                    Seluruh data akademik disajikan secara akurat dan konsisten berdasarkan
                    basis data MySQL, sehingga informasi yang tampil dapat dipertanggungjawabkan.
                </p>
                <ul class="space-y-2.5">
                    <li class="flex items-start gap-2.5 text-sm text-slate-600">
                        <i class="fa-solid fa-circle-check text-elegant-600 mt-0.5"></i>
                        Data real-time dari database terpusat
                    </li>
                    <li class="flex items-start gap-2.5 text-sm text-slate-600">
                        <i class="fa-solid fa-circle-check text-elegant-600 mt-0.5"></i>
                        Konsistensi data antar-unit fakultas
                    </li>
                    <li class="flex items-start gap-2.5 text-sm text-slate-600">
                        <i class="fa-solid fa-circle-check text-elegant-600 mt-0.5"></i>
                        Tampilan publik yang mudah dipahami
                    </li>
                </ul>
            </div>
        </article>

        <!-- Fitur 3: Audit System -->
        <article class="card-hover group bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden">
            <div class="h-2 bg-gradient-to-r from-slate-800 to-slate-500"></div>
            <div class="p-7 md:p-8">
                <div class="w-14 h-14 rounded-2xl bg-slate-900 text-white flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-shield-halved text-xl"></i>
                </div>
                <h3 class="font-heading font-bold text-lg text-slate-900 mb-3">Audit System</h3>
                <p class="text-sm text-slate-600 leading-relaxed mb-5">
                    Setiap aktivitas login, perubahan data, dan proses administrasi dicatat
                    otomatis ke tabel audit_log untuk menjamin akuntabilitas sistem.
                </p>
                <ul class="space-y-2.5">
                    <li class="flex items-start gap-2.5 text-sm text-slate-600">
                        <i class="fa-solid fa-circle-check text-elegant-600 mt-0.5"></i>
                        Log otomatis setiap aksi pengguna
                    </li>
                    <li class="flex items-start gap-2.5 text-sm text-slate-600">
                        <i class="fa-solid fa-circle-check text-elegant-600 mt-0.5"></i>
                        Jejak waktu, IP, &amp; user-agent tersimpan
                    </li>
                    <li class="flex items-start gap-2.5 text-sm text-slate-600">
                        <i class="fa-solid fa-circle-check text-elegant-600 mt-0.5"></i>
                        Monitoring khusus oleh administrator
                    </li>
                </ul>
            </div>
        </article>

    </div>
</section>

<!-- ============================================================
     SECTION CTA AKHIR
     ============================================================ -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16 md:pb-24">
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-academic-800 via-academic-700 to-elegant-800 shadow-soft">
        <div class="absolute -top-16 -right-16 w-72 h-72 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute -bottom-20 -left-10 w-80 h-80 rounded-full bg-elegant-500/20 blur-3xl"></div>

        <div class="relative px-6 sm:px-10 py-12 md:py-16 text-center">
            <h2 class="font-heading font-extrabold text-white text-2xl sm:text-3xl lg:text-4xl mb-4">
                Siap Mengakses Portal Akademik?
            </h2>
            <p class="text-slate-200/90 max-w-2xl mx-auto mb-8 text-sm md:text-base leading-relaxed">
                Masuk ke SIM Mahasiswa UMB untuk mengelola data akademik secara resmi,
                atau telusuri daftar fakultas dan program studi kami terlebih dahulu.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="<?= url('fakultas-prodi.php') ?>"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl bg-white text-academic-800 font-bold text-sm shadow-soft hover:shadow-lg hover:-translate-y-0.5 transition-all">
                    <i class="fa-solid fa-building-columns"></i>
                    Jelajahi Program Studi
                </a>
                <a href="<?= url('auth/login.php') ?>"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl border-2 border-white/50 text-white font-bold text-sm hover:bg-white/10 hover:-translate-y-0.5 transition-all">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Login Sekarang
                </a>
            </div>
        </div>
    </div>
</section>

<?php
// ---------- Footer ----------
require_once __DIR__ . '/layouts/public/footer.php';
?>

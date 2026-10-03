<?php
/**
 * ============================================================
 *  about.php — Halaman About Developer
 * ============================================================
 */

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

$pageTitle = 'About Dev — SIM Mahasiswa UMB';
$pageDesc  = 'Tim Pengembang Sistem Informasi Manajemen Mahasiswa Universitas Muhammadiyah Bengkulu.';
$activePage = 'about';

// Data Pengembang
$developers = [
    [
        'nama' => 'Egi Putu Wijaya',
        'deskripsi' => 'Bertanggung jawab atas arsitektur sistem, basis data, dan antarmuka pengguna secara keseluruhan.',
        'github' => 'https://github.com/egiputuwijaya',
        
    ],
    [
        'nama' => 'Khairul Pratama',
        'deskripsi' => 'Fokus pada perancangan basis data, integrasi sistem, dan manajemen keamanan backend.',
        'github' => 'https://github.com/Pratama12340',
        
    ],
    [
        'nama' => 'Rahman Badio',
        'deskripsi' => 'Membangun komponen antarmuka yang responsif, modern, dan memberikan pengalaman pengguna yang optimal.',
        'github' => 'https://github.com/erxanz',
     
    ]
];

?>

<!-- Layout: Header -> Navbar -> Konten -> Footer -->
<?php require_once __DIR__ . '/layouts/public/header.php'; ?>
<?php require_once __DIR__ . '/layouts/public/navbar.php'; ?>

<!-- ============================================================
     HERO HEADER HALAMAN
     ============================================================ -->
<section class="relative overflow-hidden min-h-[80vh] flex flex-col justify-start w-full">
    <!-- Hero Slider Backgrounds -->
    <div id="hero-bg-1" class="absolute inset-0 bg-cover bg-center transition-opacity duration-1000 opacity-100" style="background-image: url('<?= url('assets/person/1.jpeg') ?>');"></div>
    <div id="hero-bg-2" class="absolute inset-0 bg-cover bg-center transition-opacity duration-1000 opacity-0" style="background-image: url('<?= url('assets/person/2.jpeg') ?>');"></div>
    <div id="hero-bg-3" class="absolute inset-0 bg-cover bg-center transition-opacity duration-1000 opacity-0" style="background-image: url('<?= url('assets/person/3.jpeg') ?>');"></div>

    <!-- Overlay Transparan Hitam -->
    <div class="absolute inset-0 bg-slate-900/80 pointer-events-none"></div>

    <!-- Dekorasi background -->
    <div class="absolute inset-0 opacity-20 pointer-events-none">
        <div class="absolute -top-20 -right-20 w-96 h-96 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute bottom-0 -left-24 w-80 h-80 rounded-full bg-elegant-500/20 blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 md:pt-12 pb-10 text-center">
        <h1 class="font-heading font-extrabold text-white text-3xl sm:text-4xl md:text-5xl leading-tight mb-4">
            Tim Pengembang
        </h1>
        <p class="text-slate-200/90 text-sm md:text-base leading-relaxed max-w-2xl mx-auto drop-shadow-sm">
            Kenali profil tiga pengembang di balik pembuatan Sistem Informasi Manajemen
            Mahasiswa Universitas Muhammadiyah Bengkulu.
        </p>
    </div>
</section>

<!-- ============================================================
     PROFIL PENGEMBANG (CARDS)
     ============================================================ -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-20 md:-mt-32 pb-16 md:pb-24 relative z-10">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-10">
        <?php foreach ($developers as $dev): ?>
            <div class="group bg-white rounded-2xl shadow-sm hover:shadow-card border border-slate-100 overflow-hidden transition-all duration-300 transform hover:-translate-y-1">
                <div class="px-6 py-10 relative text-center">
                    <!-- Info Text -->
                    <h2 class="font-heading font-extrabold text-xl text-slate-900 mb-4">
                        <?= htmlspecialchars($dev['nama'], ENT_QUOTES, 'UTF-8') ?>
                    </h2>
                    <p class="text-slate-500 text-sm leading-relaxed mb-6">
                        <?= htmlspecialchars($dev['deskripsi'], ENT_QUOTES, 'UTF-8') ?>
                    </p>
                    
                    <!-- Sosial Media -->
                    <div class="flex items-center justify-center gap-3">
                        <a href="<?= htmlspecialchars($dev['github'], ENT_QUOTES, 'UTF-8') ?>" class="w-10 h-10 rounded-full bg-slate-50 text-slate-400 flex items-center justify-center hover:bg-slate-900 hover:text-white transition-colors shadow-sm">
                            <i class="fa-brands fa-github text-lg"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<script>
    let heroIndex = 1;
    const totalHeroSlides = 3;
    setInterval(() => {
        for (let i = 1; i <= totalHeroSlides; i++) {
            document.getElementById('hero-bg-' + i).classList.replace('opacity-100', 'opacity-0');
        }
        heroIndex = heroIndex >= totalHeroSlides ? 1 : heroIndex + 1;
        document.getElementById('hero-bg-' + heroIndex).classList.replace('opacity-0', 'opacity-100');
    }, 4000);
</script>

<!-- Catatan / CTA bawah -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
    <div class="pt-10 border-t border-slate-200 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-start md:items-center gap-4 text-center md:text-left flex-col md:flex-row">
            <div class="w-12 h-12 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center shrink-0 mx-auto md:mx-0">
                <i class="fa-solid fa-code text-slate-500 text-lg"></i>
            </div>
            <div>
                <h3 class="font-heading font-extrabold text-slate-900 text-lg mb-1">Proyek Kelompok Web Semantik</h3>
                <p class="text-slate-500 text-sm leading-relaxed max-w-xl">
                    Sistem ini dibangun sebagai bagian dari tugas dan karya inovasi mahasiswa
                    di Universitas Muhammadiyah Bengkulu.
                </p>
            </div>
        </div>
        <a href="<?= url('index.php') ?>"
           class="shrink-0 inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-white border border-slate-300 text-slate-700 font-bold text-sm hover:bg-slate-50 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
            Kembali ke Beranda
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/layouts/public/footer.php'; ?>

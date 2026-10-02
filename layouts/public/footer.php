<?php
/**
 * ============================================================
 *  layouts/public/footer.php
 *  Footer 3 kolom + copyright tahun berjalan
 * ============================================================
 *  Data universitas diambil dari DB (`universitas`) —
 *  diisi oleh pemanggil halaman via $univData (array) jika ada.
 */
if (!isset($univData) || !is_array($univData)) {
    // Fallback statis bila DB belum tersedia di halaman pemanggil
    $univData = [
        'nama_universitas' => 'Universitas Muhammadiyah Bengkulu',
        'slogan'           => 'Bersama Membangun Negeri',
        'alamat'           => 'Jl. Kebun Tebe, Kec. Ratu Agung, Kota Bengkulu',
        'kota'             => 'Bengkulu',
        'provinsi'         => 'Bengkulu',
        'website'          => 'https://umb.ac.id',
        'email'            => 'info@umb.ac.id',
        'telepon'          => '(0736) 000000',
    ];
}

/**
 * Helper: ambil nilai field DB, kembalikan fallback bila NULL / string kosong
 */
$footer_field = function (string $key, string $fallback) use ($univData): string {
    $val = $univData[$key] ?? '';
    return is_string($val) && trim($val) !== '' ? $val : $fallback;
};

$namaUniv = $footer_field('nama_universitas', 'Universitas Muhammadiyah Bengkulu');
$slogan   = $footer_field('slogan', 'Bersama Membangun Negeri');
$alamat   = trim(
    $footer_field('alamat', 'Jl. Kebun Tebe, Kec. Ratu Agung') . ', '
    . $footer_field('kota', 'Bengkulu') . ', '
    . $footer_field('provinsi', 'Bengkulu'),
    ' ,'
);
$website = $footer_field('website', 'https://umb.ac.id');
$email   = $footer_field('email', 'info@umb.ac.id');
$telepon = $footer_field('telepon', '(0736) 000000');
$tahun   = date('Y');
?>
<!-- ===== FOOTER ===== -->
<footer class="mt-auto bg-slate-900 text-slate-300">
    <!-- Bagian Atas Footer -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10 lg:gap-12">

            <!-- Kolom 1: Profil Singkat Universitas -->
            <div>
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-academic-600 to-elegant-600 flex items-center justify-center shadow-soft">
                        <i class="fa-solid fa-graduation-cap text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="font-heading font-bold text-white text-base leading-tight">SIM Mahasiswa UMB</p>
                        <p class="text-[11px] text-elegant-400 font-semibold">Sistem Informasi Manajemen</p>
                    </div>
                </div>
                <p class="font-semibold text-white text-sm mb-1"><?= htmlspecialchars($namaUniv, ENT_QUOTES, 'UTF-8') ?></p>
                <?php if ($slogan !== ''): ?>
                    <p class="text-xs italic text-academic-300 mb-4">"<?= htmlspecialchars($slogan, ENT_QUOTES, 'UTF-8') ?>"</p>
                <?php endif; ?>
                <p class="text-sm leading-relaxed flex items-start gap-2">
                    <i class="fa-solid fa-location-dot mt-1 text-elegant-400 shrink-0"></i>
                    <span><?= htmlspecialchars($alamat, ENT_QUOTES, 'UTF-8') ?></span>
                </p>
            </div>

            <!-- Kolom 2: Tautan Cepat -->
            <div>
                <h3 class="font-heading font-bold text-white text-sm uppercase tracking-wider mb-5 flex items-center gap-2">
                    <span class="w-8 h-0.5 bg-elegant-500 inline-block"></span>
                    Tautan Cepat
                </h3>
                <ul class="space-y-3">
                    <li>
                        <a href="<?= url('index.php') ?>"
                           class="group inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors">
                            <i class="fa-solid fa-house text-elegant-500 text-xs group-hover:translate-x-0.5 transition-transform"></i>
                            Beranda
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('fakultas-prodi.php') ?>"
                           class="group inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors">
                            <i class="fa-solid fa-building-columns text-elegant-500 text-xs group-hover:translate-x-0.5 transition-transform"></i>
                            Fakultas & Program Studi
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('auth/login.php') ?>"
                           class="group inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors">
                            <i class="fa-solid fa-right-to-bracket text-elegant-500 text-xs group-hover:translate-x-0.5 transition-transform"></i>
                            Login Portal
                        </a>
                    </li>
                    <li>
                        <a href="<?= url('index.php') ?>#fitur"
                           class="group inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors">
                            <i class="fa-solid fa-cubes text-elegant-500 text-xs group-hover:translate-x-0.5 transition-transform"></i>
                            Fitur SIM
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Kolom 3: Kontak -->
            <div>
                <h3 class="font-heading font-bold text-white text-sm uppercase tracking-wider mb-5 flex items-center gap-2">
                    <span class="w-8 h-0.5 bg-elegant-500 inline-block"></span>
                    Kontak
                </h3>
                <ul class="space-y-4 text-sm">
                    <li class="flex items-start gap-3">
                        <span class="w-9 h-9 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-envelope text-academic-400"></i>
                        </span>
                        <div>
                            <p class="text-[11px] uppercase tracking-wide text-slate-500 font-semibold">Email</p>
                            <a href="mailto:<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                               class="text-slate-300 hover:text-elegant-400 transition-colors break-all">
                                <?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="w-9 h-9 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-phone text-academic-400"></i>
                        </span>
                        <div>
                            <p class="text-[11px] uppercase tracking-wide text-slate-500 font-semibold">Telepon</p>
                            <a href="tel:<?= htmlspecialchars(str_replace([' ', '-'], '', $telepon), ENT_QUOTES, 'UTF-8') ?>"
                               class="text-slate-300 hover:text-elegant-400 transition-colors">
                                <?= htmlspecialchars($telepon, ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="w-9 h-9 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-globe text-academic-400"></i>
                        </span>
                        <div>
                            <p class="text-[11px] uppercase tracking-wide text-slate-500 font-semibold">Website</p>
                            <a href="<?= htmlspecialchars($website, ENT_QUOTES, 'UTF-8') ?>"
                               target="_blank" rel="noopener noreferrer"
                               class="text-slate-300 hover:text-elegant-400 transition-colors break-all">
                                <?= htmlspecialchars($website, ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>

        </div>
    </div>

    <!-- Copyright -->
    <div class="border-t border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-slate-500 text-center sm:text-left">
                &copy; <?= $tahun ?> <span class="text-slate-400 font-semibold"><?= htmlspecialchars($namaUniv, ENT_QUOTES, 'UTF-8') ?></span>.
                Seluruh hak cipta dilindungi.
            </p>
            <p class="text-xs text-slate-500 flex items-center gap-2">
                <i class="fa-solid fa-shield-halved text-elegant-500"></i>
                Dibangun dengan
                <span class="text-elegant-400 font-semibold">PHP Native + PDO</span>
                untuk layanan akademik
            </p>
        </div>
    </div>
</footer>

<!-- Kembali ke atas -->
<button id="btn-back-to-top"
        type="button"
        aria-label="Kembali ke atas"
        class="fixed bottom-5 right-5 z-40 hidden w-11 h-11 rounded-full bg-academic-700 text-white shadow-soft hover:bg-academic-800 focus:outline-none focus:ring-2 focus:ring-academic-400 transition-all">
    <i class="fa-solid fa-arrow-up"></i>
</button>

<script>
(function () {
    var btn = document.getElementById('btn-back-to-top');
    if (!btn) return;

    window.addEventListener('scroll', function () {
        if (window.scrollY > 400) {
            btn.classList.remove('hidden');
        } else {
            btn.classList.add('hidden');
        }
    });

    btn.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
})();
</script>
</body>
</html>

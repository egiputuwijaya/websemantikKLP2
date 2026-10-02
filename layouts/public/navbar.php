<?php
/**
 * ============================================================
 *  layouts/public/navbar.php
 *  Navbar responsif (hamburger mobile) + tombol Login
 * ============================================================
 *  Variabel opsional:
 *    $activePage — 'home' | 'fakultas' (untuk highlight menu)
 */
if (!isset($activePage)) {
    $activePage = '';
}

$navItems = [
    'home' => [
        'label' => 'Home',
        'href'  => url('index.php'),
        'icon'  => 'fa-house',
    ],
    'fakultas' => [
        'label' => 'Fakultas & Prodi',
        'href'  => url('fakultas-prodi.php'),
        'icon'  => 'fa-graduation-cap',
    ],
];
?>
<!-- ===== NAVBAR ===== -->
<header class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-slate-200 shadow-sm">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 md:h-20">

            <!-- Logo + Nama Aplikasi -->
            <a href="<?= url('index.php') ?>" class="flex items-center gap-3 group">
                <div class="relative w-10 h-10 md:w-12 md:h-12 rounded-xl bg-gradient-to-br from-academic-700 to-elegant-700 flex items-center justify-center shadow-soft shrink-0">
                    <i class="fa-solid fa-graduation-cap text-white text-lg md:text-xl"></i>
                </div>
                <div class="leading-tight">
                    <span class="block font-heading font-extrabold text-slate-900 text-sm md:text-base tracking-tight">
                        SIM Mahasiswa
                    </span>
                    <span class="block text-[11px] md:text-xs font-semibold text-elegant-700">
                        Universitas Muhammadiyah Bengkulu
                    </span>
                </div>
            </a>

            <!-- Menu Desktop -->
            <ul class="hidden lg:flex items-center gap-8">
                <?php foreach ($navItems as $key => $item): ?>
                    <li>
                        <a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>"
                           class="nav-link inline-flex items-center gap-2 text-sm font-semibold text-slate-700 hover:text-academic-700 transition-colors <?= $activePage === $key ? 'text-academic-700 active' : '' ?>">
                            <i class="fa-solid <?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?> text-academic-600"></i>
                            <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- Aksi Desktop: Tombol Login -->
            <div class="hidden lg:flex items-center gap-3">
                <a href="<?= url('auth/login.php') ?>"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-academic-700 hover:bg-academic-800 text-white text-sm font-semibold shadow-soft transition-all hover:shadow-lg">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Login Sistem
                </a>
            </div>

            <!-- Tombol Hamburger (Mobile) -->
            <button id="btn-mobile-menu"
                    type="button"
                    aria-label="Buka menu navigasi"
                    aria-controls="mobile-menu"
                    aria-expanded="false"
                    class="lg:hidden inline-flex items-center justify-center w-11 h-11 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-academic-500">
                <i id="icon-hamburger" class="fa-solid fa-bars text-lg"></i>
                <i id="icon-close" class="fa-solid fa-xmark text-lg hidden"></i>
            </button>
        </div>
    </nav>

    <!-- Menu Mobile -->
    <div id="mobile-menu" class="lg:hidden hidden border-t border-slate-200 bg-white">
        <div class="px-4 sm:px-6 py-4 space-y-2">
            <?php foreach ($navItems as $key => $item): ?>
                <a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-slate-700 hover:bg-academic-50 hover:text-academic-700 transition-colors <?= $activePage === $key ? 'bg-academic-50 text-academic-700' : '' ?>">
                    <span class="w-9 h-9 rounded-lg bg-academic-100 text-academic-700 flex items-center justify-center">
                        <i class="fa-solid <?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?>"></i>
                    </span>
                    <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>

            <a href="<?= url('auth/login.php') ?>"
               class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-academic-700 to-elegant-700 shadow-soft">
                <span class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
                    <i class="fa-solid fa-right-to-bracket"></i>
                </span>
                Login Sistem
            </a>
        </div>
    </div>
</header>

<script>
(function () {
    var btn = document.getElementById('btn-mobile-menu');
    var menu = document.getElementById('mobile-menu');
    var iconHamburger = document.getElementById('icon-hamburger');
    var iconClose = document.getElementById('icon-close');

    if (!btn || !menu) return;

    btn.addEventListener('click', function () {
        var isOpen = !menu.classList.contains('hidden');
        if (isOpen) {
            menu.classList.add('hidden');
            iconHamburger.classList.remove('hidden');
            iconClose.classList.add('hidden');
            btn.setAttribute('aria-expanded', 'false');
        } else {
            menu.classList.remove('hidden');
            iconHamburger.classList.add('hidden');
            iconClose.classList.remove('hidden');
            btn.setAttribute('aria-expanded', 'true');
        }
    });
})();
</script>

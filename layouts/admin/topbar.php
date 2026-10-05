<?php
/**
 * ============================================================
 *  layouts/admin/topbar.php — Bar atas (profil user & logout)
 * ============================================================
 *  Menampilkan nama lengkap, role, dan tombol Logout.
 */

$user       = get_user_login() ?? [];
$namaLengkap = $user['nama_lengkap'] ?? 'Pengguna';
$namaRole    = $user['nama_role'] ?? '-';
$username    = $user['username'] ?? '-';

$initials = '';
$parts = preg_split('/\s+/', trim($namaLengkap));
foreach (array_slice($parts, 0, 2) as $p) {
    $initials .= mb_strtoupper(mb_substr($p, 0, 1));
}
if ($initials === '') {
    $initials = 'U';
}
?>
<header class="sticky top-0 z-20 bg-white/95 backdrop-blur border-b border-slate-200 shadow-sm">
    <div class="h-16 flex items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">

        <!-- Kiri: Tombol hamburger + judul halaman -->
        <div class="flex items-center gap-3 min-w-0">
            <button type="button"
                    id="btn-open-sidebar"
                    aria-label="Buka menu navigasi"
                    class="lg:hidden w-10 h-10 rounded-xl border border-slate-200 bg-white text-slate-600 flex items-center justify-center hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-academic-500">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="min-w-0">
                <h1 class="font-heading font-bold text-slate-900 text-base sm:text-lg truncate">
                    <?= htmlspecialchars($pageTitle ?? 'Dashboard', ENT_QUOTES, 'UTF-8') ?>
                </h1>
                <p class="text-[11px] text-slate-500 truncate hidden sm:block">
                    SIM Mahasiswa — Universitas Muhammadiyah Bengkulu
                </p>
            </div>
        </div>

        <!-- Kanan: Profil user + Logout -->
        <div class="flex items-center gap-2 sm:gap-3">

            <!-- Info user (desktop) -->
            <div class="hidden md:flex items-center gap-3 pl-3 pr-4 py-1.5 rounded-md bg-slate-50 border border-slate-200">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-academic-600 to-elegant-600 flex items-center justify-center text-white font-bold text-xs shrink-0">
                    <?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="leading-tight min-w-0">
                    <p class="text-sm font-semibold text-slate-900 truncate max-w-[10rem]"><?= htmlspecialchars($namaLengkap, ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="text-[11px] text-academic-700 font-semibold truncate max-w-[10rem]">
                        <?= htmlspecialchars($namaRole, ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>
            </div>

            <!-- Avatar user (mobile) -->
            <div class="md:hidden w-9 h-9 rounded-full bg-gradient-to-br from-academic-600 to-elegant-600 flex items-center justify-center text-white font-bold text-xs shrink-0"
                 title="<?= htmlspecialchars($namaLengkap, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?>
            </div>

            <!-- Tombol Logout (bawa token CSRF agar tidak bisa dipicu dari situs lain) -->
            <a href="<?= url('auth/logout.php') ?>?csrf_token=<?= urlencode(csrf_token()) ?>"
               data-confirm="Apakah Anda yakin ingin keluar dari sistem?"
               class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white text-xs sm:text-sm font-semibold shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-1">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span class="hidden sm:inline">Logout</span>
            </a>
        </div>
    </div>
</header>

<script>
(function () {
    var btnOpen = document.getElementById('btn-open-sidebar');
    var btnClose = document.getElementById('btn-close-sidebar');
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebar-overlay');

    function openSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('open');
        if (overlay) overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('open');
        if (overlay) overlay.classList.add('hidden');
        document.body.style.overflow = '';
    }

    if (btnOpen) btnOpen.addEventListener('click', openSidebar);
    if (btnClose) btnClose.addEventListener('click', closeSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);
})();
</script>

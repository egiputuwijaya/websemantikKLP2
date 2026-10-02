<?php
/**
 * ============================================================
 *  layouts/admin/sidebar.php — Navigasi sidebar per-role
 * ============================================================
 *  Menu menyesuaikan $_SESSION['user']['id_role']:
 *   1 ADMIN      : Dashboard, Pengguna, Fakultas, Prodi, Mahasiswa, Audit Log
 *   2 OPERATOR   : Dashboard, Data Mahasiswa (Prodi)
 *   3 DEKANAT    : Dashboard, Laporan Mahasiswa
 *   4 REKTORAT   : Dashboard, Laporan Mahasiswa
 *   5 MAHASISWA  : Dashboard, Profil Saya
 */

$user       = get_user_login() ?? [];
$idRole     = (int) ($user['id_role'] ?? 0);
$activeMenu = $activeMenu ?? '';

// Definisi menu per role
$menuByRole = [
    1 => [ // ADMIN
        ['key' => 'dashboard', 'label' => 'Dashboard',      'icon' => 'fa-gauge-high',          'href' => url('admin/dashboard/index.php')],
        ['key' => 'pengguna',  'label' => 'Data Pengguna',  'icon' => 'fa-users-gear',          'href' => url('admin/pengguna/index.php')],
        ['key' => 'fakultas',  'label' => 'Fakultas',       'icon' => 'fa-building-columns',    'href' => url('admin/fakultas/index.php')],
        ['key' => 'prodi',     'label' => 'Program Studi',  'icon' => 'fa-book-open-reader',    'href' => url('admin/program_studi/index.php')],
        ['key' => 'mahasiswa', 'label' => 'Data Mahasiswa', 'icon' => 'fa-user-graduate',       'href' => url('admin/mahasiswa/index.php')],
        ['key' => 'audit_log', 'label' => 'Audit Log',      'icon' => 'fa-clipboard-list',      'href' => url('admin/audit_log/index.php')],
    ],
    2 => [ // OPERATOR PRODI
        ['key' => 'dashboard', 'label' => 'Dashboard',           'icon' => 'fa-gauge-high',     'href' => url('admin/dashboard/index.php')],
        ['key' => 'mahasiswa', 'label' => 'Data Mahasiswa Prodi', 'icon' => 'fa-user-graduate',  'href' => url('admin/mahasiswa/index.php')],
    ],
    3 => [ // DEKANAT
        ['key' => 'dashboard', 'label' => 'Dashboard',           'icon' => 'fa-gauge-high',     'href' => url('admin/dashboard/index.php')],
        ['key' => 'laporan',   'label' => 'Laporan Mahasiswa',   'icon' => 'fa-file-lines',     'href' => url('admin/mahasiswa/index.php')],
    ],
    4 => [ // REKTORAT
        ['key' => 'dashboard', 'label' => 'Dashboard',           'icon' => 'fa-gauge-high',     'href' => url('admin/dashboard/index.php')],
        ['key' => 'laporan',   'label' => 'Laporan Mahasiswa',   'icon' => 'fa-file-lines',     'href' => url('admin/mahasiswa/index.php')],
    ],
    5 => [ // MAHASISWA
        ['key' => 'dashboard', 'label' => 'Dashboard',      'icon' => 'fa-gauge-high',   'href' => url('admin/dashboard/index.php')],
        ['key' => 'profil',    'label' => 'Profil Saya',    'icon' => 'fa-id-card',      'href' => url('admin/profil-saya/index.php')],
    ],
];

$menuItems = $menuByRole[$idRole] ?? [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'fa-gauge-high', 'href' => url('admin/dashboard/index.php')],
];

$initials = '';
$parts = preg_split('/\s+/', trim($namaLengkap ?? 'U'));
foreach (array_slice($parts, 0, 2) as $p) {
    $initials .= mb_strtoupper(mb_substr($p, 0, 1));
}
if ($initials === '') {
    $initials = 'U';
}
?>
<aside id="sidebar"
       class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 text-slate-300 flex flex-col shadow-soft">

    <!-- Logo -->
    <div class="h-16 flex items-center gap-3 px-5 border-b border-white/10 shrink-0">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-academic-600 to-elegant-600 flex items-center justify-center shadow-soft shrink-0">
            <i class="fa-solid fa-graduation-cap text-white text-lg"></i>
        </div>
        <div class="leading-tight min-w-0">
            <p class="font-heading font-extrabold text-white text-sm truncate">SIM Mahasiswa</p>
            <p class="text-[11px] text-elegant-400 font-semibold truncate">UMB — Internal</p>
        </div>
        <!-- Tombol tutup (mobile) -->
        <button type="button" id="btn-close-sidebar"
                class="lg:hidden ml-auto w-8 h-8 rounded-lg bg-white/10 text-white flex items-center justify-center hover:bg-white/20">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Profil ringkas -->
    <div class="px-5 py-4 border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-academic-500 to-elegant-600 flex items-center justify-center text-white font-bold text-sm shrink-0">
                <?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-white truncate"><?= htmlspecialchars($namaLengkap, ENT_QUOTES, 'UTF-8') ?></p>
                <p class="text-[11px] text-academic-300 truncate"><?= htmlspecialchars($namaRole, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>
    </div>

    <!-- Menu navigasi -->
    <nav class="flex-1 overflow-y-auto py-4 px-3">
        <p class="px-3 mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">Menu Utama</p>
        <ul class="space-y-1">
            <?php foreach ($menuItems as $item):
                $isActive = ($activeMenu === $item['key']);
            ?>
                <li>
                    <a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>"
                       class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:bg-white/5 hover:text-white transition-colors <?= $isActive ? 'active' : '' ?>">
                        <i class="fa-solid <?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?> w-5 text-center <?= $isActive ? 'text-elegant-400' : 'text-slate-500' ?>"></i>
                        <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <!-- Footer sidebar -->
    <div class="px-5 py-4 border-t border-white/10 shrink-0">
        <a href="<?= url('index.php') ?>"
           class="flex items-center gap-2 text-xs text-slate-400 hover:text-elegant-400 transition-colors">
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
            Lihat Situs Publik
        </a>
    </div>
</aside>

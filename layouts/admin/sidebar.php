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
        ['key' => 'universitas', 'label' => 'Profil Universitas', 'icon' => 'fa-building',      'href' => url('admin/universitas/index.php')],
        ['key' => 'pengguna',  'label' => 'Data Pengguna',  'icon' => 'fa-users-gear',          'href' => url('admin/pengguna/index.php')],
        ['key' => 'fakultas',  'label' => 'Fakultas',       'icon' => 'fa-building-columns',    'href' => url('admin/fakultas/index.php')],
        ['key' => 'prodi',     'label' => 'Program Studi',  'icon' => 'fa-book-open-reader',    'href' => url('admin/program_studi/index.php')],
        ['key' => 'mahasiswa', 'label' => 'Data Mahasiswa', 'icon' => 'fa-user-graduate',       'href' => url('admin/mahasiswa/index.php')],
        ['key' => 'audit_log', 'label' => 'Audit Log',      'icon' => 'fa-clipboard-list',      'href' => url('admin/audit_log/index.php')],
        ['key' => 'profil',    'label' => 'Pengaturan Akun', 'icon' => 'fa-user-gear',           'href' => url('admin/profil/index.php')],
    ],
    2 => [ // OPERATOR PRODI
        ['key' => 'dashboard', 'label' => 'Dashboard',           'icon' => 'fa-gauge-high',     'href' => url('admin/dashboard/index.php')],
        ['key' => 'mahasiswa', 'label' => 'Data Mahasiswa Prodi', 'icon' => 'fa-user-graduate',  'href' => url('admin/mahasiswa/index.php')],
        ['key' => 'profil',    'label' => 'Pengaturan Akun',     'icon' => 'fa-user-gear',      'href' => url('admin/profil/index.php')],
    ],
    3 => [ // DEKANAT
        ['key' => 'dashboard', 'label' => 'Dashboard',           'icon' => 'fa-gauge-high',     'href' => url('admin/dashboard/index.php')],
        ['key' => 'laporan',   'label' => 'Laporan Mahasiswa',   'icon' => 'fa-file-lines',     'href' => url('admin/laporan/index.php')],
        ['key' => 'profil',    'label' => 'Pengaturan Akun',     'icon' => 'fa-user-gear',      'href' => url('admin/profil/index.php')],
    ],
    4 => [ // REKTORAT
        ['key' => 'dashboard', 'label' => 'Dashboard',           'icon' => 'fa-gauge-high',     'href' => url('admin/dashboard/index.php')],
        ['key' => 'laporan',   'label' => 'Laporan Mahasiswa',   'icon' => 'fa-file-lines',     'href' => url('admin/laporan/index.php')],
        ['key' => 'profil',    'label' => 'Pengaturan Akun',     'icon' => 'fa-user-gear',      'href' => url('admin/profil/index.php')],
    ],
    5 => [ // MAHASISWA
        ['key' => 'dashboard', 'label' => 'Dashboard',      'icon' => 'fa-gauge-high',   'href' => url('admin/dashboard/index.php')],
        ['key' => 'profil_saya', 'label' => 'Profil Mahasiswa', 'icon' => 'fa-id-card',      'href' => url('admin/profil-saya/index.php')],
        ['key' => 'profil',      'label' => 'Pengaturan Akun',  'icon' => 'fa-user-gear',    'href' => url('admin/profil/index.php')],
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
       class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-slate-100 flex flex-col shadow-sm">
       
    <!-- Toggle Collapse Button (Desktop) -->
    <button type="button" id="btn-toggle-sidebar"
            class="hidden lg:flex absolute -right-3 top-6 w-6 h-6 bg-white border border-slate-200 rounded-full items-center justify-center text-slate-400 hover:text-academic-600 shadow-sm z-50 transition-transform">
        <i class="fa-solid fa-chevron-left text-[10px]" id="toggle-sidebar-icon"></i>
    </button>

    <!-- Logo -->
    <div class="sidebar-header h-16 flex items-center gap-3 px-5 border-b border-slate-100 shrink-0">
        <div class="sidebar-logo w-9 h-9 rounded-full overflow-hidden shrink-0">
            <img src="<?= url('assets/logoumb.png') ?>" alt="Logo UMB" class="w-full h-full object-cover">
        </div>
        <div class="sidebar-text leading-tight min-w-0">
            <p class="font-heading font-extrabold text-slate-800 text-sm truncate">SIM UMB</p>
            <p class="text-[10px] text-academic-600 font-bold truncate">Sistem Informasi Mahasiswa</p>
        </div>
        <!-- Tombol tutup (mobile) -->
        <button type="button" id="btn-close-sidebar"
                class="sidebar-text lg:hidden ml-auto w-8 h-8 rounded-lg text-slate-400 flex items-center justify-center hover:bg-slate-100">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Profil ringkas -->
    <div class="sidebar-profile px-5 py-4 border-b border-slate-100 hidden">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-academic-100 flex items-center justify-center text-academic-700 font-bold text-sm shrink-0">
                <?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-slate-700 truncate"><?= htmlspecialchars($namaLengkap, ENT_QUOTES, 'UTF-8') ?></p>
                <p class="text-[11px] text-slate-400 truncate"><?= htmlspecialchars($namaRole, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>
    </div>

    <!-- Menu navigasi -->
    <nav class="flex-1 overflow-y-auto py-4 px-3">
        <!-- Optional: we can hide the text "Menu Utama" or keep it. I'll hide it. -->
        <!-- <p class="sidebar-text px-3 mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">Menu Utama</p> -->
        <ul class="space-y-1">
            <?php foreach ($menuItems as $item):
                $isActive = ($activeMenu === $item['key']);
            ?>
                <li>
                    <a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>"
                       class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors <?= $isActive ? 'active' : 'text-slate-500 hover:bg-slate-50 hover:text-academic-600' ?>">
                        <i class="fa-solid <?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?> w-5 text-center <?= $isActive ? '' : 'text-slate-400' ?>"></i>
                        <span class="sidebar-text"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

</aside>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const toggleBtn = document.getElementById('btn-toggle-sidebar');
        const toggleIcon = document.getElementById('toggle-sidebar-icon');
        
        let isCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';
        
        function applyCollapseState() {
            if (isCollapsed) {
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-20', 'collapsed');
                mainContent.classList.remove('lg:pl-64');
                mainContent.classList.add('lg:pl-20');
                toggleIcon.classList.remove('fa-chevron-left');
                toggleIcon.classList.add('fa-chevron-right');
            } else {
                sidebar.classList.remove('w-20', 'collapsed');
                sidebar.classList.add('w-64');
                mainContent.classList.remove('lg:pl-20');
                mainContent.classList.add('lg:pl-64');
                toggleIcon.classList.remove('fa-chevron-right');
                toggleIcon.classList.add('fa-chevron-left');
            }
        }
        
        // Initial apply
        if (window.innerWidth >= 1024) {
            applyCollapseState();
        }
        
        toggleBtn.addEventListener('click', function() {
            isCollapsed = !isCollapsed;
            localStorage.setItem('sidebar_collapsed', isCollapsed);
            applyCollapseState();
        });
    });
</script>

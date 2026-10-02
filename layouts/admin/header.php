<?php
/**
 * ============================================================
 *  layouts/admin/header.php — Header HTML area admin
 * ============================================================
 *  Membuka <head>, <body>, sidebar, topbar, dan <main>.
 *  Variabel yang diset oleh halaman pemanggil:
 *    $pageTitle  — judul halaman
 *    $activeMenu — kunci menu aktif (dashboard|mahasiswa|...)
 */

if (!defined('BASE_URL')) {
    require_once dirname(__DIR__, 2) . '/config/app.php';
}
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';

check_auth();

if (!isset($pageTitle)) {
    $pageTitle = 'Dashboard';
}
if (!isset($activeMenu)) {
    $activeMenu = '';
}

$current = get_user_login();
$namaLengkap = $current['nama_lengkap'] ?? 'Pengguna';
$namaRole    = $current['nama_role'] ?? 'Pengguna';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dashboard internal SIM Mahasiswa Universitas Muhammadiyah Bengkulu">
    <meta name="theme-color" content="#1e40af">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                        heading: ['Poppins', 'ui-sans-serif', 'system-ui'],
                    },
                    colors: {
                        academic: {
                            50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd',
                            400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8',
                            800: '#1e40af', 900: '#1e3a8a',
                        },
                        elegant: {
                            50: '#f0fdf4', 100: '#dcfce7', 200: '#bbf7d0', 300: '#86efac',
                            400: '#4ade80', 500: '#22c55e', 600: '#16a34a', 700: '#15803d',
                            800: '#166534', 900: '#14532d',
                        },
                    },
                    boxShadow: {
                        'soft': '0 10px 40px -12px rgba(30, 64, 175, 0.18)',
                        'card': '0 4px 24px -6px rgba(15, 23, 42, 0.08)',
                    },
                },
            },
        };
    </script>

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
          crossorigin="anonymous" referrerpolicy="no-referrer">

    <style>
        body { font-family: 'Inter', system-ui, sans-serif; background: #f1f5f9; }
        h1, h2, h3, h4, h5, h6, .font-heading { font-family: 'Poppins', 'Inter', sans-serif; }
        #sidebar { transition: transform .25s ease; }
        @media (max-width: 1023px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
        }
        .sidebar-link.active {
            background: linear-gradient(90deg, rgba(34,197,94,0.18), rgba(30,64,175,0.12));
            color: #fff;
            border-left: 3px solid #22c55e;
        }
        .sidebar-link.active i { color: #86efac; }
        input:focus, select:focus, textarea:focus { outline: none; }
        .table-hover tbody tr:hover { background: #f8fafc; }
    </style>
</head>
<body class="min-h-screen text-slate-800">

<div class="flex min-h-screen">

    <!-- ===================== SIDEBAR ===================== -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <!-- Overlay mobile -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-slate-900/50 z-30 hidden lg:hidden"></div>

    <!-- ===================== KONTEN KANAN ===================== -->
    <div class="flex-1 flex flex-col min-w-0 lg:pl-64">

        <!-- ===================== TOPBAR ===================== -->
        <?php require_once __DIR__ . '/topbar.php'; ?>

        <!-- ===================== MAIN CONTENT ===================== -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <?= render_flash_alert() ?>

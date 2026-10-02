<?php
/**
 * ============================================================
 *  layouts/public/header.php
 *  Tag <head> lengkap + CDN Tailwind, FontAwesome, Google Fonts
 * ============================================================
 *  Dipanggil oleh halaman publik (index.php, fakultas-prodi.php).
 *  Variabel opsional yang dapat dioverride sebelum require:
 *    $pageTitle  — judul halaman
 *    $pageDesc   — deskripsi meta
 */
if (!isset($pageTitle)) {
    $pageTitle = APP_NAME;
}
if (!isset($pageDesc)) {
    $pageDesc = 'Sistem Informasi Manajemen Mahasiswa Universitas Muhammadiyah Bengkulu — akses akademik cepat, transparan, dan teraudit.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($pageDesc, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="author" content="Universitas Muhammadiyah Bengkulu">
    <meta name="theme-color" content="#1e40af">

    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>

    <!-- Favicon (opsional) -->
    <link rel="icon" type="image/png" href="<?= url('assets/images/favicon.png') ?>">

    <!-- Google Fonts: Inter + Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS via CDN (play CDN — untuk pengembangan) -->
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
                            50:  '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                        elegant: {
                            50:  '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
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

    <!-- FontAwesome 6 (CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
          crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- Style kustom tambahan -->
    <style>
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }
        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Poppins', 'Inter', sans-serif;
        }
        .hero-gradient {
            background:
                radial-gradient(1000px 500px at 85% -10%, rgba(34,197,94,0.22), transparent 60%),
                radial-gradient(800px 420px at 10% 110%, rgba(59,130,246,0.28), transparent 55%),
                linear-gradient(135deg, #1e3a8a 0%, #1e40af 45%, #166534 100%);
        }
        .glass {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        .card-hover {
            transition: transform .25s ease, box-shadow .25s ease;
        }
        .card-hover:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 40px -12px rgba(30, 64, 175, 0.25);
        }
        .nav-link {
            position: relative;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -6px;
            height: 2px;
            width: 0;
            background: #22c55e;
            transition: width .25s ease;
        }
        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }
        .counter-gradient {
            background: linear-gradient(135deg, #1e40af, #166534);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col">

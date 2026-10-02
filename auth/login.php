<?php
/**
 * ============================================================
 *  auth/login.php — Form Login Pengguna
 * ============================================================
 *  Halaman login standalone (tidak memakai navbar/footer publik).
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/app/helper.php';

// Jika sudah login, langsung ke dashboard
if (!empty($_SESSION['user'])) {
    redirect(url('admin/dashboard/index.php'));
    exit;
}

$pageTitle = 'Login — ' . APP_NAME;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login ke Sistem Informasi Manajemen Mahasiswa Universitas Muhammadiyah Bengkulu">
    <meta name="theme-color" content="#1e40af">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>

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
        body { font-family: 'Inter', system-ui, sans-serif; }
        h1, h2, h3, .font-heading { font-family: 'Poppins', 'Inter', sans-serif; }
        .login-bg {
            background:
                radial-gradient(900px 480px at 85% -10%, rgba(34,197,94,0.22), transparent 60%),
                radial-gradient(700px 400px at 10% 110%, rgba(59,130,246,0.30), transparent 55%),
                linear-gradient(135deg, #1e3a8a 0%, #1e40af 45%, #166534 100%);
        }
        .glass {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        input:focus { outline: none; }
    </style>
</head>
<body class="login-bg min-h-screen flex items-center justify-center px-4 py-8">

    <div class="w-full max-w-5xl grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">

        <!-- ===== Panel Kiri: Branding ===== -->
        <div class="hidden lg:block text-center lg:text-left">
            <div class="inline-flex items-center gap-3 mb-8">
                <div class="w-14 h-14 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center backdrop-blur">
                    <i class="fa-solid fa-graduation-cap text-white text-2xl"></i>
                </div>
                <div class="text-left">
                    <p class="font-heading font-extrabold text-white text-lg leading-tight">SIM Mahasiswa</p>
                    <p class="text-elegant-300 text-xs font-semibold">Universitas Muhammadiyah Bengkulu</p>
                </div>
            </div>

            <h1 class="font-heading font-extrabold text-white text-4xl leading-tight mb-5">
                Selamat Datang<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-elegant-300 to-white">
                    Kembali
                </span>
            </h1>

            <p class="text-slate-200/90 text-sm leading-relaxed max-w-md mx-auto lg:mx-0 mb-8">
                Masuk untuk mengelola data akademik, program studi, dan layanan
                mahasiswa Universitas Muhammadiyah Bengkulu secara terintegrasi.
            </p>

            <div class="grid grid-cols-3 gap-4 max-w-md mx-auto lg:mx-0">
                <div class="glass rounded-xl p-4 text-center">
                    <i class="fa-solid fa-building-columns text-elegant-300 text-lg mb-2"></i>
                    <p class="text-white font-bold text-lg leading-none">5+</p>
                    <p class="text-[11px] text-slate-300 mt-1">Role</p>
                </div>
                <div class="glass rounded-xl p-4 text-center">
                    <i class="fa-solid fa-shield-halved text-elegant-300 text-lg mb-2"></i>
                    <p class="text-white font-bold text-lg leading-none">Audit</p>
                    <p class="text-[11px] text-slate-300 mt-1">Log Aktif</p>
                </div>
                <div class="glass rounded-xl p-4 text-center">
                    <i class="fa-solid fa-lock text-elegant-300 text-lg mb-2"></i>
                    <p class="text-white font-bold text-lg leading-none">Hash</p>
                    <p class="text-[11px] text-slate-300 mt-1">bcrypt</p>
                </div>
            </div>
        </div>

        <!-- ===== Panel Kanan: Form Login ===== -->
        <div class="w-full">
            <div class="bg-white rounded-3xl shadow-soft p-7 sm:p-9 border border-slate-100">

                <!-- Logo mobile -->
                <div class="lg:hidden flex items-center gap-3 mb-7 justify-center">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-academic-700 to-elegant-700 flex items-center justify-center">
                        <i class="fa-solid fa-graduation-cap text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="font-heading font-extrabold text-slate-900 text-sm leading-tight">SIM Mahasiswa</p>
                        <p class="text-elegant-700 text-[11px] font-semibold">UMB — Login Sistem</p>
                    </div>
                </div>

                <!-- Judul -->
                <div class="mb-7">
                    <h2 class="font-heading font-extrabold text-2xl text-slate-900 mb-1.5">Login Sistem</h2>
                    <p class="text-slate-500 text-sm">Masukkan kredensial akun Anda untuk melanjutkan.</p>
                </div>

                <!-- Flash Message -->
                <?= render_flash_alert() ?>

                <!-- Form -->
                <form action="<?= url('auth/process.php') ?>" method="POST" autocomplete="on" class="space-y-5">

                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Username
                        </label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                                <i class="fa-solid fa-user"></i>
                            </span>
                            <input type="text"
                                   id="username"
                                   name="username"
                                   required
                                   maxlength="100"
                                   placeholder="Masukkan username"
                                   value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                   class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-900 placeholder-slate-400 focus:border-academic-500 focus:bg-white focus:ring-2 focus:ring-academic-100 transition-colors">
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-sm font-semibold text-slate-700">
                                Password
                            </label>
                        </div>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   required
                                   placeholder="Masukkan password"
                                   class="w-full pl-10 pr-12 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-900 placeholder-slate-400 focus:border-academic-500 focus:bg-white focus:ring-2 focus:ring-academic-100 transition-colors">
                            <button type="button"
                                    id="btn-toggle-password"
                                    aria-label="Tampilkan password"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-academic-700 focus:outline-none transition-colors">
                                <i class="fa-solid fa-eye" id="icon-eye"></i>
                                <i class="fa-solid fa-eye-slash hidden" id="icon-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tombol Submit -->
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-gradient-to-r from-academic-700 to-academic-600 hover:from-academic-800 hover:to-academic-700 text-white font-bold text-sm shadow-soft hover:shadow-lg transition-all focus:outline-none focus:ring-2 focus:ring-academic-400 focus:ring-offset-2">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Masuk ke Sistem
                    </button>
                </form>

                <!-- Link kembali -->
                <div class="mt-7 pt-6 border-t border-slate-100 text-center">
                    <a href="<?= url('index.php') ?>"
                       class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-academic-700 font-medium transition-colors">
                        <i class="fa-solid fa-arrow-left"></i>
                        Kembali ke Beranda
                    </a>
                </div>

            </div>

            <!-- Info keamanan -->
            <p class="text-center text-[11px] text-slate-400 mt-5 leading-relaxed">
                <i class="fa-solid fa-shield-halved text-elegant-600"></i>
                Sesi Anda terlindungi. Seluruh aktivitas login dicatat dalam sistem audit.
            </p>
        </div>

    </div>

    <!-- Toggle password visibility -->
    <script>
    (function () {
        var btn = document.getElementById('btn-toggle-password');
        var input = document.getElementById('password');
        var eye = document.getElementById('icon-eye');
        var eyeSlash = document.getElementById('icon-eye-slash');
        if (!btn || !input) return;

        btn.addEventListener('click', function () {
            var isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            eye.classList.toggle('hidden', !isPassword);
            eyeSlash.classList.toggle('hidden', isPassword);
            btn.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Tampilkan password');
        });
    })();
    </script>

</body>
</html>

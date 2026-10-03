# SIM Mahasiswa — Universitas Muhammadiyah Bengkulu (UMB)

Sistem Informasi Manajemen Mahasiswa berbasis **PHP Native** + **PDO MySQL** + **Tailwind CSS**.

- **Database:** `sim_mahasiswa`
- **Basis URL:** `/websemantikKLP2` (Laragon)
- **Stack:** PHP 8.x, PDO, MySQL/MariaDB, Tailwind CSS (CDN), FontAwesome 6

---

## Checklist Status Pengerjaan

### FASE 0: Perancangan Basis Data & Arsitektur Sistem
- [x] Analisis skema SQL `sim_mahasiswa` & pembuatan ERD
- [x] Perancangan struktur folder PHP Native (pemisahan Public, Auth, & Admin)
- [x] File `sim_mahasiswa.sql` (struktur + data awal + view)

### FASE 1: Halaman Publik (Landing Page)
- [x] Layout Header, Navbar, & Footer Publik (`layouts/public/`)
- [x] Landing Page Utama Kampus & Counter Statistik (`index.php`)
- [x] Halaman Informasi Fakultas & Program Studi (`fakultas-prodi.php`)

### FASE 2: Core Engine & Sistem Autentikasi
- [x] Konfigurasi Database PDO & App Setting (`config/database.php`, `config/app.php`)
- [x] Helper Function, Middleware Role, & Audit System (`app/helper.php`, `app/auth.php`, `app/audit.php`)
- [x] Form Login, Verifikasi Hash, Session & Logout (`auth/login.php`, `auth/process.php`, `auth/logout.php`)

### FASE 3: Layout Admin, Dashboard & Modul Mahasiswa
- [x] Topbar, Sidebar Dinamis Berdasarkan Role, & Footer Admin (`layouts/admin/`)
- [x] Dashboard Internal & Ringkasan Statistik (`admin/dashboard/index.php`)
- [x] Modul CRUD Data Mahasiswa & Auto-Create Akun Login (`admin/mahasiswa/`)

### FASE 4: Pengelolaan Data Master
- [x] Modul Kelola Profil Universitas (`admin/universitas/`)
- [x] Modul Kelola Fakultas (`admin/fakultas/`)
- [x] Modul Kelola Program Studi (`admin/program_studi/`)

### FASE 5: Pengelolaan Pengguna & Hak Akses
- [x] Modul Manajemen User / Pengguna Aplikasi (`admin/pengguna/`)
- [x] Modul Pengaturan Akun Pribadi & Ganti Password (`admin/profil/`)

### FASE 6: Portal Mandiri Mahasiswa (Self-Service)
- [x] Halaman Profil Saya & Update Data Kontak Mahasiswa (`admin/profil-saya/`)

### FASE 7: Monitoring & Laporan
- [x] Modul Viewer Audit Log System (`admin/audit_log/`)
- [x] Modul Laporan Mahasiswa, Rekapitulasi & Export Excel/PDF (`admin/laporan/`)

### FASE 8: Keamanan & Pengujian Sistem
- [ ] Hardening `.htaccess` pada folder `uploads/`
- [ ] Implementasi Token Proteksi CSRF pada Form
- [ ] Testing Pengujian Hak Akses (RBAC) & Security Audit

---

## Roadmap Pengembangan (Detail Modul)

### FASE 4: Pengelolaan Data Master

#### 4.1 Modul Profil Universitas (`admin/universitas/`)
- **Akses Role:** Admin (Role 1)
- **Tabel Terkait:** `universitas`
- **Deliverables:**
  - [x] `index.php`: Tampilan informasi profil perguruan tinggi.
  - [x] `edit.php` & `process.php`: Form perbaikan data kampus (nama universitas, slogan, alamat, kota, provinsi, kode pos, email, telepon, website).
  - [x] Integrasi pencatatan `audit_log` untuk aksi `UPDATE` data universitas.

#### 4.2 Modul Kelola Fakultas (`admin/fakultas/`)
- **Akses Role:** Admin (Role 1)
- **Tabel Terkait:** `fakultas`, `universitas`
- **Deliverables:**
  - [x] `index.php`: Tabel daftar fakultas dilengkapi jumlah program studi di dalamnya.
  - [x] `create.php` & `edit.php`: Form penambahan dan pengubahan data fakultas (`kode_fakultas`, `nama_fakultas`).
  - [x] `process.php`: Handler penambahan, pembaruan, dan penghapusan fakultas dengan validasi kunci unik `uk_fakultas_univ_kode`.

#### 4.3 Modul Kelola Program Studi (`admin/program_studi/`)
- **Akses Role:** Admin (Role 1)
- **Tabel Terkait:** `program_studi`, `fakultas`
- **Deliverables:**
  - [x] `index.php`: Tabel daftar program studi dengan filter berdasarkan fakultas.
  - [x] `create.php` & `edit.php`: Form pendaftaran dan pengubahan prodi (`kode_program_studi`, `nama_program_studi`, `jenjang`, `status_aktif`).
  - [x] `process.php`: Handler transaksi data prodi beserta validasi keunikan kombinasi `id_fakultas` & `kode_program_studi`.

---

### FASE 5: Pengelolaan Pengguna & Hak Akses

#### 5.1 Modul Manajemen User (`admin/pengguna/`)
- **Akses Role:** Admin (Role 1)
- **Tabel Terkait:** `pengguna`, `roles`, `universitas`, `fakultas`, `program_studi`
- **Deliverables:**
  - [x] `index.php`: Tabel daftar seluruh pengguna aplikasi dengan filter berdasarkan Role, Fakultas, dan Prodi.
  - [x] `create.php`: Form tambah user manual (Operator Prodi, Dekanat, Rektorat, Admin, Mahasiswa).
  - [x] `edit.php`: Form ubah role, penetapan wilayah kewenangan (`id_fakultas` / `id_program_studi`), serta ubah status aktif/tidak aktif.
  - [x] `reset-password.php`: Fitur reset password user oleh admin menggunakan `password_hash()`.
  - [x] `process.php`: Handler simpan data user dan pencatatan `audit_log` untuk aksi `INSERT`/`UPDATE`/`DELETE`.

#### 5.2 Modul Pengaturan Akun Pribadi (`admin/profil/`)
- **Akses Role:** Semua Role (1, 2, 3, 4, 5)
- **Tabel Terkait:** `pengguna`
- **Deliverables:**
  - [x] `index.php`: Form ubah informasi pribadi (Nama Lengkap, Email, No HP).
  - [x] `ganti-password.php`: Form ubah password sendiri (wajib memasukkan password lama, password baru, dan konfirmasi password baru).

---

### FASE 6: Portal Mandiri Mahasiswa (Self-Service)

#### 6.1 Halaman Profil Mahasiswa (`admin/profil-saya/`)
- **Akses Role:** Mahasiswa (Role 5)
- **Tabel / View Terkait:** View `v_profil_mahasiswa`, tabel `mahasiswa`
- **Deliverables:**
  - [x] `index.php`: Tampilan kartu identitas/profil mahasiswa lengkap (NPM, Nama, TTL, Jenis Kelamin, Tanggal Masuk, Prodi, Fakultas, Universitas, Status Akademik).
  - [x] `edit.php` & `process.php`: Form pembaruan mandiri data kontak/alamat oleh mahasiswa (dengan pembatasan tidak dapat mengubah NPM, Prodi, atau Status Akademik).

---

### FASE 7: Monitoring & Laporan

#### 7.1 Modul Audit Log System (`admin/audit_log/`)
- **Akses Role:** Admin (Role 1)
- **Tabel Terkait:** `audit_log`, `pengguna`
- **Deliverables:**
  - [x] `index.php`: Tabel riwayat aktivitas sistem.
  - [x] Fitur Filter: Berdasarkan rentang tanggal (`waktu`), jenis aksi (`LOGIN`, `LOGOUT`, `INSERT`, `UPDATE`, `DELETE`), dan nama pengguna.
  - [x] `detail.php` / Modal Detail: Viewer untuk membandingkan isi kolom `data_lama` dan `data_baru` (format JSON).

#### 7.2 Modul Laporan Data Mahasiswa (`admin/laporan/`)
- **Akses Role:** Operator Prodi (Role 2), Dekanat (Role 3), Rektorat (Role 4)
- **Tabel / View Terkait:** View `v_mahasiswa_per_prodi`
- **Deliverables:**
  - [x] `index.php`: Halaman rekapitulasi data mahasiswa berbasis grafik & tabel ringkasan (Jumlah Mahasiswa per Status: Aktif, Cuti, Lulus, Drop Out).
  - [x] `export-excel.php`: Skrip ekspor daftar mahasiswa terfilter ke format Spreadsheet/Excel (`.csv` / `.xlsx`).
  - [x] `cetak-pdf.php`: Layout cetak laporan versi PDF / Print-friendly.

---

### FASE 8: Keamanan, Optimasi & Deployment

#### 8.1 Hardening Keamanan
- [X] Proteksi folder `uploads/` dengan file `.htaccess` agar berkas yang diunggah tidak dapat dieksekusi sebagai script PHP.
- [ ] Implementasi token CSRF (`$_SESSION['csrf_token']`) pada seluruh form penambahan, pengubahan, dan penghapusan data.
- [ ] Validasi tipe dan ukuran file upload (misal: foto profil maksimal 2MB, ekstensi `.jpg`, `.jpeg`, `.png`).

#### 8.2 Testing & Quality Assurance
- [ ] Testing Hak Akses (RBAC): Memastikan user dengan Role 5 (Mahasiswa) atau Role 2 (Operator) tidak bisa membobol URL halaman milik Admin (Role 1).
- [ ] Testing Integritas Database: Memastikan transaksi `PDO Transaction` berfungsi saat terjadi pembatalan (*rollback*) ketika pembuatan akun user gagal.
- [ ] Checking SQL Injection & XSS: Memastikan seluruh input menggunakan *Prepared Statements* dan seluruh variabel output dilapisi `htmlspecialchars()`.

---

## Kredensial Default (Development)

Seluruh kredensial di bawah ini sudah ditanamkan pada file `sim_mahasiswa.sql` dan dapat langsung digunakan untuk *login* setelah database diimpor.

| Username | Password | Role / Hak Akses |
|---|---|---|
| `Admin` | `Admin123` | 1 — Admin |
| `operator.ti` | `operator123` | 2 — Operator Program Studi |
| `dekanat.ft` | `dekanat123` | 3 — Dekanat |
| `rektorat` | `rektorat123` | 4 — Rektorat |
| `20260001` | `20260001` | 5 — Mahasiswa |

---

## Struktur Folder

```
sim-mahasiswa/
├── config/
│   ├── database.php          # Koneksi PDO
│   └── app.php               # BASE_URL, session, helper url()
│
├── app/
│   ├── auth.php              # check_auth(), check_role(), require_role()
│   ├── helper.php            # sanitize(), redirect(), flash message
│   └── audit.php             # log_activity() → tabel audit_log
│
├── layouts/
│   ├── public/               # header, navbar, footer halaman publik
│   └── admin/                # header, topbar, sidebar, footer area admin
│
├── auth/                     # login, process, logout
├── admin/
│   ├── dashboard/            # Dashboard dinamis per role
│   └── mahasiswa/            # CRUD mahasiswa (index, create, edit, detail, process)
│
├── assets/                   # css, js, images
├── uploads/                  # file upload (foto profil, dll.)
├── index.php                 # Landing page publik
├── fakultas-prodi.php        # Daftar fakultas & program studi (publik)
└── sim_mahasiswa.sql         # Dump database
```

---

## Roles

| ID | Kode | Nama | Kewenangan Utama |
|---|---|---|---|
| 1 | `ADMIN` | Admin | Kelola seluruh data & konfigurasi |
| 2 | `OPERATOR_PRODI` | Operator Prodi | Kelola mahasiswa pada prodi miliknya |
| 3 | `DEKANAT` | Dekanat | Lihat data mahasiswa fakultasnya |
| 4 | `REKTORAT` | Rektorat | Lihat data mahasiswa tingkat universitas |
| 5 | `MAHASISWA` | Mahasiswa | Lihat profil/data pribadi sendiri |

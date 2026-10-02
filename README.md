# Checklist Status Pengerjaan SIM Mahasiswa (PHP Native)

## FASE 0: Perancangan Basis Data & Arsitektur Sistem
- [x] Analisis Skema SQL `sim_mahasiswa` & Pembuatan ERD (Mermaid)[cite: 1]
- [x] Perancangan Struktur Folder PHP Native (Pemisahan Public, Auth, & Admin)

## FASE 1: Halaman Publik (Landing Page)
- [x] Layout Header, Navbar, & Footer Publik (`layouts/public/`)
- [x] Landing Page Utama Kampus & Counter Statistik (`index.php`)
- [x] Halaman Informasi Fakultas & Program Studi (`fakultas-prodi.php`)

## FASE 2: Core Engine & Sistem Autentikasi
- [x] Konfigurasi Database PDO & App Setting (`config/database.php`, `config/app.php`)
- [x] Helper Function, Middleware Role, & Audit System (`app/helper.php`, `app/auth.php`, `app/audit.php`)
- [x] Form Login, Verifikasi Hash, Session & Logout (`auth/login.php`, `auth/process.php`, `auth/logout.php`)

## FASE 3: Layout Admin, Dashboard & Modul Utama (Mahasiswa)
- [x] Topbar, Sidebar Dinamis Berdasarkan Role, & Footer Admin (`layouts/admin/`)
- [x] Dashboard Internal & Ringkasan Statistik (`admin/dashboard/index.php`)
- [x] Modul CRUD Data Mahasiswa & Auto-Create Akun Login (`admin/mahasiswa/`)

---

## FASE 4: Pengelolaan Data Master (Master Data Management)
- [ ] Modul Kelola Profil Universitas (`admin/universitas/`)[cite: 1]
- [ ] Modul Kelola Fakultas (`admin/fakultas/`)[cite: 1]
- [ ] Modul Kelola Program Studi (`admin/program_studi/`)[cite: 1]

## FASE 5: Pengelolaan Pengguna & Profil (User Management)
- [ ] Modul Manajemen User / Pengguna Aplikasi (`admin/pengguna/`)[cite: 1]
- [ ] Modul Pengaturan Akun Pribadi & Ganti Password (`admin/profil/`)

## FASE 6: Portal Mandiri Mahasiswa (Self-Service)
- [ ] Halaman Profil Saya & Update Data Kontak Mahasiswa (`admin/profil-saya/`)[cite: 1]

## FASE 7: Monitoring & Laporan Executive
- [ ] Modul Viewer Audit Log System (`admin/audit_log/`)[cite: 1]
- [ ] Modul Laporan Mahasiswa, Rekapitulasi & Export Excel/PDF (`admin/laporan/`)[cite: 1]

## FASE 8: Keamanan & Pengujian System
- [ ] Hardening `.htaccess` pada folder `uploads/`
- [ ] Implementasi Token Proteksi CSRF pada Form
- [ ] Testing Pengujian Hak Akses (RBAC) & Security Audit

# Roadmap Pengembangan SIM Mahasiswa (PHP Native)

Dokumen ini berisi daftar modul, tugas teknis, dan spesifikasi pengerjaan yang harus diselesaikan oleh tim pengembang untuk melengkapi Sistem Informasi Manajemen Mahasiswa.

---

## FASE 1: Modul Pengelolaan Data Master (Master Data Management)

### 1.1. Modul Profil Universitas (`admin/universitas/`)
* **Akses Role:** Admin (Role 1)
* **Tabel Terkait:** `universitas`
* **Deliverables:**
  - [ ] `index.php`: Tampilan informasi profil perguruan tinggi.
  - [ ] `edit.php` & `process.php`: Form perbaikan data kampus (nama universitas, slogan, alamat, kota, provinsi, kode pos, email, telepon, dan website).
  - [ ] Integrasi pencatatan `audit_log` untuk aksi `UPDATE` data universitas.

### 1.2. Modul Kelola Fakultas (`admin/fakultas/`)
* **Akses Role:** Admin (Role 1)
* **Tabel Terkait:** `fakultas`, `universitas`
* **Deliverables:**
  - [ ] `index.php`: Tabel daftar fakultas dilengkapi jumlah program studi di dalamnya.
  - [ ] `create.php` & `edit.php`: Form penambahan dan pengubahan data fakultas (`kode_fakultas`, `nama_fakultas`).
  - [ ] `process.php`: Handler penambahan, pembaruan, dan penghapusan fakultas dengan validasi kunci unik `uk_fakultas_univ_kode`.

### 1.3. Modul Kelola Program Studi (`admin/program_studi/`)
* **Akses Role:** Admin (Role 1)
* **Tabel Terkait:** `program_studi`, `fakultas`
* **Deliverables:**
  - [ ] `index.php`: Tabel daftar program studi dengan filter berdasarkan fakultas.
  - [ ] `create.php` & `edit.php`: Form pendaftaran dan pengubahan prodi (`kode_program_studi`, `nama_program_studi`, `jenjang`, `status_aktif`).
  - [ ] `process.php`: Handler transaksi data prodi beserta validasi keunikan kombinasi `id_fakultas` & `kode_program_studi`.

---

## FASE 2: Modul Pengelolaan Pengguna & Hak Akses (User Management)

### 2.1. Modul Manajemen User (`admin/pengguna/`)
* **Akses Role:** Admin (Role 1)
* **Tabel Terkait:** `pengguna`, `roles`, `universitas`, `fakultas`, `program_studi`
* **Deliverables:**
  - [ ] `index.php`: Tabel daftar seluruh pengguna aplikasi dengan filter berdasarkan Role, Fakultas, dan Prodi.
  - [ ] `create.php`: Form tambah user manual (Operator Prodi, Dekanat, Rektorat, Admin, Mahasiswa).
  - [ ] `edit.php`: Form ubah role, penetapan wilayah kewenangan (id_fakultas / id_program_studi), serta ubah status aktif/tidak aktif.
  - [ ] `reset-password.php`: Fitur reset password user oleh admin menggunakan `password_hash()`.
  - [ ] `process.php`: Handler simpan data user dan pencatatan audit log `INSERT`/`UPDATE`/`DELETE`.

### 2.2. Modul Pengaturan Akun Pribadi (`admin/profil/`)
* **Akses Role:** Semua Role (1, 2, 3, 4, 5)
* **Tabel Terkait:** `pengguna`
* **Deliverables:**
  - [ ] `index.php`: Form ubah informasi pribadi (Nama Lengkap, Email, No HP).
  - [ ] `ganti-password.php`: Form ubah password sendiri (wajib memasukkan password lama, password baru, dan konfirmasi password baru).

---

## FASE 3: Modul Portal Mahasiswa (Self-Service)

### 3.1. Halaman Profil Mahasiswa (`admin/profil-saya/`)
* **Akses Role:** Mahasiswa (Role 5)
* **Tabel / View Terkait:** View `v_profil_mahasiswa`, tabel `mahasiswa`
* **Deliverables:**
  - [ ] `index.php`: Tampilan kartu identitas/profil mahasiswa lengkap (NPM, Nama, TTL, Jenis Kelamin, Tanggal Masuk, Prodi, Fakultas, Universitas, dan Status Akademik).
  - [ ] `edit.php` & `process.php`: Form pembaruan mandiri data kontak/alamat oleh mahasiswa (dengan pembatasan tidak dapat mengubah NPM, Prodi, atau Status Akademik).

---

## FASE 4: Modul Monitoring & Laporan (Executive View)

### 4.1. Modul Audit Log System (`admin/audit_log/`)
* **Akses Role:** Admin (Role 1)
* **Tabel Terkait:** `audit_log`, `pengguna`
* **Deliverables:**
  - [ ] `index.php`: Tabel riwayat aktivitas sistem.
  - [ ] Fitur Filter: Berdasarkan rentang tanggal (`waktu`), jenis aksi (`LOGIN`, `LOGOUT`, `INSERT`, `UPDATE`, `DELETE`), dan nama pengguna.
  - [ ] `detail.php` / Modal Detail: Viewer untuk membandingkan isi kolom `data_lama` dan `data_baru` (JSON format).

### 4.2. Modul Laporan Data Mahasiswa (`admin/laporan/`)
* **Akses Role:** Dekanat (Role 3), Rektorat (Role 4), Operator Prodi (Role 2)
* **Tabel / View Terkait:** View `v_mahasiswa_per_prodi`
* **Deliverables:**
  - [ ] `index.php`: Halaman rekapitulasi data mahasiswa berbasis grafik & tabel ringkasan (Jumlah Mahasiswa per Status: Aktif, Cuti, Lulus, DO).
  - [ ] `export-excel.php`: Skrip eksport daftar mahasiswa terfilter ke format Spreadsheet/Excel (`.csv` / `.xlsx`).
  - [ ] `cetak-pdf.php`: Layout cetak laporan versi PDF / Print-friendly.

---

## FASE 5: Pengamanan, Optimasi & Deployment

### 5.1. Hardening Keamanan
- [ ] Proteksi folder `uploads/` dengan file `.htaccess` agar berkas yang diunggah tidak dapat dieksekusi sebagai script PHP.
- [ ] Implementasi token CSRF (`$_SESSION['csrf_token']`) pada seluruh form penambahan, pengubahan, dan penghapusan data.
- [ ] Validasi tipe dan ukuran file upload (misal: foto profil maksimal 2MB, ekstensi `.jpg`, `.jpeg`, `.png`).

### 5.2. Testing & Quality Assurance
- [ ] Testing Hak Akses (RBAC): Memastikan user dengan Role 5 (Mahasiswa) atau Role 2 (Operator) tidak bisa membobol URL halaman milik Admin (Role 1).
- [ ] Testing Integritas Database: Memastikan transaksi `PDO Transaction` berfungsi saat terjadi pembatalan (*rollback*) ketika pembuatan akun user gagal.
- [ ] Checking SQL Injection & XSS: Memastikan seluruh input menggunakan *Prepared Statements* dan seluruh variabel output dilapisi `htmlspecialchars()`.
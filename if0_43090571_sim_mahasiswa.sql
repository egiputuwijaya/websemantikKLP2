-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql208.infinityfree.com
-- Generation Time: Oct 05, 2026 at 02:06 AM
-- Server version: 11.4.13-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_43090571_sim_mahasiswa`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id_audit` bigint(20) UNSIGNED NOT NULL,
  `id_pengguna` bigint(20) UNSIGNED DEFAULT NULL,
  `tabel_nama` varchar(100) NOT NULL,
  `record_id` varchar(100) DEFAULT NULL,
  `aksi` enum('LOGIN','LOGOUT','INSERT','UPDATE','DELETE','VIEW') NOT NULL,
  `data_lama` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`id_audit`, `id_pengguna`, `tabel_nama`, `record_id`, `aksi`, `data_lama`, `data_baru`, `ip_address`, `user_agent`, `waktu`) VALUES
(1, 6, 'pengguna', '6', 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-28 10:01:52'),
(0, 5, 'pengguna', '5', 'LOGIN', NULL, '{\"hasil\":\"Gagal: password salah\",\"username\":\"20260001\"}', '114.12.31.110', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-05 12:51:50'),
(0, 6, 'pengguna', '6', 'LOGIN', NULL, '{\"username\":\"Admin\",\"nama_lengkap\":\"Admin SIM Mhs UMB\",\"id_role\":1,\"kode_role\":\"ADMIN\"}', '114.12.31.110', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-05 12:52:00'),
(0, 6, 'pengguna', '6', 'LOGOUT', '{\"username\":\"Admin\",\"nama_lengkap\":\"Admin SIM Mhs UMB\",\"id_role\":1,\"last_login\":\"2026-10-05 12:52:00\"}', NULL, '114.12.31.110', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-05 12:52:29'),
(0, 4, 'pengguna', '4', 'LOGIN', NULL, '{\"hasil\":\"Gagal: password salah\",\"username\":\"rektorat\"}', '114.12.31.110', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-05 12:53:01'),
(0, 5, 'pengguna', '5', 'LOGIN', NULL, '{\"username\":\"20260001\",\"nama_lengkap\":\"Contoh Mahasiswa\",\"id_role\":5,\"kode_role\":\"MAHASISWA\"}', '114.12.31.110', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-05 12:55:12'),
(0, 5, 'pengguna', '5', 'LOGOUT', '{\"username\":\"20260001\",\"nama_lengkap\":\"Contoh Mahasiswa\",\"id_role\":5,\"last_login\":\"2026-10-05 12:55:12\"}', NULL, '114.12.31.110', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-05 12:57:48');

-- --------------------------------------------------------

--
-- Table structure for table `fakultas`
--

CREATE TABLE `fakultas` (
  `id_fakultas` bigint(20) UNSIGNED NOT NULL,
  `id_universitas` bigint(20) UNSIGNED NOT NULL,
  `kode_fakultas` varchar(20) NOT NULL,
  `nama_fakultas` varchar(200) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fakultas`
--

INSERT INTO `fakultas` (`id_fakultas`, `id_universitas`, `kode_fakultas`, `nama_fakultas`, `created_at`, `updated_at`) VALUES
(1, 1, 'FT', 'Fakultas Teknik', '2026-09-28 09:53:26', '2026-09-28 09:53:26');

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `id_mahasiswa` bigint(20) UNSIGNED NOT NULL,
  `id_pengguna` bigint(20) UNSIGNED DEFAULT NULL,
  `id_program_studi` bigint(20) UNSIGNED NOT NULL,
  `npm` varchar(30) NOT NULL,
  `nama_mahasiswa` varchar(200) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `tanggal_masuk` date NOT NULL,
  `alamat` text DEFAULT NULL,
  `status_mahasiswa` enum('Aktif','Cuti','Lulus','Mengundurkan Diri','Drop Out','Tidak Aktif') NOT NULL DEFAULT 'Aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`id_mahasiswa`, `id_pengguna`, `id_program_studi`, `npm`, `nama_mahasiswa`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `tanggal_masuk`, `alamat`, `status_mahasiswa`, `created_at`, `updated_at`) VALUES
(1, 5, 1, '20260001', 'Contoh Mahasiswa', 'L', 'Bengkulu', '2005-01-15', '2026-09-01', 'Bengkulu', 'Aktif', '2026-09-28 09:53:27', '2026-09-28 09:53:27');

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id_pengguna` bigint(20) UNSIGNED NOT NULL,
  `id_role` smallint(5) UNSIGNED NOT NULL,
  `id_universitas` bigint(20) UNSIGNED DEFAULT NULL,
  `id_fakultas` bigint(20) UNSIGNED DEFAULT NULL,
  `id_program_studi` bigint(20) UNSIGNED DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nama_lengkap` varchar(200) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `no_hp` varchar(30) DEFAULT NULL,
  `status_aktif` enum('Aktif','Tidak Aktif') NOT NULL DEFAULT 'Aktif',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id_pengguna`, `id_role`, `id_universitas`, `id_fakultas`, `id_program_studi`, `username`, `password_hash`, `nama_lengkap`, `email`, `no_hp`, `status_aktif`, `last_login`, `created_at`, `updated_at`) VALUES
(2, 2, 1, 1, 1, 'operator.ti', 'y$pgI38nODeVOD/Q6V6rbSbeMS20hBX.cSbvGF0F7bAvMcP6ttpBClO', 'Operator Program Studi Teknik Informatika', 'operator.ti@umb.ac.id', NULL, 'Aktif', NULL, '2026-09-28 09:53:26', '2026-09-28 09:53:26'),
(3, 3, 1, 1, NULL, 'dekanat.ft', 'y$KnpUIRRPiP1v1eJG0WYG8eiAUNy8ZGyniFTEPRGXmozOVqMMrvd.a', 'Operator Dekanat Fakultas Teknik', 'dekanat.ft@umb.ac.id', NULL, 'Aktif', NULL, '2026-09-28 09:53:26', '2026-09-28 09:53:26'),
(4, 4, 1, NULL, NULL, 'rektorat', '$2y$10$abcdefghijklmnopqrstuuNvtz7CXQ0oewL44dsLFnC6ihkNxlU86', 'Operator Rektorat', 'rektorat@umb.ac.id', NULL, 'Aktif', NULL, '2026-09-28 09:53:27', '2026-10-05 05:53:52'),
(5, 5, 1, 1, 1, '20260001', '$2y$12$r7l71mS1h0AvqhR2i.gojuN8uNMl6fii5YSrzO/co2b9RIEdxNAqG', 'Contoh Mahasiswa', '20260001@student.umb.ac.id', NULL, 'Aktif', '2026-10-04 22:55:13', '2026-09-28 09:53:27', '2026-10-05 05:55:13'),
(6, 1, 1, NULL, NULL, 'Admin', '$2y$12$5i2Cwv4w7B2oFoxwHpP.YumZE7IhsAOMLefHia7dj9dul5Sxy1hmm', 'Admin SIM Mhs UMB', 'harrywitriyono@umb.ac.id', NULL, 'Aktif', '2026-10-04 22:52:01', '2026-09-28 10:01:42', '2026-10-05 05:52:01');

-- --------------------------------------------------------

--
-- Table structure for table `program_studi`
--

CREATE TABLE `program_studi` (
  `id_program_studi` bigint(20) UNSIGNED NOT NULL,
  `id_fakultas` bigint(20) UNSIGNED NOT NULL,
  `kode_program_studi` varchar(20) NOT NULL,
  `nama_program_studi` varchar(200) NOT NULL,
  `jenjang` varchar(20) NOT NULL DEFAULT 'S1',
  `status_aktif` enum('Aktif','Tidak Aktif') NOT NULL DEFAULT 'Aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `program_studi`
--

INSERT INTO `program_studi` (`id_program_studi`, `id_fakultas`, `kode_program_studi`, `nama_program_studi`, `jenjang`, `status_aktif`, `created_at`, `updated_at`) VALUES
(1, 1, 'TI', 'Teknik Informatika', 'S1', 'Aktif', '2026-09-28 09:53:26', '2026-09-28 09:53:26');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id_role` smallint(5) UNSIGNED NOT NULL,
  `kode_role` varchar(30) NOT NULL,
  `nama_role` varchar(100) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id_role`, `kode_role`, `nama_role`, `keterangan`, `created_at`) VALUES
(1, 'ADMIN', 'Admin', 'Mengelola seluruh data dan konfigurasi aplikasi', '2026-09-28 09:53:26'),
(2, 'OPERATOR_PRODI', 'Operator Program Studi', 'Mengelola mahasiswa pada Program Studi yang menjadi kewenangannya', '2026-09-28 09:53:26'),
(3, 'DEKANAT', 'Dekanat', 'Melihat data mahasiswa pada Fakultas yang menjadi kewenangannya', '2026-09-28 09:53:26'),
(4, 'REKTORAT', 'Rektorat', 'Melihat data mahasiswa pada tingkat universitas', '2026-09-28 09:53:26'),
(5, 'MAHASISWA', 'Mahasiswa', 'Melihat data pribadi/rekord mahasiswa sendiri', '2026-09-28 09:53:26'),
(6, '55201', 'Prodi Teknik Informatika', NULL, '2026-09-28 10:07:56');

-- --------------------------------------------------------

--
-- Table structure for table `universitas`
--

CREATE TABLE `universitas` (
  `id_universitas` bigint(20) UNSIGNED NOT NULL,
  `kode_universitas` varchar(20) NOT NULL,
  `nama_universitas` varchar(200) NOT NULL,
  `slogan` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `kota` varchar(100) DEFAULT NULL,
  `provinsi` varchar(100) DEFAULT NULL,
  `kode_pos` varchar(10) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telepon` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `universitas`
--

INSERT INTO `universitas` (`id_universitas`, `kode_universitas`, `nama_universitas`, `slogan`, `alamat`, `kota`, `provinsi`, `kode_pos`, `website`, `email`, `telepon`, `created_at`, `updated_at`) VALUES
(1, 'UMB', 'Universitas Muhammadiyah Bengkulu', NULL, 'Jl. Bali, Kampung Bali', 'Bengkulu', 'Bengkulu', NULL, NULL, NULL, NULL, '2026-09-28 09:53:26', '2026-09-28 09:53:26');

-- --------------------------------------------------------

--
-- Table structure for table `v_mahasiswa_per_prodi`
--

CREATE TABLE `v_mahasiswa_per_prodi` (
  `id_universitas` bigint(20) UNSIGNED DEFAULT NULL,
  `kode_universitas` varchar(20) DEFAULT NULL,
  `nama_universitas` varchar(200) DEFAULT NULL,
  `id_fakultas` bigint(20) UNSIGNED DEFAULT NULL,
  `kode_fakultas` varchar(20) DEFAULT NULL,
  `nama_fakultas` varchar(200) DEFAULT NULL,
  `id_program_studi` bigint(20) UNSIGNED DEFAULT NULL,
  `kode_program_studi` varchar(20) DEFAULT NULL,
  `nama_program_studi` varchar(200) DEFAULT NULL,
  `jenjang` varchar(20) DEFAULT NULL,
  `id_mahasiswa` bigint(20) UNSIGNED DEFAULT NULL,
  `npm` varchar(30) DEFAULT NULL,
  `nama_mahasiswa` varchar(200) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `jenis_kelamin_text` varchar(9) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `tanggal_masuk` date DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `status_mahasiswa` enum('Aktif','Cuti','Lulus','Mengundurkan Diri','Drop Out','Tidak Aktif') DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `v_profil_mahasiswa`
--

CREATE TABLE `v_profil_mahasiswa` (
  `id_mahasiswa` bigint(20) UNSIGNED DEFAULT NULL,
  `npm` varchar(30) DEFAULT NULL,
  `nama_mahasiswa` varchar(200) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `tanggal_masuk` date DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `status_mahasiswa` enum('Aktif','Cuti','Lulus','Mengundurkan Diri','Drop Out','Tidak Aktif') DEFAULT NULL,
  `kode_program_studi` varchar(20) DEFAULT NULL,
  `nama_program_studi` varchar(200) DEFAULT NULL,
  `jenjang` varchar(20) DEFAULT NULL,
  `kode_fakultas` varchar(20) DEFAULT NULL,
  `nama_fakultas` varchar(200) DEFAULT NULL,
  `kode_universitas` varchar(20) DEFAULT NULL,
  `nama_universitas` varchar(200) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

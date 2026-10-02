### 1. Tabel `universitas`
| id_universitas | kode_universitas | nama_universitas | slogan | alamat | kota | provinsi | kode_pos | website | email | telepon | created_at | updated_at |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | UMB | Universitas Muhammadiyah Bengkulu | *NULL* | Jl. Bali, Kampung Bali | Bengkulu | Bengkulu | *NULL* | *NULL* | *NULL* | *NULL* | 2026-09-28 02:53:26 | 2026-09-28 02:53:26 |

### 2. Tabel `fakultas`
| id_fakultas | id_universitas | kode_fakultas | nama_fakultas | created_at | updated_at |
| :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | 1 | FT | Fakultas Teknik | 2026-09-28 02:53:26 | 2026-09-28 02:53:26 |

### 3. Tabel `program_studi`
| id_program_studi | id_fakultas | kode_program_studi | nama_program_studi | jenjang | status_aktif | created_at | updated_at |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | 1 | TI | Teknik Informatika | S1 | Aktif | 2026-09-28 02:53:26 | 2026-09-28 02:53:26 |

### 4. Tabel `roles`
| id_role | kode_role | nama_role | keterangan | created_at |
| :--- | :--- | :--- | :--- | :--- |
| 1 | ADMIN | Admin | Mengelola seluruh data dan konfigurasi aplikasi | 2026-09-28 02:53:26 |
| 2 | OPERATOR_PRODI | Operator Program Studi | Mengelola mahasiswa pada Program Studi yang menjadi kewenangannya | 2026-09-28 02:53:26 |
| 3 | DEKANAT | Dekanat | Melihat data mahasiswa pada Fakultas yang menjadi kewenangannya | 2026-09-28 02:53:26 |
| 4 | REKTORAT | Rektorat | Melihat data mahasiswa pada tingkat universitas | 2026-09-28 02:53:26 |
| 5 | MAHASISWA | Mahasiswa | Melihat data pribadi/rekord mahasiswa sendiri | 2026-09-28 02:53:26 |
| 6 | 55201 | Prodi Teknik Informatika | *NULL* | 2026-09-28 03:07:56 |

### 5. Tabel `pengguna`
| id_pengguna | id_role | id_universitas | id_fakultas | id_program_studi | username | password_hash | nama_lengkap | email | no_hp | status_aktif | last_login |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 2 | 2 | 1 | 1 | 1 | operator.ti | $2y$10$REPLACE_WITH_PASSWORD_HASH | Operator Program Studi Teknik Informatika | operator.ti@umb.ac.id | *NULL* | Aktif | *NULL* |
| 3 | 3 | 1 | 1 | *NULL* | dekanat.ft | $2y$10$REPLACE_WITH_PASSWORD_HASH | Operator Dekanat Fakultas Teknik | dekanat.ft@umb.ac.id | *NULL* | Aktif | *NULL* |
| 4 | 4 | 1 | *NULL* | *NULL* | rektorat | $2y$10$REPLACE_WITH_PASSWORD_HASH | Operator Rektorat | rektorat@umb.ac.id | *NULL* | Aktif | *NULL* |
| 5 | 5 | 1 | 1 | 1 | 20260001 | $2y$10$REPLACE_WITH_PASSWORD_HASH | Contoh Mahasiswa | 20260001@student.umb.ac.id | *NULL* | Aktif | *NULL* |
| 6 | 1 | 1 | *NULL* | *NULL* | Admin | $2y$10$HDxCEJy3pjBzPhKOPCyO2ucvD/GObtbU2rx5nf9HgrZ5IleU3xwte | Admin SIM Mhs UMB | harrywitriyono@umb.ac.id | *NULL* | Aktif | 2026-09-28 10:01:52 |

### 6. Tabel `mahasiswa`
| id_mahasiswa | id_pengguna | id_program_studi | npm | nama_mahasiswa | jenis_kelamin | tempat_lahir | tanggal_lahir | tanggal_masuk | alamat | status_mahasiswa |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | 5 | 1 | 20260001 | Contoh Mahasiswa | L | Bengkulu | 2005-01-15 | 2026-09-01 | Bengkulu | Aktif |

### 7. Tabel `audit_log`
| id_audit | id_pengguna | tabel_nama | record_id | aksi | data_lama | data_baru | ip_address | user_agent | waktu |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | 6 | pengguna | 6 | LOGIN | *NULL* | *NULL* | ::1 | Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 | 2026-09-28 10:01:52 |
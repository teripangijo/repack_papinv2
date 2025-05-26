# REPACK PAPIN - Aplikasi Manajemen Returnable Package & Kuota Impor

## Deskripsi Singkat

REPACK PAPIN adalah aplikasi web yang dirancang untuk memfasilitasi proses manajemen *returnable package* (kemasan yang dapat digunakan kembali) dan administrasi terkait kuota impor. Aplikasi ini bertujuan untuk membantu perusahaan (Pengguna Jasa) dalam mengajukan permohonan impor kembali untuk barang-barang mereka yang menggunakan kemasan returnable, serta mengelola kuota yang mereka miliki berdasarkan Surat Keputusan (SKEP) yang berlaku.

Aplikasi ini juga menyediakan antarmuka untuk peran internal seperti Petugas Pemeriksa, Administrator Sistem, dan Tim Monitoring untuk memproses, memverifikasi, menyetujui, dan memantau seluruh alur kerja permohonan dan kuota.

## Fitur Utama

Aplikasi ini memiliki beberapa modul utama yang dikelola berdasarkan peran pengguna:

### 1. Pengguna Jasa (Perusahaan)
* **Registrasi dan Aktivasi Akun:** Pengguna baru dapat mendaftar dan harus melengkapi profil perusahaan untuk aktivasi.
* **Manajemen Profil:** Pengguna dapat mengedit profil pribadi dan detail perusahaan, termasuk mengunggah dokumen pendukung seperti TTD PIC dan SKEP Fasilitas.
* **Pengajuan Kuota Barang:** Mengajukan permohonan untuk penetapan atau penambahan kuota barang returnable berdasarkan SKEP yang dimiliki.
* **Daftar Pengajuan Kuota:** Melihat riwayat dan status pengajuan kuota yang telah dikirim.
* **Pembuatan Permohonan Impor Kembali:** Mengajukan permohonan untuk impor kembali barang returnable dengan memilih dari kuota yang telah disetujui dan melampirkan dokumen pendukung seperti BC 1.1/Manifest.
* **Daftar Permohonan Impor:** Melihat riwayat dan status permohonan impor kembali, termasuk detail pemeriksaan dan keputusan akhir.
* **Cetak Dokumen:** Mencetak bukti pengajuan kuota dan formulir permohonan impor.
* **Dashboard Pengguna:** Menampilkan ringkasan kuota (awal, sisa, terpakai) per jenis barang dan histori permohonan terbaru.

### 2. Petugas Pemeriksa
* **Dashboard Petugas:** Menampilkan ringkasan tugas pemeriksaan yang ditugaskan.
* **Daftar Tugas Pemeriksaan:** Melihat daftar permohonan impor yang telah ditugaskan untuk diperiksa.
* **Perekaman Laporan Hasil Pemeriksaan (LHP):** Mengisi detail LHP, termasuk jumlah barang yang disetujui/ditolak, catatan, dan mengunggah file LHP serta dokumentasi foto.
* **Riwayat LHP Direkam:** Melihat daftar LHP yang telah direkam oleh petugas.
* **Monitoring Permohonan (Lingkup Petugas):** Kemampuan untuk memantau alur permohonan yang terkait.
* **Edit Profil Petugas.**

### 3. Administrator Sistem
* **Dashboard Admin:** Menampilkan statistik umum sistem (total user, permohonan pending, dll.).
* **Manajemen Permohonan Impor:**
    * Melihat semua permohonan masuk.
    * Memproses permohonan: menunjuk petugas pemeriksa, mengunggah surat tugas.
    * Finalisasi permohonan (setelah LHP direkam): menyetujui atau menolak permohonan, mencatat nomor dan tanggal surat keputusan, mengunggah file surat keputusan (jika disetujui), dan mencatat alasan penolakan (jika ditolak).
    * Melihat detail lengkap permohonan, termasuk LHP dan histori proses.
* **Manajemen Pengajuan Kuota:**
    * Melihat semua pengajuan kuota dari pengguna jasa.
    * Memproses pengajuan kuota: menyetujui atau menolak, menentukan jumlah kuota yang disetujui, mencatat nomor dan tanggal SK, mengunggah file SK.
    * Melihat detail pengajuan kuota.
* **Monitoring Kuota Perusahaan:** Melihat ringkasan dan detail kuota (awal, sisa, terpakai) untuk setiap perusahaan dan setiap jenis barang yang terdaftar.
* **Manajemen User:** Menambah, melihat, mengedit, dan menghapus akun pengguna untuk semua peran (Pengguna Jasa, Petugas, Monitoring, Admin lain).
* **Manajemen Role & Akses:** Mengelola peran pengguna dan hak akses menu (jika diimplementasikan lebih lanjut).
* **Edit Profil Admin.**

### 4. Monitoring
* **Dashboard Monitoring:** Menampilkan statistik ringkasan dari seluruh proses di sistem.
* **Pantauan Pengajuan Kuota:** Melihat daftar semua pengajuan kuota dari seluruh perusahaan beserta status dan detailnya.
* **Pantauan Permohonan Impor:** Melihat daftar semua permohonan impor dari seluruh perusahaan beserta status, petugas yang ditunjuk, dan detailnya.
* **Pantauan Kuota Perusahaan:** Melihat agregat dan rincian kuota (awal, sisa, terpakai) per perusahaan dan per jenis barang.
* **Edit Profil Monitoring.**

## Alur Kerja Umum (Contoh untuk Permohonan Impor)

1.  **Pengguna Jasa:** Melengkapi profil -> Mengajukan kuota barang (jika belum ada) -> Menunggu persetujuan kuota dari Admin.
2.  **Admin:** Memproses pengajuan kuota -> Menyetujui/Menolak -> Mencatat SK Kuota.
3.  **Pengguna Jasa:** Jika kuota disetujui dan aktif -> Membuat Permohonan Impor Kembali untuk barang yang ada kuotanya -> Mengunggah dokumen pendukung (BC 1.1/Manifest).
4.  **Admin:** Menerima permohonan impor -> Menunjuk Petugas Pemeriksa -> Menerbitkan Surat Tugas.
5.  **Petugas Pemeriksa:** Menerima tugas -> Melakukan pemeriksaan fisik (jika perlu) -> Merekam Laporan Hasil Pemeriksaan (LHP) beserta file pendukung.
6.  **Admin:** Menerima notifikasi LHP sudah direkam -> Melakukan finalisasi permohonan -> Menerbitkan Surat Persetujuan Pengeluaran (jika disetujui) atau Surat Penolakan (jika ditolak) beserta catatan.
7.  **Pengguna Jasa & Monitoring:** Dapat melihat status terbaru dan hasil akhir permohonan.

## Teknologi yang Digunakan

* **Framework:** CodeIgniter 3.x
* **Bahasa Pemrograman:** PHP (versi 7.4+ direkomendasikan, dikembangkan/diuji pada PHP 8.0.28 & 8.3)
* **Database:** MySQL / MariaDB
* **Frontend:** HTML, CSS, JavaScript, jQuery, Bootstrap (berdasarkan template SB Admin 2)
* **Manajemen Dependensi PHP:** Composer (untuk pustaka seperti `phpdotenv`)
* **Server Web (Contoh Setup):** Apache2 di Ubuntu (XAMPP untuk development lokal)
* **Lainnya:**
    * `phpdotenv` untuk manajemen variabel lingkungan.
    * DataTables (JavaScript library) untuk tabel interaktif.
    * Gijgo Datepicker (atau datepicker lain) untuk input tanggal.

## Struktur Direktori Utama
repack_papin/
├── application/
│   ├── config/
│   ├── controllers/
│   │   ├── Admin.php
│   │   ├── Auth.php
│   │   ├── Monitoring.php
│   │   ├── Petugas.php
│   │   └── User.php
│   ├── helpers/
│   ├── libraries/
│   ├── models/
│   ├── views/
│   │   ├── admin/
│   │   ├── auth/
│   │   ├── monitoring/
│   │   ├── petugas/
│   │   ├── templates/
│   │   └── user/
│   └── ...
├── assets/
│   ├── css/
│   ├── img/
│   ├── js/
│   └── vendor/ (SB Admin 2 assets)
├── system/ (CodeIgniter core)
├── uploads/
│   ├── bc_manifest/
│   ├── dokumentasi_lhp/
│   ├── lampiran_kuota/
│   ├── lhp/
│   ├── profile_images/
│   ├── sk_kuota/
│   ├── skep_awal_user/
│   ├── skep_fasilitas/
│   ├── surat_tugas/
│   └── ttd/
├── vendor/ (Composer dependencies)
├── .env (File lingkungan, tidak di-commit)
├── .env.example (Contoh file lingkungan)
├── .gitignore
├── composer.json
├── composer.lock
└── index.php

## Instalasi dan Setup

1.  **Prasyarat:**
    * Server Web (Apache/Nginx)
    * PHP (versi sesuai `composer.json`, misalnya 7.4+ atau 8.0+)
    * Ekstensi PHP yang dibutuhkan: `mbstring`, `xml` (termasuk `dom`), `mysql` (atau `mysqli`/`pdo_mysql`), `intl` (jika menggunakan fitur lokalisasi CI).
    * Database Server (MySQL/MariaDB)
    * Composer
    * Git

2.  **Langkah-langkah:**
    * Clone repository ini
    * Masuk ke direktori proyek: `cd repack_papin`
    * Instal dependensi Composer: `composer install --no-dev`
    * Salin `.env.example` menjadi `.env`: `cp .env.example .env`
    * Konfigurasi file `.env` dengan detail database Anda (DB\_HOSTNAME, DB\_USERNAME, DB\_PASSWORD, DB\_DATABASE, DB\_PORT) dan `CI_ENV` (atur ke `development` untuk lokal, `production` untuk server).
    * Pastikan `base_url` di `application/config/config.php` sudah benar atau diatur melalui konstanta `BASE_URL` dari `.env` via `index.php`.
    * Impor struktur database. Buat database dan impor file `.sql` jika ada (atau jalankan migrasi jika menggunakan sistem migrasi CI).
    * Atur izin tulis untuk direktori yang dibutuhkan oleh CodeIgniter dan aplikasi:
        * `application/cache/`
        * `application/logs/`
        * `application/session/` (jika menggunakan driver sesi 'files')
        * Seluruh direktori `uploads/` dan subdirektorinya.
        Pastikan pengguna server web (misalnya `www-data` di Ubuntu) memiliki izin tulis ke direktori tersebut. Contoh:
        ```bash
        sudo chown -R www-data:www-data application/cache application/logs application/session uploads
        sudo chmod -R 775 application/cache application/logs application/session uploads
        ```
    * Konfigurasi Virtual Host di Apache atau Server Block di Nginx untuk mengarahkan domain/subdomain Anda ke direktori root proyek (`repack_papin`). Pastikan `AllowOverride All` (untuk Apache) aktif agar `.htaccess` berfungsi.
    * Pastikan `.htaccess` ada di root proyek untuk URL yang bersih.

3.  **Akun Awal:**
    * Informasi mengenai akun admin default atau cara membuat akun pertama (mungkin melalui seeder atau registrasi manual).

## Kontribusi (Opsional)

Jika Anda ingin berkontribusi pada proyek ini, silakan:
1.  Fork repository.
2.  Buat branch baru untuk fitur atau perbaikan Anda (`git checkout -b nama-fitur-anda`).
3.  Commit perubahan Anda (`git commit -am 'Menambahkan fitur X'`).
4.  Push ke branch (`git push origin nama-fitur-anda`).
5.  Buat Pull Request baru.

## Lisensi

Proyek ini menggunakan lisensi MIT. Lihat file `LICENSE` untuk detailnya.

---
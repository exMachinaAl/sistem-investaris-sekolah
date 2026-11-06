# Sistem Inventaris Sekolah (SIS) — Laravel 12 / MySQL / Laragon — v1.2

Aplikasi web bidang sarana dan prasarana sekolah. Dibuat dengan Laravel 12, Blade, CSS mandiri, Eloquent, dan MySQL/MariaDB dari Laragon. Tidak membutuhkan Node.js, React, NPM, Filament, maupun Vite.

> **Upgrade V1.2:** import CSV dua tahap untuk gedung, jurusan, prasarana, dan inventaris; template CSV per jenis data; ekspor CSV; serta pencatatan otomatis riwayat mutasi saat lokasi inventaris diubah melalui import. Fitur CSV ini tidak menambah tabel baru. Instalasi yang belum pernah memasang V1.1 tetap perlu menjalankan `php artisan migrate` setelah backup DB; **jangan** menjalankan `migrate:fresh` pada database aktif.
>
> **PENTING:** `php artisan migrate` diperlukan untuk kolom `inventaris.deleted_at`. Mengembalikan file kode tidak otomatis mengembalikan struktur database. Jika sudah menjalankan migrasi, ikuti petunjuk rollback database secara terpisah.
>
> **Status:** sumber kode Laravel lengkap dan dapat diinstal lewat Composer. Arsip **tidak** menyertakan `vendor/` karena paket Laravel harus diunduh menggunakan Composer di komputer Anda. Pengujian akhir menggunakan database lokal Anda tetap diperlukan.

## Preview tampilan sebelum instalasi

Buka file `PREVIEW-DASHBOARD.html` langsung di browser untuk melihat ilustrasi UI (hanya preview statis, bukan fitur aktif).

## Persyaratan

- Laragon (Apache/Nginx dan MySQL atau MariaDB aktif)
- PHP **8.2 atau lebih baru** dan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `tokenizer`, `ctype`, `xml` (umumnya tersedia dalam PHP Laragon)
- Composer
- Browser

## Cara pasang — Windows / Laragon

1. Ekstrak folder `sistem-inventaris-sekolah` dari ZIP ke `C:\laragon\www\sistem-inventaris-sekolah`.
2. Buka Laragon lalu jalankan **Start All**. Klik **Terminal** dan masuk ke folder:

   ```bash
   cd C:\laragon\www\sistem-inventaris-sekolah
   php -v
   composer install
   ```

   Jika `composer` tidak dikenal, instal Composer atau aktifkan lewat Laragon. Pastikan versi PHP >= 8.2.
3. Buat file `.env` dari `.env.example`:

   **CMD:** `copy .env.example .env`  
   **PowerShell:** `Copy-Item .env.example .env`

4. Buka `.env`, sesuaikan informasi MySQL Laragon:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sistem_inventaris_sekolah
   DB_USERNAME=root
   DB_PASSWORD=
   
   SEED_ADMIN_NAME="Admin Sarpras"
   SEED_ADMIN_EMAIL=admin@sekolah.test
   SEED_ADMIN_PASSWORD="GANTI-DENGAN-PASSWORD-KUAT-ANDA"
   ```

   `DB_PASSWORD` hanya boleh dikosongkan jika MySQL Anda memang memakai root tanpa password.
   **Jangan gunakan password contoh**. Gunakan password unik minimal 12 karakter. `SEED_ADMIN_PASSWORD` hanya digunakan saat melakukan seed akun awal.
5. Buat database **kosong** bernama `sistem_inventaris_sekolah` lewat HeidiSQL/phpMyAdmin/terminal. Anda juga dapat menjalankan berkas `BUAT_DATABASE.sql` yang disertakan. **Jika Anda sebelumnya sudah mengimpor `kerangka_database_sistem_inventaris.sql` dari percakapan sebelumnya ke database bernama sama, jangan gunakan database berisi tabel tersebut untuk migration Laravel.** Buat database kosong baru (mis. `sis_laravel`) dan ubah `DB_DATABASE` di `.env`; backup data lama jika ingin dipindahkan.

   ```sql
   CREATE DATABASE sistem_inventaris_sekolah CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

6. Pada terminal project jalankan secara berurutan:

   ```bash
   php artisan key:generate
   php artisan migrate
   php artisan db:seed
   php artisan serve
   ```

7. Buka **http://127.0.0.1:8000/login**, masuk dengan `SEED_ADMIN_EMAIL` dan `SEED_ADMIN_PASSWORD` yang Anda isi.

### Mode demo (opsional)

Setelah `php artisan db:seed`, untuk menambahkan **5 gedung dummy + 9 jurusan dummy + 12 entri barang dummy** jalankan:

```bash
php artisan db:seed --class=DemoSeeder
```

Seluruh data demo diberi label **Dummy**, dan dapat diedit setelah data nyata diperoleh. Seeder utama sudah memuat **148 entri prasarana** dari file `prasarana.xls` yang diberikan sebelumnya; gedung, lantai dan jurusan prasarana tersebut **tidak diasumsikan** dari nama ruang.

Untuk menghapus data demo, gunakan halaman master data dengan memperhatikan relasi yang sudah terpasang. Jangan gunakan `migrate:fresh` setelah melakukan input data yang ingin dipertahankan karena akan menghapus tabel.

### Mengakses lewat virtual host Laragon

Laragon dapat mengenali `sistem-inventaris-sekolah.test` jika fitur Auto Virtual Hosts aktif. Domain tersebut harus menunjuk **folder `public/`** Laravel, tidak boleh folder akar aplikasi. Cara paling sederhana tetap `php artisan serve` dan `http://127.0.0.1:8000`.

## Modul

| Halaman | Kemampuan |
|---|---|
| Dashboard | jumlah gedung, prasarana, jurusan, unit barang, belum dipetakan, kategori dan kondisi |
| Data Gedung | CRUD, jumlah lantai, ukuran dan luas tapak, status pengukuran, daftar prasarana di gedung |
| Data Jurusan | CRUD, daftar fasilitas pengguna, relasi ruangan dapat untuk banyak jurusan |
| Ruang & Prasarana | CRUD, gedung/lantai/ukuran, verifikasi, filter, daftar inventaris per ruang |
| Inventaris Barang | CRUD, unit, kondisi, tahun perolehan, lokasi |
| Detail Inventaris | mutasi **seluruh** unit pada entri inventaris dan catatan pemeliharaan |
| Dokumentasi Foto | foto ruang dan barang, tanggal, kategori bukti, jumlah terverifikasi, serta keterangan |
| FAQ & Petunjuk | pusat bantuan yang dapat dicari serta tombol bantuan kontekstual pada menu, form, filter, dan tindakan penting |
| Laporan | rekap per gedung dan jurusan, ekspor CSV ke Excel |
| Impor CSV | unduh template lalu tambah/perbarui gedung, jurusan, prasarana, atau inventaris berdasarkan kode; dilengkapi validasi, pratinjau, dan konfirmasi |
| Login/role | admin & petugas boleh ubah; pimpinan hanya lihat/unduh laporan |

### Catatan penting

- Kode internal gedung, jurusan, prasarana, dan inventaris dibuat otomatis ketika data ditambahkan melalui formulir. Kode yang disediakan melalui impor CSV tetap dipertahankan untuk kompatibilitas data lama dan integrasi.
- Ukuran gedung adalah ukuran tapak keseluruhan. Luas tapak dihitung dari panjang × lebar dan tidak dianggap sebagai luas total seluruh lantai.
- Tombol bantuan berbentuk tanda tanya dapat dibuka dengan klik/ketuk atau dengan mengarahkan kursor sebentar. Menu FAQ menyediakan pencarian panduan lengkap; tombol bantuan juga dapat dioperasikan dengan keyboard dan ditutup menggunakan tombol Esc.
- **Akun default tidak memiliki password bawaan.** Akun admin dibuat dari `.env` ketika menjalankan `php artisan db:seed`.
- Akun `petugas` / `pimpinan` dapat ditambahkan oleh pengembang melalui Eloquent/Tinker karena MVP ini belum menyediakan antarmuka pengelolaan user. Misalnya:

  ```bash
  php artisan tinker
  ```
  Lalu pada prompt interaktif:

  ```php
  \App\Models\User::create(['name'=>'Pimpinan','email'=>'pimpinan@sekolah.test','password'=>'GANTI_PASSWORD_UNIK','role'=>'pimpinan']);
  ```

  Nilai `password` otomatis di-hash oleh cast model User; gunakan password kuat dan unik.
- Pemeliharaan berupa histori yang dapat ditambah, belum memiliki jadwal pengingat.
- Foto dokumentasi disimpan secara privat di `storage/app/private/dokumentasi` dan hanya ditampilkan melalui aplikasi kepada pengguna yang sudah login. Format yang diterima JPG, PNG, dan WEBP maksimal 5 MB.
- Mutasi memindahkan satu **kelompok entri barang utuh**; belum mendukung mutasi sebagian jumlah. Untuk mutasi sebagian, pecah stok menjadi dua entri dengan kode berbeda terlebih dahulu.
- CSV dari Excel harus disimpan sebagai CSV UTF-8, bukan XLS/XLSX. Unduh template dari menu **Impor CSV** terlebih dahulu dan hapus baris contoh sebelum mengisi data. File sumber `prasarana.xls` telah diekstrak ke `database/data/prasarana.json` agar tidak memerlukan reader file `.xls` dari pihak ketiga.
- Untuk laporan ke Excel, unduh CSV dari menu Laporan. Data yang berpotensi diproses sebagai rumus ditandai aman dalam ekspor.
- `status_verifikasi=belum` **bukan** berarti prasarana tidak ada, melainkan datanya belum diverifikasi.
- Tidak ada hubungan otomatis RPS ke jurusan tertentu karena belum ada penetapan resmi.
- Laravel harus dilayani melalui folder `public` untuk keamanan; hindari server mengakses `.env`.
- Ubah `APP_ENV=production` dan `APP_DEBUG=false` untuk deployment, gunakan HTTPS, akun berbeda, backup database, dan pembatasan akses server.

## Struktur inti

```text
app/Http/Controllers/    — dashboard, CRUD, import, laporan, login
app/Models/               — Eloquent model dan relasi
bootstrap/app.php         — bootstrapping + middleware editor
database/migrations/      — struktur database MySQL
database/seeders/         — admin + prasarana nyata, data demo opsional
database/data/            — ekstrak 148 entri dari lampiran
resources/views/          — halaman Blade untuk semua modul
public/assets/app.css    — styling mandiri (tanpa build)
public/templates/         — template import CSV
routes/web.php            — seluruh endpoint
```

## Alur uji manual setelah instalasi

1. Masuk menggunakan akun admin → tampil dashboard dengan 148 prasarana.
2. Tambahkan gedung; buat/ubah jurusan; edit salah satu prasarana dan hubungkan ke gedung dan jurusan.
3. Tambahkan inventaris ke prasarana. Buka halaman detail inventaris, buat catatan perbaikan dan lakukan mutasi ke lokasi lain.
4. Cari/filter prasarana, lalu ekspor CSV dari Laporan.
5. Simpan file template CSV dari Excel dan coba impor contoh data, lalu cari kode barunya.
6. Buat akun `pimpinan` melalui Tinker, login, pastikan opsi edit/tambah tidak muncul dan URL tulis mengembalikan status 403.

## Masalah yang sering terjadi

- **`could not find driver`** — aktifkan `pdo_mysql` dari konfigurasi PHP Laragon.
- **`Access denied for user 'root'`** — koreksi `DB_USERNAME`/`DB_PASSWORD` di `.env`, lalu `php artisan config:clear`.
- **`Unknown database`** — buat database dahulu lewat HeidiSQL.
- **`No application encryption key`** — jalankan `php artisan key:generate`.
- **`vendor/autoload.php missing`** — jalankan `composer install` pada folder project, bukan di folder `public`.
- **`Class ... not found`** — jalankan `composer dump-autoload`.
- **Session/cache error** — pastikan `storage/framework/sessions`, `storage/framework/cache/data`, `storage/framework/views`, `storage/logs` dapat ditulis.
- **CSS tidak tampil** — buka melalui `php artisan serve` atau virtual host yang diarahkan ke `public/`; jangan buka file Blade langsung.
- **Buka di Laragon virtual host** — jika 404, lebih sederhana uji dulu melalui `http://127.0.0.1:8000`.

## Keamanan dan kebijakan data

Ini adalah MVP lokal untuk pengujian dengan pelanggan bidang Sarpras. Uji dengan data sekolah yang diizinkan untuk diproses. Lakukan backup sebelum migrasi/import di sistem aktif. Catat dan review perubahan saat diuji; laporan demo tidak untuk keputusan inventaris resmi.

## Tes otomatis (opsional, setelah `composer install`)

Bila ekstensi `pdo_sqlite` aktif di PHP Laragon, jalankan:

```bash
php artisan test
```

Suite tes fitur mencakup akses, CRUD utama, kode otomatis, ukuran gedung, validasi lantai, dokumentasi foto, arsip, serta impor/ekspor CSV. Ini **belum** menggantikan pengujian langsung Laravel dan MySQL pada Laragon Anda.
# sistem-investaris-sekolah

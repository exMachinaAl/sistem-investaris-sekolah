@extends('layouts.app')
@section('title','FAQ & Petunjuk')
@section('content')
@php($previousUrl = url()->previous() === url()->current() ? route('dashboard') : url()->previous())
<div class="heading-row">
    <div><div class="kicker">PUSAT BANTUAN</div><h1>FAQ & Petunjuk Penggunaan</h1><p>Cari jawaban singkat atau buka petunjuk sesuai pekerjaan yang sedang dilakukan.</p></div>
    <a class="btn" href="{{ $previousUrl }}">← Kembali</a>
</div>

<div class="help-intro">
    <div><span class="help-demo">?</span></div>
    <div><h2>Tombol bantuan tersedia di seluruh aplikasi</h2><p>Arahkan kursor selama beberapa saat atau klik tombol tanda tanya. Pada ponsel, cukup ketuk tombolnya. Tekan <span class="code">Esc</span> atau klik area kosong untuk menutup petunjuk.</p></div>
</div>

<div class="filter-card faq-search-card">
    <div class="field"><div class="field-label-row"><label for="faq-search">Cari petunjuk</label><x-help-tip text="Ketik kata kunci; daftar pertanyaan akan disaring langsung tanpa memuat ulang halaman." /></div><input id="faq-search" class="control" type="search" data-faq-search placeholder="Contoh: mutasi barang, unggah foto, impor CSV..."></div>
</div>

<div class="faq-grid">
    <details class="faq-item" data-faq-item open><summary>Bagaimana alur data aplikasi ini?</summary><div>Mulai dari <b>gedung</b>, lalu catat <b>ruang/prasarana</b> di dalamnya. Hubungkan ruang ke jurusan bila diperlukan, kemudian tempatkan <b>inventaris</b> pada ruang tersebut.</div></details>
    <details class="faq-item" data-faq-item><summary>Apakah kode harus dibuat manual?</summary><div>Tidak. Data yang dibuat melalui formulir memperoleh kode otomatis seperti <span class="code">GDG-000001</span> dan <span class="code">INV-000001</span>. Kode pada impor CSV tetap digunakan agar data eksternal dapat dicocokkan.</div></details>
    <details class="faq-item" data-faq-item><summary>Apa arti status verifikasi?</summary><div><b>Belum</b> berarti belum diperiksa, <b>terverifikasi</b> berarti sudah sesuai bukti atau survei, sedangkan <b>perlu koreksi</b> berarti ditemukan data yang harus diperbaiki.</div></details>
    <details class="faq-item" data-faq-item><summary>Bagaimana mencatat ukuran gedung dan ruang?</summary><div>Masukkan panjang dan lebar dalam meter. Keduanya harus diisi bersama. Luas dihitung otomatis. Ukuran gedung adalah luas tapak, bukan penjumlahan luas seluruh lantai.</div></details>
    <details class="faq-item" data-faq-item><summary>Bagaimana menentukan gedung dan lantai ruang?</summary><div>Pilih gedung terlebih dahulu, lalu isi lantai. Nomor lantai ruang tidak boleh melebihi jumlah lantai gedung. Lapangan atau fasilitas luar dapat dibiarkan tanpa gedung dan lantai.</div></details>
    <details class="faq-item" data-faq-item><summary>Bagaimana memindahkan inventaris?</summary><div>Gunakan bagian <b>Mutasi Lokasi Barang</b> pada detail inventaris. Mutasi memindahkan seluruh unit dalam satu entri dan menyimpan lokasi asal, tujuan, tanggal, serta petugas.</div></details>
    <details class="faq-item" data-faq-item><summary>Bagaimana jika hanya sebagian unit yang dipindahkan?</summary><div>Pisahkan stok menjadi dua entri inventaris terlebih dahulu. Setelah itu, mutasikan entri yang mewakili jumlah unit yang dipindahkan.</div></details>
    <details class="faq-item" data-faq-item><summary>Apa perbedaan arsip dan hapus?</summary><div>Inventaris yang diarsipkan tidak tampil sebagai barang aktif, tetapi histori mutasi dan pemeliharaannya tetap tersimpan. Pemulihan arsip hanya dapat dilakukan admin.</div></details>
    <details class="faq-item" data-faq-item><summary>Bagaimana mengunggah dokumentasi foto?</summary><div>Buka detail ruang atau inventaris, pilih jenis bukti, isi tanggal, lalu unggah JPG, PNG, atau WEBP maksimal 5 MB. Foto jumlah barang harus menyertakan jumlah yang benar-benar terlihat atau dihitung.</div></details>
    <details class="faq-item" data-faq-item><summary>Bagaimana menggunakan impor CSV?</summary><div>Unduh template dari menu Impor CSV, hapus baris contoh, isi tanpa mengganti nama kolom, lalu unggah. Periksa pratinjau sebelum konfirmasi karena data baru ditulis setelah persetujuan.</div></details>
    <details class="faq-item" data-faq-item><summary>Siapa yang dapat mengubah data?</summary><div>Admin dan petugas dapat mengubah data operasional. Pimpinan memiliki akses baca dan laporan. Impor CSV serta pemulihan arsip dibatasi untuk admin.</div></details>
    <details class="faq-item" data-faq-item><summary>Apa yang harus dilakukan jika data masih diragukan?</summary><div>Jangan menebak. Tandai sebagai data dummy atau belum diverifikasi, tambahkan keterangan, dan unggah bukti setelah survei lapangan dilakukan.</div></details>
</div>
<div class="empty" data-faq-empty hidden>Tidak ada petunjuk yang cocok. Coba gunakan kata kunci yang lebih singkat.</div>
@endsection

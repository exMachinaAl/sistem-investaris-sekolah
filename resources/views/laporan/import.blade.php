@extends('layouts.app')
@section('title','Impor CSV')
@section('content')
@php($activeType = isset($jenis) ? $importTypes[$jenis] : null)
<div class="heading-row">
    <div>
        <div class="kicker">PENGELOLAAN DATA • KHUSUS ADMIN</div>
        <div class="section-title-help"><h1>{{ $activeType ? 'Pratinjau Impor '.$activeType['label'] : 'Impor Data dari CSV' }}</h1><x-help-tip text="Impor menggunakan dua tahap: validasi dan pratinjau terlebih dahulu, baru penyimpanan setelah admin mengonfirmasi." /></div>
        <p>Unduh template sesuai jenis data, isi tanpa mengubah nama kolom, lalu unggah untuk diperiksa.</p>
    </div>
    <div class="heading-actions">
        <a href="{{ route('laporan.index') }}" class="btn">Ekspor CSV</a>
        @if($activeType)<a href="{{ route('import.index') }}" class="btn">← Pilih Data Lain</a>@endif
    </div>
</div>

@if(isset($token))
<div class="info-banner" style="margin-bottom:16px">
    <strong>Belum ada data yang diubah.</strong> Pratinjau berlaku 30 menit dan hanya untuk akun admin yang mengunggahnya.
    Jika data berubah dari tempat lain sebelum konfirmasi, impor akan dibatalkan.
</div>
<div class="kpi-grid" style="margin-bottom:16px">
    <div class="kpi"><span class="label">Baru</span><div class="stat-number">{{ $stats['baru'] }}</div></div>
    <div class="kpi"><span class="label">Diperbarui</span><div class="stat-number">{{ $stats['ubah'] }}</div></div>
    <div class="kpi"><span class="label">Tanpa Perubahan</span><div class="stat-number">{{ $stats['sama'] }}</div></div>
</div>
<div class="section-card">
    <div class="card-header">
        <div class="section-title-help"><h2>Pratinjau {{ $activeType['label'] }}</h2><x-help-tip text="Periksa status Tambah, Perbarui, atau Tidak berubah. Maksimal 50 baris ditampilkan, tetapi seluruh file tetap divalidasi." /></div>
        <span class="small muted">Maksimal 50 baris pertama ditampilkan</span>
    </div>
    <div class="table-scroll"><table>
        <thead><tr><th>Kode</th><th>Nama</th><th>Rencana</th></tr></thead>
        <tbody>@foreach($preview as $item)<tr>
            <td><span class="code">{{ $item['kode'] }}</span></td>
            <td>{{ $item['nama'] }}</td>
            <td><span class="badge {{ $item['status']==='baru'?'green':($item['status']==='ubah'?'yellow':'') }}">{{ ['baru'=>'Tambah','ubah'=>'Perbarui','sama'=>'Tidak berubah'][$item['status']] }}</span></td>
        </tr>@endforeach</tbody>
    </table></div>
    <div class="card-body">
        <form method="POST" action="{{ route('import.konfirmasi') }}">@csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="field-label-row"><label><input type="checkbox" name="setuju" value="1" required> Saya sudah memeriksa ringkasan dan menyetujui penambahan/perubahan data.</label><x-help-tip text="Centang hanya setelah kode, nama, dan jumlah perubahan pada pratinjau sudah diperiksa." /></div>
            <div style="margin-top:16px;display:flex;gap:12px;flex-wrap:wrap">
                <span class="action-help"><button class="btn btn-primary">Konfirmasi &amp; Simpan Perubahan</button><x-help-tip text="Tindakan ini baru menulis seluruh perubahan yang lolos validasi ke database secara atomik." /></span>
                <a href="{{ route('import.index') }}" class="btn">Batal, jangan simpan</a>
            </div>
        </form>
    </div>
</div>
@else
<div class="info-banner" style="margin-bottom:20px">
    <strong>Mulai dari template.</strong> Setiap template berisi nama kolom yang benar dan satu baris contoh.
    Hapus baris contoh tersebut sebelum mengisi data sekolah.
</div>
<div class="two-panel">
@foreach($importTypes as $key=>$type)
    <div class="card">
        <div class="card-header" style="padding:0 0 16px">
            <div><div class="section-title-help"><h2 style="margin-bottom:5px">{{ $type['label'] }}</h2><x-help-tip text="Gunakan template khusus jenis data ini. Jangan mengganti nama kolom pada baris pertama." /></div><p style="margin:0">{{ $type['description'] }}</p></div>
        </div>
        <p class="small"><b>Kolom wajib:</b> {{ implode(', ', $type['required']) }}</p>
        <p class="small muted" style="word-break:break-word"><b>Semua kolom:</b> {{ implode(', ', $type['headers']) }}</p>
        <a class="btn btn-light" href="{{ route('import.template',$key) }}">⇩ Unduh Template {{ $type['label'] }}</a>
        <form method="POST" action="{{ route('import.'.$key) }}" enctype="multipart/form-data">@csrf
            <div class="field" style="margin-top:18px">
                <div class="field-label-row"><label for="file-{{ $key }}">Unggah CSV {{ $type['label'] }} *</label><x-help-tip text="Pilih CSV UTF-8 dari template {{ $type['label'] }}. File jenis lain atau header yang tidak dikenal akan ditolak." /></div>
                <input id="file-{{ $key }}" class="control" type="file" name="file" accept=".csv,.txt,text/csv" required>
                <span class="hint">CSV UTF-8, maksimal 4 MB dan 1.000 baris.</span>
            </div>
            <div class="action-help" style="margin-top:16px"><button type="submit" class="btn btn-primary">Periksa &amp; Pratinjau →</button><x-help-tip text="Tahap ini hanya memeriksa file dan belum mengubah database." /></div>
        </form>
    </div>
@endforeach
</div>
<div class="section-card" style="margin-top:20px">
    <div class="card-header"><div class="section-title-help"><h2>Aturan Penting</h2><x-help-tip text="Baca aturan nilai pilihan dan referensi sebelum mengisi banyak baris agar impor tidak gagal." /></div></div>
    <div class="card-body">
        <p>Kode yang belum ada akan ditambahkan; kode yang sudah ada akan diperbarui hanya pada kolom yang disertakan. Tidak ada perubahan sebelum tombol konfirmasi ditekan.</p>
        <p><b>Nilai pilihan:</b> kondisi inventaris = <span class="code">baik</span>, <span class="code">rusak_ringan</span>, <span class="code">rusak_berat</span>, atau <span class="code">hilang</span>. Status prasarana = <span class="code">belum</span>, <span class="code">terverifikasi</span>, atau <span class="code">perlu_koreksi</span>.</p>
        <p><b>Referensi lokasi:</b> gunakan kode gedung pada <span class="code">gedung_kode</span> dan kode prasarana pada <span class="code">lokasi_kode</span>. Kode referensi harus sudah tersimpan. Nilai <span class="code">is_dummy</span> menggunakan 0/1, ya/tidak, atau true/false.</p>
        <p style="margin-bottom:0"><b>Excel:</b> simpan kembali sebagai <i>CSV UTF-8</i>. Pemisah titik koma maupun koma dapat dibaca otomatis.</p>
    </div>
</div>
@endif
@endsection

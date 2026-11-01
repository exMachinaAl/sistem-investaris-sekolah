@extends('layouts.app')
@section('title','Impor CSV')
@section('content')
@php($activeType = isset($jenis) ? $importTypes[$jenis] : null)
<div class="heading-row">
    <div>
        <div class="kicker">PENGELOLAAN DATA • KHUSUS ADMIN</div>
        <h1>{{ $activeType ? 'Pratinjau Impor '.$activeType['label'] : 'Impor Data dari CSV' }}</h1>
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
        <h2>Pratinjau {{ $activeType['label'] }}</h2>
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
            <label><input type="checkbox" name="setuju" value="1" required> Saya sudah memeriksa ringkasan dan menyetujui penambahan/perubahan data.</label>
            <div style="margin-top:16px;display:flex;gap:12px;flex-wrap:wrap">
                <button class="btn btn-primary">Konfirmasi &amp; Simpan Perubahan</button>
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
            <div><h2 style="margin-bottom:5px">{{ $type['label'] }}</h2><p style="margin:0">{{ $type['description'] }}</p></div>
        </div>
        <p class="small"><b>Kolom wajib:</b> {{ implode(', ', $type['required']) }}</p>
        <p class="small muted" style="word-break:break-word"><b>Semua kolom:</b> {{ implode(', ', $type['headers']) }}</p>
        <a class="btn btn-light" href="{{ route('import.template',$key) }}">⇩ Unduh Template {{ $type['label'] }}</a>
        <form method="POST" action="{{ route('import.'.$key) }}" enctype="multipart/form-data">@csrf
            <div class="field" style="margin-top:18px">
                <label for="file-{{ $key }}">Unggah CSV {{ $type['label'] }} *</label>
                <input id="file-{{ $key }}" class="control" type="file" name="file" accept=".csv,.txt,text/csv" required>
                <span class="hint">CSV UTF-8, maksimal 4 MB dan 1.000 baris.</span>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top:16px">Periksa &amp; Pratinjau →</button>
        </form>
    </div>
@endforeach
</div>
<div class="section-card" style="margin-top:20px">
    <div class="card-header"><h2>Aturan Penting</h2></div>
    <div class="card-body">
        <p>Kode yang belum ada akan ditambahkan; kode yang sudah ada akan diperbarui hanya pada kolom yang disertakan. Tidak ada perubahan sebelum tombol konfirmasi ditekan.</p>
        <p><b>Nilai pilihan:</b> kondisi inventaris = <span class="code">baik</span>, <span class="code">rusak_ringan</span>, <span class="code">rusak_berat</span>, atau <span class="code">hilang</span>. Status prasarana = <span class="code">belum</span>, <span class="code">terverifikasi</span>, atau <span class="code">perlu_koreksi</span>.</p>
        <p><b>Referensi lokasi:</b> gunakan kode gedung pada <span class="code">gedung_kode</span> dan kode prasarana pada <span class="code">lokasi_kode</span>. Kode referensi harus sudah tersimpan. Nilai <span class="code">is_dummy</span> menggunakan 0/1, ya/tidak, atau true/false.</p>
        <p style="margin-bottom:0"><b>Excel:</b> simpan kembali sebagai <i>CSV UTF-8</i>. Pemisah titik koma maupun koma dapat dibaca otomatis.</p>
    </div>
</div>
@endif
@endsection

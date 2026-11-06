@extends('layouts.app')
@section('title','Dokumentasi Foto')
@section('content')
<div class="heading-row">
    <div>
        <div class="kicker">VERIFIKASI LAPANGAN</div>
        <div class="section-title-help"><h1>Dokumentasi Foto</h1><x-help-tip text="Halaman ini memprioritaskan ruang dan barang yang belum memiliki bukti visual." /></div>
        <p>Lengkapi bukti kondisi ruang terlebih dahulu, kemudian dokumentasikan jumlah dan ketersediaan barang.</p>
    </div>
    <div class="heading-actions">
        <span class="action-help"><a href="{{ route('prasarana.index') }}" class="btn btn-primary">Pilih Ruangan untuk Difoto</a><x-help-tip text="Pilih ruang, buka detailnya, lalu gunakan bagian Tambah Foto Ruangan." /></span>
        <a href="{{ route('inventaris.index') }}" class="btn">Pilih Barang</a>
    </div>
</div>
<div class="info-banner">
    <strong>Prioritas ruang:</strong> buka ruang yang belum memiliki foto, lalu gunakan bagian “Tambah Foto Ruangan”.
    Kamera ponsel dapat dibuka langsung dari input foto.
</div>
<div class="kpi-grid">
    <div class="kpi"><span class="label">Foto Ruangan</span><div class="stat-number">{{ number_format($ringkasan['foto_ruang'],0,',','.') }}</div><div class="mini">Dokumentasi tersimpan</div></div>
    <div class="kpi"><span class="label">Ruang Tanpa Foto</span><div class="stat-number">{{ number_format($ringkasan['ruang_tanpa_foto'],0,',','.') }}</div><div class="mini">Menjadi prioritas verifikasi</div></div>
    <div class="kpi"><span class="label">Foto Barang</span><div class="stat-number">{{ number_format($ringkasan['foto_barang'],0,',','.') }}</div><div class="mini">Bukti inventaris tersimpan</div></div>
    <div class="kpi"><span class="label">Barang Tanpa Foto</span><div class="stat-number">{{ number_format($ringkasan['barang_tanpa_foto'],0,',','.') }}</div><div class="mini">Belum memiliki bukti visual</div></div>
</div>

<section class="section-card">
    <div class="card-header"><div class="section-title-help"><h2>Prioritas Dokumentasi Ruangan</h2><x-help-tip text="Ruang tanpa foto berada di urutan awal agar proses verifikasi lapangan lebih terarah." /></div><span class="small muted">Ruang tanpa foto ditampilkan lebih dahulu</span></div>
    <div class="table-scroll"><table>
        <thead><tr><th>Kode</th><th>Ruangan / Prasarana</th><th>Gedung</th><th>Dokumentasi</th><th>Aksi</th></tr></thead>
        <tbody>@forelse($ruang as $item)<tr>
            <td><span class="code">{{ $item->kode }}</span></td>
            <td><a class="cell-link" href="{{ route('prasarana.show',$item) }}">{{ $item->nama }}</a></td>
            <td>{{ $item->gedung?->nama ?: 'Belum dipetakan' }}</td>
            <td><span class="badge {{ $item->dokumentasi_foto_count?'green':'red' }}">{{ $item->dokumentasi_foto_count }} foto</span></td>
            <td><a class="btn btn-sm {{ auth()->user()->canEdit()?'btn-primary':'' }}" href="{{ route('prasarana.show',$item) }}#dokumentasi-foto-prasarana">{{ auth()->user()->canEdit()?'Tambah / Lihat Foto':'Lihat Foto' }}</a></td>
        </tr>@empty<tr><td colspan="5" class="empty">Belum ada data ruangan.</td></tr>@endforelse</tbody>
    </table></div>
    {{ $ruang->links() }}
</section>

<section class="section-card" style="margin-top:23px">
    <div class="card-header"><div class="section-title-help"><h2>Dokumentasi Inventaris Barang</h2><x-help-tip text="Gunakan foto jumlah barang untuk membandingkan stok fisik dengan jumlah yang tercatat." /></div><span class="small muted">Barang tanpa foto ditampilkan lebih dahulu</span></div>
    <div class="table-scroll"><table>
        <thead><tr><th>Kode</th><th>Barang</th><th>Lokasi</th><th>Jumlah</th><th>Dokumentasi</th><th>Aksi</th></tr></thead>
        <tbody>@forelse($barang as $item)<tr>
            <td><span class="code">{{ $item->kode }}</span></td>
            <td><a class="cell-link" href="{{ route('inventaris.show',$item) }}">{{ $item->nama }}</a></td>
            <td>{{ $item->prasarana?->nama ?: 'Belum ditentukan' }}</td>
            <td>{{ number_format($item->jumlah,0,',','.') }} unit</td>
            <td><span class="badge {{ $item->dokumentasi_foto_count?'green':'gray' }}">{{ $item->dokumentasi_foto_count }} foto</span></td>
            <td><a class="btn btn-sm {{ auth()->user()->canEdit()?'btn-primary':'' }}" href="{{ route('inventaris.show',$item) }}#dokumentasi-foto-inventaris">{{ auth()->user()->canEdit()?'Tambah / Lihat Foto':'Lihat Foto' }}</a></td>
        </tr>@empty<tr><td colspan="6" class="empty">Belum ada data inventaris.</td></tr>@endforelse</tbody>
    </table></div>
    {{ $barang->links() }}
</section>
@endsection

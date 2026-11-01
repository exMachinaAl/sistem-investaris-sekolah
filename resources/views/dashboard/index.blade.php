@extends('layouts.app')
@section('title','Dashboard')
@section('content')
<div class="heading-row"><div><div class="kicker">OVERVIEW SARANA DAN PRASARANA</div><h1>Dashboard Inventaris Sekolah</h1><p>Ringkasan fasilitas, ruangan, dan barang inventaris yang tercatat.</p></div><div class="heading-actions"><a href="{{ route('laporan.index') }}" class="btn">▥ Lihat laporan</a>@if(auth()->user()->canEdit())<a href="{{ route('prasarana.create') }}" class="btn btn-primary">+ Tambah prasarana</a>@endif</div></div>
<div class="info-banner"><strong>Transparansi data:</strong> {{ number_format($stats['prasarana'],0,',','.') }} prasarana tercatat; {{ number_format($stats['belum_lokasi'],0,',','.') }} belum ditetapkan ke gedung. Data yang belum terverifikasi tetap perlu dicek oleh petugas.</div>
<div class="kpi-grid">
<div class="kpi"><span class="label">Total Gedung</span><div class="kpi-icon">▤</div><div class="stat-number">{{ number_format($stats['gedung'],0,',','.') }}</div><div class="mini">{{ $stats['gedung_dummy'] }} gedung berlabel dummy</div></div>
<div class="kpi"><span class="label">Total Prasarana</span><div class="kpi-icon green">▧</div><div class="stat-number">{{ number_format($stats['prasarana'],0,',','.') }}</div><div class="mini">{{ $stats['belum_verifikasi'] }} perlu verifikasi</div></div>
<div class="kpi"><span class="label">Jumlah Jurusan</span><div class="kpi-icon amber">◇</div><div class="stat-number">{{ number_format($stats['jurusan'],0,',','.') }}</div><div class="mini">Jurusan yang tercatat</div></div>
<div class="kpi"><span class="label">Unit Barang</span><div class="kpi-icon red">◈</div><div class="stat-number">{{ number_format($stats['unit_barang'],0,',','.') }}</div><div class="mini">{{ $stats['jenis_barang'] }} entri • {{ $stats['barang_dummy'] }} entri dummy</div></div>
</div>
<div class="card welcome" style="margin-bottom:23px"><div><div class="kicker" style="color:#aac7ff">PENGELOLAAN DATA</div><h2>Data lebih rapi, keputusan lebih mudah.</h2><p>Petakan ruangan ke gedung dan jurusan, lalu catat inventaris per lokasi.</p></div><a class="btn" href="{{ route('prasarana.index',['gedung'=>'kosong']) }}">Lihat ruang belum dipetakan →</a></div>
<div class="layout-grid">
<section class="section-card"><div class="card-header"><h2>Kategori Prasarana</h2><a class="btn-link small" href="{{ route('prasarana.index') }}">Semua data →</a></div><div class="card-body bars">
@forelse($kategori as $x)<div class="bar-row"><div class="bar-head"><span>{{ $x->kategori ?: 'Tanpa Kategori' }}</span><span>{{ $x->jumlah }} fasilitas</span></div><div class="bar-track"><div class="bar-fill" style="width:{{ ($x->jumlah/max(1,$stats['prasarana']))*100 }}%"></div></div></div>@empty<p>Belum ada data kategori.</p>@endforelse
</div></section>
<section class="section-card"><div class="card-header"><h2>Ruang per Gedung</h2><a class="btn-link small" href="{{ route('gedung.index') }}">Kelola gedung →</a></div><div class="card-body">
@forelse($gedung as $x)<div class="list-check"><span class="dot"></span><div style="flex:1"><a class="cell-link" href="{{ route('gedung.show',$x) }}">{{ $x->nama }}</a>@if($x->is_dummy) <span class="badge yellow">Dummy</span>@endif</div><b>{{ $x->prasarana_count }} ruang</b></div>@empty<p>Gedung belum dicatat. Mulai dengan menambahkan gedung sekolah.</p>@endforelse
<div class="list-check"><span class="dot" style="background:#e3a650"></span><div style="flex:1">Lokasi gedung belum ditentukan</div><b>{{ $stats['belum_lokasi'] }}</b></div>
</div></section>
</div>
<div class="section-card" style="margin-top:23px"><div class="card-header"><h2>Prasarana terakhir diperbarui</h2><a href="{{ route('prasarana.index') }}" class="btn-link small">Buka semua →</a></div><div class="table-scroll"><table><thead><tr><th>Kode</th><th>Prasarana</th><th>Kategori</th><th>Gedung</th><th>Status</th></tr></thead><tbody>
@forelse($terbaru as $x)<tr><td><span class="code">{{ $x->kode }}</span></td><td><a class="cell-link" href="{{ route('prasarana.show',$x) }}">{{ $x->nama }}</a></td><td>{{ $x->kategori_sementara ?: '—' }}</td><td>{{ $x->gedung?->nama ?: 'Belum dipetakan' }}</td><td><span class="badge {{ $x->status_verifikasi==='terverifikasi'?'green':($x->status_verifikasi==='perlu_koreksi'?'red':'yellow') }}">{{ str_replace('_',' ',ucfirst($x->status_verifikasi)) }}</span></td></tr>@empty<tr><td colspan="5" class="empty">Belum ada data prasarana.</td></tr>@endforelse
</tbody></table></div></div>
@endsection

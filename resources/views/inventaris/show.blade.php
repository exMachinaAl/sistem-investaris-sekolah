@extends('layouts.app')
@section('title','Detail Inventaris')
@section('content')
<div class="heading-row"><div><div class="kicker">INVENTARIS / DETAIL</div><h1>{{ $inventaris->nama }}</h1><p><span class="code">{{ $inventaris->kode }}</span> <span class="badge {{ $inventaris->kondisi==='baik'?'green':($inventaris->kondisi==='rusak_ringan'?'yellow':'red') }}">{{ ucfirst(str_replace('_',' ',$inventaris->kondisi)) }}</span> @if($inventaris->is_dummy)<span class="badge yellow">Data Dummy</span>@endif</p></div><div class="heading-actions"><a class="btn" href="{{ route('inventaris.index') }}">← Kembali</a>@if(auth()->user()->canEdit())<a href="#dokumentasi-foto-inventaris" class="btn">+ Foto Bukti</a><a class="btn btn-primary" href="{{ route('inventaris.edit',$inventaris) }}">Edit Barang</a>@endif</div></div>
@if(auth()->user()->canEdit())
<div class="info-banner" style="margin-bottom:16px"><strong>Arsip, bukan hapus permanen:</strong> menonaktifkan entri tidak menghapus riwayatnya.
<form method="POST" action="{{ route('inventaris.destroy',$inventaris) }}" style="display:inline-block;margin-left:12px">@csrf @method('DELETE')<button class="btn" onclick="return confirm('Arsipkan barang ini? Riwayat tetap tersimpan.')">Arsipkan Barang</button></form></div>
@endif
<div class="two-panel"><div class="stack">
<div class="card"><h2>Informasi Barang</h2><div class="detail-grid"><div class="detail"><dt>Kode Inventaris</dt><dd>{{ $inventaris->kode }}</dd></div><div class="detail"><dt>Jumlah Unit</dt><dd>{{ number_format($inventaris->jumlah,0,',','.') }} unit</dd></div><div class="detail"><dt>Prasarana / Lokasi</dt><dd>@if($inventaris->prasarana)<a href="{{ route('prasarana.show',$inventaris->prasarana) }}" class="cell-link">{{ $inventaris->prasarana->nama }}</a>@else Belum ditentukan @endif</dd></div><div class="detail"><dt>Kondisi</dt><dd>{{ ucfirst(str_replace('_',' ',$inventaris->kondisi)) }}</dd></div><div class="detail"><dt>Tahun Perolehan</dt><dd>{{ $inventaris->tahun_perolehan ?: 'Belum ditentukan' }}</dd></div><div class="detail"><dt>Data</dt><dd>{{ $inventaris->is_dummy?'Dummy / perlu verifikasi':'Input operasional' }}</dd></div><div class="detail span-2"><dt>Keterangan</dt><dd>{{ $inventaris->keterangan ?: '—' }}</dd></div></div></div>
<div class="section-card"><div class="card-header"><h2>Riwayat Mutasi</h2><span class="small muted">Perpindahan SELURUH unit/baris</span></div><div class="table-scroll"><table><thead><tr><th>Tanggal</th><th>Dari</th><th>Ke</th><th>Jumlah</th></tr></thead><tbody>@forelse($inventaris->mutasi as $m)<tr><td>{{ $m->tanggal?->format('d/m/Y') }}</td><td>{{ $m->asal?->nama ?: 'Belum ada lokasi' }}</td><td>{{ $m->tujuan?->nama ?: '—' }}</td><td>{{ $m->jumlah }}</td></tr>@empty<tr><td colspan="4" class="empty">Belum ada mutasi.</td></tr>@endforelse</tbody></table></div></div>
<div class="section-card"><div class="card-header"><h2>Riwayat Pemeliharaan</h2></div><div class="table-scroll"><table><thead><tr><th>Tanggal</th><th>Jenis</th><th>Status</th><th>Biaya</th></tr></thead><tbody>@forelse($inventaris->pemeliharaan as $p)<tr><td>{{ $p->tanggal?->format('d/m/Y') }}</td><td>{{ $p->jenis }} @if($p->catatan)<div class="tiny muted">{{ $p->catatan }}</div>@endif</td><td><span class="badge {{ $p->status==='selesai'?'green':'yellow' }}">{{ ucfirst($p->status) }}</span></td><td>Rp {{ number_format((float)$p->biaya,0,',','.') }}</td></tr>@empty<tr><td colspan="4" class="empty">Belum ada pemeliharaan.</td></tr>@endforelse</tbody></table></div></div>
</div>
<div class="stack">
@if(auth()->user()->canEdit())
<div class="card"><h2>Mutasi Lokasi Barang</h2><p class="small">Mutasi memindahkan <b>seluruh {{ $inventaris->jumlah }} unit</b> yang dicatat pada baris barang ini. Untuk mutasi sebagian, pisahkan menjadi dua entri barang terlebih dahulu.</p><form method="POST" action="{{ route('inventaris.mutasi',$inventaris) }}">@csrf
<div class="field"><label for="tujuan">Lokasi Tujuan *</label><select class="control" id="tujuan" name="tujuan_id" required><option value="">Pilih prasarana tujuan...</option>@foreach($lokasi as $x)<option value="{{ $x->id }}">{{ $x->kode }} — {{ $x->nama }}</option>@endforeach</select></div>
<div class="field" style="margin-top:13px"><label for="tgl">Tanggal Mutasi *</label><input class="control" id="tgl" type="date" name="tanggal" value="{{ date('Y-m-d') }}" required></div>
<div class="field" style="margin-top:13px"><label for="catatan">Keterangan</label><textarea id="catatan" class="control" name="catatan" placeholder="Alasan perpindahan..."></textarea></div>
<button class="btn btn-primary btn-wide" style="margin-top:16px">Catat Mutasi</button></form></div>
<div class="card"><h2>Catat Pemeliharaan</h2><form method="POST" action="{{ route('inventaris.pemeliharaan',$inventaris) }}">@csrf
<div class="field"><label for="tanggal-p">Tanggal *</label><input id="tanggal-p" class="control" type="date" name="tanggal" value="{{ date('Y-m-d') }}" required></div>
<div class="field" style="margin-top:13px"><label for="jenis">Jenis Pemeliharaan *</label><input id="jenis" class="control" name="jenis" placeholder="Servis proyektor" required maxlength="150"></div>
<div class="field" style="margin-top:13px"><label for="stat-p">Status *</label><select id="stat-p" class="control" name="status"><option value="dijadwalkan">Dijadwalkan</option><option value="diproses">Diproses</option><option value="selesai">Selesai</option></select></div>
<div class="field" style="margin-top:13px"><label for="biaya">Biaya (Rp)</label><input id="biaya" class="control" type="number" min="0" step="0.01" name="biaya" placeholder="0"></div>
<div class="field" style="margin-top:13px"><label for="ket-p">Catatan</label><textarea id="ket-p" class="control" name="catatan"></textarea></div><button class="btn btn-primary btn-wide" style="margin-top:16px">Simpan Pemeliharaan</button>
</form></div>
@else<div class="card"><h2>Riwayat Aset</h2><p>Anda dapat membaca data barang dan laporannya. Untuk mencatat perubahan, hubungi pengelola sarpras.</p></div>@endif
</div></div>
<div style="margin-top:23px">@include('partials.dokumentasi-foto',['owner'=>$inventaris,'mode'=>'inventaris'])</div>
@endsection

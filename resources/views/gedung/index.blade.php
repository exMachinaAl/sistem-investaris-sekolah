@extends('layouts.app')
@section('title','Data Gedung')
@section('content')
<div class="heading-row"><div><div class="kicker">MASTER DATA</div><h1>Data Gedung</h1><p>Kelola bangunan beserta jumlah ruang yang berada di dalamnya.</p></div>@if(auth()->user()->canEdit())<a class="btn btn-primary" href="{{ route('gedung.create') }}">+ Tambah Gedung</a>@endif</div>
<div class="filter-card"><form class="filter-form" action="{{ route('gedung.index') }}"><div class="field search"><label>Pencarian gedung</label><input class="control" name="q" placeholder="Cari kode atau nama gedung..." value="{{ request('q') }}"></div><button class="btn btn-primary">Cari</button><a class="btn" href="{{ route('gedung.index') }}">Reset</a></form></div>
<div class="section-card"><div class="card-header"><h2>Daftar Gedung</h2><span class="small muted">{{ $data->total() }} data</span></div><div class="table-scroll"><table><thead><tr><th>Kode</th><th>Nama Gedung</th><th>Lantai</th><th>Total Prasarana</th><th>Sumber</th><th>Aksi</th></tr></thead><tbody>
@forelse($data as $x)<tr><td><span class="code">{{ $x->kode }}</span></td><td><a href="{{ route('gedung.show',$x) }}" class="cell-link">{{ $x->nama }}</a></td><td>{{ $x->jumlah_lantai ?? '—' }}</td><td><span class="badge blue">{{ $x->prasarana_count }} ruang/fasilitas</span></td><td>@if($x->is_dummy)<span class="badge yellow">Dummy</span>@else<span class="badge green">Entri riil/manual</span>@endif</td><td class="actions"><a class="btn-link" href="{{ route('gedung.show',$x) }}">Detail</a>@if(auth()->user()->canEdit())<a class="btn-link" href="{{ route('gedung.edit',$x) }}">Edit</a>@endif</td></tr>
@empty<tr><td colspan="6" class="empty"><div class="empty-icon">▤</div>Belum ada data gedung.</td></tr>@endforelse
</tbody></table></div>{{ $data->links() }}</div>
@endsection

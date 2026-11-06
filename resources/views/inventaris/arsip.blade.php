@extends('layouts.app')
@section('title','Arsip Inventaris')
@section('content')
<div class="heading-row"><div><div class="kicker">INVENTARIS / ARSIP</div><div class="section-title-help"><h1>Barang Nonaktif</h1><x-help-tip text="Arsip menyembunyikan barang dari daftar aktif tanpa menghapus histori mutasi dan pemeliharaan." /></div><p>Barang dalam arsip tidak muncul di daftar aktif, namun histori mutasi dan pemeliharaan tetap ada.</p></div><a class="btn" href="{{ route('inventaris.index') }}">← Inventaris Aktif</a></div>
<div class="section-card"><div class="card-header"><div class="section-title-help"><h2>Arsip Barang</h2><x-help-tip text="Pulihkan jika barang kembali aktif atau sebelumnya diarsipkan karena kesalahan." /></div><span class="small muted">{{ $data->total() }} entri</span></div><div class="table-scroll"><table><thead><tr><th>Kode</th><th>Barang</th><th>Jumlah</th><th>Tanggal Arsip</th><th>Aksi</th></tr></thead><tbody>
@forelse($data as $item)<tr><td><span class="code">{{ $item->kode }}</span></td><td>{{ $item->nama }}</td><td>{{ $item->jumlah }}</td><td>{{ $item->deleted_at?->format('d/m/Y H:i') }}</td><td><form action="{{ route('inventaris.pulihkan',$item->id) }}" method="POST">@csrf<button class="btn btn-primary" onclick="return confirm('Pulihkan barang ini ke daftar inventaris aktif?')">Pulihkan</button></form></td></tr>
@empty<tr><td colspan="5" class="empty">Belum ada barang yang diarsipkan.</td></tr>@endforelse
</tbody></table></div>{{ $data->links() }}</div>
@endsection

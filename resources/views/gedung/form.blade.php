@extends('layouts.app')
@section('title',$item->exists?'Edit Gedung':'Tambah Gedung')
@section('content')
<div class="heading-row"><div><div class="kicker">MASTER DATA / GEDUNG</div><h1>{{ $item->exists?'Edit Gedung':'Tambah Gedung' }}</h1><p>Pastikan setiap gedung memiliki kode yang berbeda.</p></div><a class="btn" href="{{ route('gedung.index') }}">← Kembali</a></div>
<form method="POST" action="{{ $item->exists?route('gedung.update',$item):route('gedung.store') }}">@csrf @if($item->exists)@method('PUT')@endif
<div class="card"><h2>Informasi Gedung</h2><div class="form-grid">
<div class="field"><label for="kode">Kode Gedung *</label><input id="kode" name="kode" class="control" value="{{ old('kode',$item->kode) }}" placeholder="Contoh: GD-A01" maxlength="30" required></div>
<div class="field"><label for="nama">Nama Gedung *</label><input id="nama" name="nama" class="control" value="{{ old('nama',$item->nama) }}" placeholder="Contoh: Gedung Praktik A" required></div>
<div class="field"><label for="lantai">Jumlah Lantai</label><input id="lantai" name="jumlah_lantai" type="number" min="1" max="100" class="control" value="{{ old('jumlah_lantai',$item->jumlah_lantai) }}"></div>
<div class="field"><label for="keterangan">Keterangan</label><textarea id="keterangan" name="keterangan" class="control">{{ old('keterangan',$item->keterangan) }}</textarea></div>
<div class="span-2"><label class="checkrow"><input type="checkbox" name="is_dummy" value="1" {{ old('is_dummy',$item->is_dummy)?'checked':'' }}> Tandai sebagai data dummy (belum berdasarkan survei lapangan)</label></div>
</div><div class="form-actions"><a class="btn" href="{{ route('gedung.index') }}">Batal</a><button class="btn btn-primary" type="submit">Simpan Gedung</button></div></div></form>
@if($item->exists && auth()->user()->canEdit())<div class="danger-zone"><h3>Hapus gedung</h3><p>Hanya bisa dihapus apabila belum mempunyai prasarana yang terkait.</p><form method="POST" action="{{ route('gedung.destroy',$item) }}" onsubmit="return confirm('Hapus gedung ini?')">@csrf @method('DELETE')<button class="btn btn-danger">Hapus Gedung</button></form></div>@endif
@endsection

@extends('layouts.app')
@section('title',$item->exists?'Edit Prasarana':'Tambah Prasarana')
@section('content')
<div class="heading-row"><div><div class="kicker">MASTER DATA / PRASARANA</div><h1>{{ $item->exists?'Edit Prasarana':'Tambah Prasarana' }}</h1><p>Isi informasi lokasi dan pilih jurusan pengguna atau pengelola fasilitas.</p></div><a href="{{ $item->exists?route('prasarana.show',$item):route('prasarana.index') }}" class="btn">← Kembali</a></div>
<form method="POST" action="{{ $item->exists?route('prasarana.update',$item):route('prasarana.store') }}">@csrf @if($item->exists)@method('PUT')@endif
<div class="stack">
<section class="card"><h2>Identitas Prasarana</h2><div class="form-grid">
<div class="field"><label for="kode">Kode Prasarana *</label><input id="kode" class="control" name="kode" value="{{ old('kode',$item->kode) }}" required maxlength="30" placeholder="PRS-149"></div>
<div class="field"><label for="nama">Nama Prasarana *</label><input id="nama" class="control" name="nama" value="{{ old('nama',$item->nama) }}" required maxlength="255" placeholder="Contoh: Ruang Praktik TKJ"></div>
<div class="field"><label for="kategori">Kategori</label><input list="daftar-kategori" id="kategori" class="control" name="kategori_sementara" value="{{ old('kategori_sementara',$item->kategori_sementara) }}" maxlength="80" placeholder="Contoh: Laboratorium"><datalist id="daftar-kategori">@foreach($kategori as $k)<option value="{{ $k }}">@endforeach</datalist></div>
<div class="field"><label for="status">Status Verifikasi *</label><select id="status" class="control" name="status_verifikasi" required>@foreach(['belum'=>'Belum Diverifikasi','terverifikasi'=>'Terverifikasi','perlu_koreksi'=>'Perlu Koreksi'] as $val=>$label)<option value="{{ $val }}" @selected(old('status_verifikasi',$item->status_verifikasi??'belum')===$val)>{{ $label }}</option>@endforeach</select></div>
<div class="field"><label for="panjang">Panjang (m)</label><input id="panjang" class="control" type="number" min="0" step="0.01" name="panjang" value="{{ old('panjang',$item->panjang) }}"></div>
<div class="field"><label for="lebar">Lebar (m)</label><input id="lebar" class="control" type="number" min="0" step="0.01" name="lebar" value="{{ old('lebar',$item->lebar) }}"></div>
<div class="field"><label for="kepemilikan">Status Kepemilikan</label><input id="kepemilikan" class="control" name="status_kepemilikan" value="{{ old('status_kepemilikan',$item->status_kepemilikan) }}" maxlength="100" placeholder="Contoh: Milik sekolah"></div>
<div class="field"><label for="ket">Keterangan / Kode Sumber</label><textarea id="ket" class="control" name="keterangan_sumber">{{ old('keterangan_sumber',$item->keterangan_sumber) }}</textarea></div>
</div></section>
<section class="card"><h2>Lokasi dan Pengguna</h2><div class="form-grid">
<div class="field"><label for="gedung">Gedung</label><select id="gedung" class="control" name="gedung_id"><option value="">Belum ditentukan / Area di luar gedung</option>@foreach($gedung as $g)<option value="{{ $g->id }}" @selected((string)old('gedung_id',$item->gedung_id)===(string)$g->id)>{{ $g->kode }} — {{ $g->nama }}{{ $g->is_dummy?' [DUMMY]':'' }}</option>@endforeach</select><span class="hint">Fasilitas luar seperti lapangan boleh tanpa gedung.</span></div>
<div class="field"><label for="lantai">Lantai</label><input id="lantai" name="lantai" class="control" type="number" min="0" max="100" value="{{ old('lantai',$item->lantai) }}" placeholder="Misalnya: 1"></div>
<div class="span-2"><div class="field-label" style="margin-bottom:10px">Jurusan Pengguna Ruang (boleh lebih dari satu)</div>
@php
$selected = old('jurusan_ids', $item->exists ? $item->jurusan->pluck('id')->all() : []);
@endphp
<div class="checkgroup">@forelse($jurusan as $j)<label class="checkrow"><input type="checkbox" name="jurusan_ids[]" value="{{ $j->id }}" @checked(in_array($j->id,array_map('intval',(array)$selected)))><span>{{ $j->kode }} — {{ $j->nama }}</span></label>@empty<span class="small muted">Belum ada jurusan. Tambahkan melalui menu Data Jurusan.</span>@endforelse</div></div>
<div class="span-2"><label class="checkrow"><input type="checkbox" name="is_dummy" value="1" @checked(old('is_dummy',$item->is_dummy))> Tandai baris ini sebagai data dummy</label></div>
</div><div class="form-actions"><a class="btn" href="{{ route('prasarana.index') }}">Batal</a><button type="submit" class="btn btn-primary">Simpan Prasarana</button></div></section>
</div></form>
@if($item->exists && auth()->user()->canEdit())<div class="danger-zone"><h3>Hapus Prasarana</h3><p>Prasarana dengan inventaris terkait tidak dapat dihapus sebelum barang dipindahkan.</p><form method="POST" action="{{ route('prasarana.destroy',$item) }}" onsubmit="return confirm('Hapus prasarana ini beserta relasi jurusannya?')">@csrf @method('DELETE')<button class="btn btn-danger">Hapus Prasarana</button></form></div>@endif
@endsection

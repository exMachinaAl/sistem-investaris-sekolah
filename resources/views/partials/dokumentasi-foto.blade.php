@php
    $isRuang = $mode === 'prasarana';
    $fotoList = $owner->dokumentasiFoto;
    $storeRoute = $isRuang ? route('prasarana.foto.store',$owner) : route('inventaris.foto.store',$owner);
    $kategoriLabel = [
        'ruangan'=>'Foto Ruangan',
        'jumlah_barang'=>'Bukti Jumlah Barang',
        'ketersediaan'=>'Bukti Ketersediaan',
        'kondisi'=>'Kondisi Terkini',
    ];
@endphp
<section class="section-card photo-section" id="dokumentasi-foto-{{ $mode }}">
    <div class="card-header">
        <div>
            <div class="section-title-help"><h2>{{ $isRuang ? 'Dokumentasi Foto Ruangan' : 'Foto Bukti Inventaris' }}</h2><x-help-tip text="Foto menjadi bukti visual kondisi dan ketersediaan saat verifikasi. Gunakan foto yang jelas, relevan, dan terbaru." /></div>
            <div class="small muted">{{ $isRuang ? 'Bukti kondisi, ketersediaan, dan verifikasi ruang.' : 'Bukti jumlah, ketersediaan, dan kondisi barang.' }}</div>
        </div>
        <span class="badge blue">{{ $fotoList->count() }} foto</span>
    </div>
    @if($fotoList->isNotEmpty())
    <div class="photo-grid">
        @foreach($fotoList as $foto)
        <article class="photo-card">
            <a href="{{ $foto->url }}" target="_blank" rel="noopener" title="Buka foto ukuran penuh">
                <img src="{{ $foto->url }}" alt="{{ $kategoriLabel[$foto->kategori]??'Dokumentasi' }} {{ $owner->nama }}" loading="lazy">
            </a>
            <div class="photo-meta">
                <div class="photo-meta-head">
                    <span class="badge {{ $foto->kategori==='kondisi'?'yellow':'blue' }}">{{ $kategoriLabel[$foto->kategori]??ucfirst($foto->kategori) }}</span>
                    <span class="tiny muted">{{ $foto->tanggal_foto?->format('d/m/Y') }}</span>
                </div>
                @if($foto->jumlah_tercatat!==null)<b>{{ number_format($foto->jumlah_tercatat,0,',','.') }} unit tercatat</b>@endif
                @if($foto->keterangan)<p class="small">{{ $foto->keterangan }}</p>@endif
                <div class="tiny muted">Diunggah oleh {{ $foto->user?->name ?: 'Pengguna' }}</div>
                @if(auth()->user()->canEdit())
                <form method="POST" action="{{ $isRuang ? route('prasarana.foto.destroy',[$owner,$foto]) : route('inventaris.foto.destroy',[$owner,$foto]) }}" onsubmit="return confirm('Hapus foto dokumentasi ini?')" style="margin-top:10px">@csrf @method('DELETE')
                    <button class="btn btn-danger btn-sm">Hapus Foto</button>
                </form>
                @endif
            </div>
        </article>
        @endforeach
    </div>
    @else
    <div class="empty photo-empty">Belum ada foto dokumentasi. {{ $isRuang ? 'Tambahkan foto ruang sebagai bukti verifikasi.' : 'Tambahkan foto yang memperlihatkan barang dan jumlahnya.' }}</div>
    @endif

    @if(auth()->user()->canEdit())
    <div class="photo-upload">
        <div>
            <h3>{{ $isRuang ? 'Tambah Foto Ruangan' : 'Tambah Foto Bukti Barang' }}</h3>
            <p class="small">{{ $isRuang ? 'Ambil foto yang memperlihatkan keseluruhan ruang dengan cukup cahaya.' : 'Usahakan seluruh unit atau susunan barang terlihat jelas di dalam foto.' }}</p>
        </div>
        <form method="POST" action="{{ $storeRoute }}" enctype="multipart/form-data">@csrf
            <div class="form-grid">
                <div class="field">
                    <div class="field-label-row"><label for="foto-{{ $mode }}-{{ $owner->id }}">Pilih / Ambil Foto *</label><x-help-tip text="Ambil foto baru atau pilih JPG, PNG, atau WEBP. Pastikan objek terlihat jelas dan tidak memuat data pribadi yang tidak diperlukan." /></div>
                    <input id="foto-{{ $mode }}-{{ $owner->id }}" class="control" type="file" name="foto" accept="image/jpeg,image/png,image/webp" capture="environment" required>
                    <span class="hint">JPG, PNG, atau WEBP; maksimal 5 MB. Minimal 200 × 200 piksel.</span>
                </div>
                <div class="field">
                    <div class="field-label-row"><label for="kategori-{{ $mode }}-{{ $owner->id }}">Jenis Bukti *</label><x-help-tip text="Pilih tujuan utama foto agar bukti mudah dikelompokkan dan diperiksa kembali." /></div>
                    <select id="kategori-{{ $mode }}-{{ $owner->id }}" class="control" name="kategori" required>
                        @if($isRuang)<option value="ruangan">Foto keseluruhan ruangan</option>@else<option value="jumlah_barang">Bukti jumlah barang</option>@endif
                        <option value="ketersediaan">Bukti ketersediaan</option>
                        <option value="kondisi">Kondisi terkini</option>
                    </select>
                </div>
                <div class="field">
                    <div class="field-label-row"><label for="jumlah-foto-{{ $mode }}-{{ $owner->id }}">Jumlah Terlihat / Terverifikasi {{ $isRuang?'(opsional)':'*' }}</label><x-help-tip text="Isi jumlah yang benar-benar dihitung atau terlihat saat foto diambil, bukan hanya menyalin jumlah sistem tanpa pemeriksaan." /></div>
                    <input id="jumlah-foto-{{ $mode }}-{{ $owner->id }}" class="control" type="number" min="0" name="jumlah_tercatat" value="{{ old('jumlah_tercatat', $isRuang ? '' : $owner->jumlah) }}" {{ $isRuang?'':'required' }}>
                    <span class="hint">{{ $isRuang ? 'Isi bila foto dipakai untuk memverifikasi barang di dalam ruang.' : 'Jumlah unit yang benar-benar terlihat atau dihitung saat foto diambil.' }}</span>
                </div>
                <div class="field">
                    <div class="field-label-row"><label for="tanggal-foto-{{ $mode }}-{{ $owner->id }}">Tanggal Foto *</label><x-help-tip text="Tanggal pengambilan foto. Tanggal masa depan tidak diperbolehkan." /></div>
                    <input id="tanggal-foto-{{ $mode }}-{{ $owner->id }}" class="control" type="date" name="tanggal_foto" max="{{ now()->toDateString() }}" value="{{ old('tanggal_foto',now()->toDateString()) }}" required>
                </div>
                <div class="field span-2">
                    <div class="field-label-row"><label for="ket-foto-{{ $mode }}-{{ $owner->id }}">Keterangan</label><x-help-tip text="Jelaskan sudut pengambilan, semester verifikasi, perbedaan jumlah, atau kondisi penting yang terlihat." /></div>
                    <textarea id="ket-foto-{{ $mode }}-{{ $owner->id }}" class="control" name="keterangan" maxlength="500" placeholder="Contoh: Foto verifikasi stok semester ganjil, sisi depan ruangan.">{{ old('keterangan') }}</textarea>
                </div>
            </div>
            <div class="form-actions"><div class="action-help"><button class="btn btn-primary">Unggah Dokumentasi</button><x-help-tip text="File disimpan secara privat dan hanya dapat dilihat oleh pengguna yang sudah masuk ke aplikasi." /></div></div>
        </form>
    </div>
    @endif
</section>

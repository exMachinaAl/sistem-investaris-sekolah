<?php
namespace App\Http\Controllers;
use App\Models\Gedung;
use App\Models\DokumentasiFoto;
use App\Models\Inventaris;
use App\Models\MutasiInventaris;
use App\Models\Jurusan;
use App\Models\Prasarana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
class PrasaranaController {
    public function index(Request $request){
        $query=Prasarana::with(['gedung','jurusan'])->withCount('dokumentasiFoto');
        if($q=trim((string)$request->query('q',''))){$query->where(fn($w)=>$w->where('nama','like',"%$q%")->orWhere('kode','like',"%$q%"));}
        if($request->filled('kategori')){$query->where('kategori_sementara',$request->query('kategori'));}
        if($request->query('gedung')==='kosong'){$query->whereNull('gedung_id');}
        elseif($request->filled('gedung')){$query->where('gedung_id',$request->query('gedung'));}
        if($request->filled('jurusan')){$query->whereHas('jurusan',fn($w)=>$w->where('jurusan.id',$request->query('jurusan')));}
        if($request->filled('status')){$query->where('status_verifikasi',$request->query('status'));}
        $data=$query->orderBy('kode')->paginate(12)->withQueryString();
        $gedung=Gedung::orderBy('nama')->get();$jurusan=Jurusan::orderBy('nama')->get();
        $kategori=Prasarana::whereNotNull('kategori_sementara')->distinct()->orderBy('kategori_sementara')->pluck('kategori_sementara');
        return view('prasarana.index',compact('data','gedung','jurusan','kategori'));
    }
    public function show(Prasarana $prasarana){
        $prasarana->load(['gedung','jurusan','dokumentasiFoto.user']);
        $inventaris=$prasarana->inventaris()->orderBy('nama')->paginate(10);
        return view('prasarana.show',compact('prasarana','inventaris'));
    }
    public function create(){return $this->form(new Prasarana);}
    public function edit(Prasarana $prasarana){$prasarana->load('jurusan');return $this->form($prasarana);}
    private function form(Prasarana $item){
        $gedung=Gedung::orderBy('nama')->get();$jurusan=Jurusan::orderBy('nama')->get();
        $kategori=Prasarana::whereNotNull('kategori_sementara')->distinct()->orderBy('kategori_sementara')->pluck('kategori_sementara');
        return view('prasarana.form',compact('item','gedung','jurusan','kategori'));
    }
    private function rules(?Prasarana $item=null):array{
        return [
            'kode'=>['required','string','max:30',Rule::unique('prasarana','kode')->ignore($item?->id)],
            'nama'=>['required','string','max:255'], 'kategori_sementara'=>['nullable','string','max:80'],
            'gedung_id'=>['nullable','integer','exists:gedung,id'],'lantai'=>['nullable','integer','min:0','max:100'],
            'panjang'=>['nullable','numeric','min:0','max:999999'],'lebar'=>['nullable','numeric','min:0','max:999999'],
            'keterangan_sumber'=>['nullable','string','max:10000'],'status_kepemilikan'=>['nullable','string','max:100'],
            'status_verifikasi'=>['required',Rule::in(['belum','terverifikasi','perlu_koreksi'])],
            'jurusan_ids'=>['array'],'jurusan_ids.*'=>['integer','distinct','exists:jurusan,id'],'is_dummy'=>['boolean'],
        ];
    }
    public function store(Request $request){
        $data=$request->validate($this->rules());$ids=$data['jurusan_ids']??[];unset($data['jurusan_ids']);
        $data['is_dummy']=$request->boolean('is_dummy');
        $prasarana=Prasarana::create($data);$prasarana->jurusan()->sync($ids);
        return redirect()->route('prasarana.show',$prasarana)->with('success','Prasarana berhasil ditambahkan.');
    }
    public function update(Request $request,Prasarana $prasarana){
        $data=$request->validate($this->rules($prasarana));$ids=$data['jurusan_ids']??[];unset($data['jurusan_ids']);
        $data['is_dummy']=$request->boolean('is_dummy');
        $prasarana->update($data);$prasarana->jurusan()->sync($ids);
        return redirect()->route('prasarana.show',$prasarana)->with('success','Prasarana berhasil diperbarui.');
    }
    public function destroy(Prasarana $prasarana){
        if(Inventaris::withTrashed()->where('prasarana_id',$prasarana->id)->exists() ||
            MutasiInventaris::where('prasarana_asal_id',$prasarana->id)->orWhere('prasarana_tujuan_id',$prasarana->id)->exists()) {
            return back()->with('error','Prasarana memiliki barang aktif/arsip atau riwayat mutasi; penghapusan diblokir untuk menjaga histori.');
        }
        $paths=$prasarana->dokumentasiFoto()->pluck('path')->all();
        $prasarana->delete();
        Storage::disk('local')->delete($paths);
        return redirect()->route('prasarana.index')->with('success','Prasarana dan dokumentasi fotonya dihapus.');
    }
    public function storeFoto(Request $request,Prasarana $prasarana){
        $data=$request->validate([
            'foto'=>['required','image','mimes:jpg,jpeg,png,webp','max:5120','dimensions:min_width=200,min_height=200,max_width=8000,max_height=8000'],
            'kategori'=>['required',Rule::in(['ruangan','ketersediaan','kondisi'])],
            'jumlah_tercatat'=>['nullable','integer','min:0','max:100000000'],
            'tanggal_foto'=>['required','date','before_or_equal:today'],
            'keterangan'=>['nullable','string','max:500'],
        ]);
        $path=$request->file('foto')->store('dokumentasi/prasarana','local');
        try{
            unset($data['foto']);
            $prasarana->dokumentasiFoto()->create($data+['path'=>$path,'user_id'=>$request->user()->id]);
        }catch(\Throwable $exception){
            Storage::disk('local')->delete($path);
            throw $exception;
        }
        return back()->with('success','Foto dokumentasi ruang berhasil ditambahkan.');
    }
    public function destroyFoto(Prasarana $prasarana,DokumentasiFoto $foto){
        abort_unless((int)$foto->prasarana_id===(int)$prasarana->id,404);
        $path=$foto->path;
        $foto->delete();
        Storage::disk('local')->delete($path);
        return back()->with('success','Foto dokumentasi ruang dihapus.');
    }
}

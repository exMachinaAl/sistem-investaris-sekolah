<?php
namespace App\Http\Controllers;
use App\Models\DokumentasiFoto;
use App\Models\Inventaris;
use App\Models\Prasarana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
class InventarisController {
    public function index(Request $request){
        $query=Inventaris::with('prasarana')->withCount('dokumentasiFoto');
        if($q=trim((string)$request->query('q',''))){$query->where(fn($w)=>$w->where('nama','like',"%$q%")->orWhere('kode','like',"%$q%"));}
        if($request->filled('kondisi')){$query->where('kondisi',$request->query('kondisi'));}
        if($request->query('lokasi')==='kosong'){$query->whereNull('prasarana_id');}
        elseif($request->filled('lokasi')){$query->where('prasarana_id',$request->query('lokasi'));}
        $data=$query->orderBy('kode')->paginate(12)->withQueryString();
        $lokasi=Prasarana::orderBy('nama')->get();
        return view('inventaris.index',compact('data','lokasi'));
    }
    public function show(Inventaris $inventaris){
        $inventaris->load(['prasarana','mutasi.asal','mutasi.tujuan','pemeliharaan','dokumentasiFoto.user']);
        $lokasi=Prasarana::where('id','!=',$inventaris->prasarana_id)->orderBy('nama')->get();
        return view('inventaris.show',compact('inventaris','lokasi'));
    }
    public function create(){return $this->form(new Inventaris);}
    public function edit(Inventaris $inventaris){return $this->form($inventaris);}
    private function form(Inventaris $item){$lokasi=Prasarana::orderBy('nama')->get();return view('inventaris.form',compact('item','lokasi'));}
    private function rules(?Inventaris $item=null):array{
        return ['kode'=>['required','string','max:35',Rule::unique('inventaris','kode')->ignore($item?->id)],
            'nama'=>['required','string','max:255'],'prasarana_id'=>['nullable','integer','exists:prasarana,id'],
            'jumlah'=>['required','integer','min:1','max:100000000'],
            'kondisi'=>['required',Rule::in(['baik','rusak_ringan','rusak_berat','hilang'])],
            'tahun_perolehan'=>['nullable','integer','min:1900','max:2100'],
            'keterangan'=>['nullable','string','max:10000'],'is_dummy'=>['boolean']];
    }
    public function store(Request $request){
        $data=$request->validate($this->rules());$data['is_dummy']=$request->boolean('is_dummy');
        $item=Inventaris::create($data);return redirect()->route('inventaris.show',$item)->with('success','Barang berhasil dicatat.');
    }
    public function update(Request $request,Inventaris $inventaris){
        $data=$request->validate($this->rules($inventaris));$data['is_dummy']=$request->boolean('is_dummy');
        // Perubahan lokasi harus dicatat melalui fitur mutasi (riwayat tidak hilang).
        if((string)($data['prasarana_id']??'') !== (string)($inventaris->prasarana_id??'')){
            return back()->withInput()->with('error','Untuk mengubah lokasi gunakan fitur Mutasi di halaman detail barang.');
        }
        $inventaris->update($data);return redirect()->route('inventaris.show',$inventaris)->with('success','Data barang diperbarui.');
    }
    public function destroy(Inventaris $inventaris){
        $inventaris->delete(); // arsip nonaktif; riwayat mutasi/pemeliharaan tetap tersimpan.
        return redirect()->route('inventaris.index')->with('success','Barang dipindahkan ke arsip. Riwayat tetap tersimpan.');
    }
    public function arsip(Request $request){
        abort_unless($request->user()?->role === 'admin',403);
        $data=Inventaris::onlyTrashed()->orderByDesc('deleted_at')->paginate(15);
        return view('inventaris.arsip', compact('data'));
    }
    public function pulihkan(Request $request, int $inventaris){
        abort_unless($request->user()?->role === 'admin',403);
        $item=Inventaris::onlyTrashed()->findOrFail($inventaris);
        $item->restore();
        return redirect()->route('inventaris.show',$item)->with('success','Barang berhasil dipulihkan dari arsip.');
    }
    public function mutasi(Request $request,Inventaris $inventaris){
        $data=$request->validate(['tujuan_id'=>['required','integer','exists:prasarana,id'],
            'tanggal'=>['required','date'],'catatan'=>['nullable','string','max:3000']]);
        if((int)$inventaris->prasarana_id===(int)$data['tujuan_id'])return back()->with('error','Pilih lokasi tujuan yang berbeda.');
        DB::transaction(function()use($inventaris,$data,$request){
            $locked=Inventaris::whereKey($inventaris->id)->lockForUpdate()->firstOrFail();
            if((int)$locked->prasarana_id===(int)$data['tujuan_id'])abort(422,'Barang sudah berada di lokasi tersebut.');
            $locked->mutasi()->create([
                'prasarana_asal_id'=>$locked->prasarana_id,'prasarana_tujuan_id'=>$data['tujuan_id'],
                'jumlah'=>$locked->jumlah,'tanggal'=>$data['tanggal'],'catatan'=>$data['catatan']??null,'user_id'=>$request->user()->id,
            ]);
            $locked->update(['prasarana_id'=>$data['tujuan_id']]);
        });
        return back()->with('success','Seluruh unit pada baris inventaris ini berhasil dimutasikan.');
    }
    public function pemeliharaan(Request $request,Inventaris $inventaris){
        $data=$request->validate(['tanggal'=>['required','date'],'jenis'=>['required','string','max:150'],
            'status'=>['required',Rule::in(['dijadwalkan','diproses','selesai'])],
            'biaya'=>['nullable','numeric','min:0','max:999999999999'],'catatan'=>['nullable','string','max:3000']]);
        $inventaris->pemeliharaan()->create($data);
        return back()->with('success','Catatan pemeliharaan berhasil ditambahkan.');
    }
    public function storeFoto(Request $request,Inventaris $inventaris){
        $data=$request->validate([
            'foto'=>['required','image','mimes:jpg,jpeg,png,webp','max:5120','dimensions:min_width=200,min_height=200,max_width=8000,max_height=8000'],
            'kategori'=>['required',Rule::in(['jumlah_barang','ketersediaan','kondisi'])],
            'jumlah_tercatat'=>['nullable','required_if:kategori,jumlah_barang','integer','min:0','max:100000000'],
            'tanggal_foto'=>['required','date','before_or_equal:today'],
            'keterangan'=>['nullable','string','max:500'],
        ]);
        $path=$request->file('foto')->store('dokumentasi/inventaris','local');
        try{
            unset($data['foto']);
            $inventaris->dokumentasiFoto()->create($data+['path'=>$path,'user_id'=>$request->user()->id]);
        }catch(\Throwable $exception){
            Storage::disk('local')->delete($path);
            throw $exception;
        }
        return back()->with('success','Foto bukti inventaris berhasil ditambahkan.');
    }
    public function destroyFoto(Inventaris $inventaris,DokumentasiFoto $foto){
        abort_unless((int)$foto->inventaris_id===(int)$inventaris->id,404);
        $path=$foto->path;
        $foto->delete();
        Storage::disk('local')->delete($path);
        return back()->with('success','Foto bukti inventaris dihapus.');
    }
}

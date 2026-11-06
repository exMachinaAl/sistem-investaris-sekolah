<?php
namespace App\Http\Controllers;
use App\Models\Gedung;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
class GedungController {
    public function index(Request $request){
        $query=Gedung::withCount('prasarana');
        if($q=trim((string)$request->query('q',''))){$query->where(fn($w)=>$w->where('nama','like',"%$q%")->orWhere('kode','like',"%$q%"));}
        $data=$query->orderBy('kode')->paginate(10)->withQueryString();
        return view('gedung.index',compact('data'));
    }
    public function show(Gedung $gedung){
        $data=$gedung->prasarana()->with('jurusan')->orderBy('nama')->paginate(12);
        return view('gedung.show',compact('gedung','data'));
    }
    public function create(){return view('gedung.form',['item'=>new Gedung]);}
    public function edit(Gedung $gedung){return view('gedung.form',['item'=>$gedung]);}
    private function rules(?Gedung $item=null):array{
        return ['nama'=>['required','string','max:160'],'jumlah_lantai'=>['nullable','integer','min:1','max:100'],
            'panjang'=>['nullable','required_with:lebar','numeric','gt:0','max:999999'],
            'lebar'=>['nullable','required_with:panjang','numeric','gt:0','max:999999'],
            'status_verifikasi'=>['required',Rule::in(['belum','terverifikasi','perlu_koreksi'])],
            'tanggal_pengukuran'=>['nullable','date','before_or_equal:today'],
            'sumber_ukuran'=>['nullable','string','max:200'],
            'keterangan'=>['nullable','string','max:5000'],'is_dummy'=>['boolean']];
    }
    public function store(Request $request){$data=$request->validate($this->rules());Gedung::create($data+['is_dummy'=>false]);return redirect()->route('gedung.index')->with('success','Gedung berhasil ditambahkan.');}
    public function update(Request $request,Gedung $gedung){
        $data=$request->validate($this->rules($gedung));
        $lantaiTertinggi=$gedung->prasarana()->max('lantai');
        if($data['jumlah_lantai']!==null && $lantaiTertinggi!==null && (int)$data['jumlah_lantai']<(int)$lantaiTertinggi){
            throw ValidationException::withMessages(['jumlah_lantai'=>'Jumlah lantai tidak boleh lebih kecil dari lantai ruang tertinggi yang sudah tercatat (lantai '.$lantaiTertinggi.').']);
        }
        $data['is_dummy']=$request->boolean('is_dummy');$gedung->update($data);return redirect()->route('gedung.show',$gedung)->with('success','Data gedung diperbarui.');
    }
    public function destroy(Gedung $gedung){
        if($gedung->prasarana()->exists()) return back()->with('error','Gedung masih mempunyai ruang. Pindahkan ruangan terlebih dahulu.');
        $gedung->delete();return redirect()->route('gedung.index')->with('success','Gedung dihapus.');
    }
}

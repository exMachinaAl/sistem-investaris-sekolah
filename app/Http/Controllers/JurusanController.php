<?php
namespace App\Http\Controllers;
use App\Models\Jurusan;
use Illuminate\Http\Request;
class JurusanController {
    public function index(Request $request){
        $query=Jurusan::withCount('prasarana');
        if($q=trim((string)$request->query('q',''))){$query->where(fn($w)=>$w->where('nama','like',"%$q%")->orWhere('kode','like',"%$q%"));}
        $data=$query->orderBy('kode')->paginate(12)->withQueryString();
        return view('jurusan.index',compact('data'));
    }
    public function show(Jurusan $jurusan){$data=$jurusan->prasarana()->with('gedung')->orderBy('nama')->paginate(12);return view('jurusan.show',compact('jurusan','data'));}
    public function create(){return view('jurusan.form',['item'=>new Jurusan]);}
    public function edit(Jurusan $jurusan){return view('jurusan.form',['item'=>$jurusan]);}
    private function rules(?Jurusan $item=null):array{
        return ['nama'=>['required','string','max:160'],'is_dummy'=>['boolean']];
    }
    public function store(Request $request){$data=$request->validate($this->rules());Jurusan::create($data+['is_dummy'=>false]);return redirect()->route('jurusan.index')->with('success','Jurusan berhasil ditambahkan.');}
    public function update(Request $request,Jurusan $jurusan){$data=$request->validate($this->rules($jurusan));$data['is_dummy']=$request->boolean('is_dummy');$jurusan->update($data);return redirect()->route('jurusan.show',$jurusan)->with('success','Jurusan diperbarui.');}
    public function destroy(Jurusan $jurusan){
        if($jurusan->prasarana()->exists()) return back()->with('error','Jurusan masih terhubung dengan ruang. Lepaskan hubungan terlebih dahulu.');
        $jurusan->delete();return redirect()->route('jurusan.index')->with('success','Jurusan dihapus.');
    }
}

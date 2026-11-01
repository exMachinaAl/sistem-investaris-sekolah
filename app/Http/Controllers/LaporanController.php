<?php
namespace App\Http\Controllers;
use App\Models\Gedung;
use App\Models\Jurusan;
use App\Models\Prasarana;
use App\Models\Inventaris;
use Symfony\Component\HttpFoundation\StreamedResponse;
use InvalidArgumentException;
class LaporanController {
    public function index(){
        $perGedung=Gedung::withCount('prasarana')->orderBy('nama')->get();
        $perJurusan=Jurusan::withCount('prasarana')->orderBy('nama')->get();
        $tanpaGedung=Prasarana::whereNull('gedung_id')->count();
        $tanpaJurusan=Prasarana::whereDoesntHave('jurusan')->count();
        $kondisi=Inventaris::selectRaw('kondisi, SUM(jumlah) as jumlah')->groupBy('kondisi')->pluck('jumlah','kondisi');
        return view('laporan.index',compact('perGedung','perJurusan','tanpaGedung','tanpaJurusan','kondisi'));
    }
    private static function safe($value):string{
        $str=(string)($value??'');
        // Mencegah formula injection bila CSV dibuka di spreadsheet.
        return preg_match('/^[\s\x00-\x1f]*[=+@\-]/u',$str) ? "'".$str : $str;
    }
    public function export(string $jenis):StreamedResponse {
        if(!in_array($jenis,['gedung','jurusan','prasarana','inventaris'],true))throw new InvalidArgumentException('Jenis ekspor tidak tersedia');
        $filename='laporan-'.$jenis.'-'.date('Ymd-His').'.csv';
        return response()->streamDownload(function()use($jenis){
            $out=fopen('php://output','w');fwrite($out,"\xEF\xBB\xBF");
            $write=static function(array $row)use($out){fputcsv($out,array_map([self::class,'safe'],$row),';');};
            if($jenis==='gedung'){
                $write(['Kode','Nama','Jumlah Lantai','Jumlah Prasarana','Data Dummy','Keterangan']);
                Gedung::withCount('prasarana')->orderBy('kode')->chunk(200,function($items)use($write){foreach($items as $x)$write([$x->kode,$x->nama,$x->jumlah_lantai,$x->prasarana_count,$x->is_dummy?'YA':'TIDAK',$x->keterangan]);});
            }elseif($jenis==='jurusan'){
                $write(['Kode','Nama Jurusan','Jumlah Prasarana','Data Dummy']);
                Jurusan::withCount('prasarana')->orderBy('kode')->chunk(200,function($items)use($write){foreach($items as $x)$write([$x->kode,$x->nama,$x->prasarana_count,$x->is_dummy?'YA':'TIDAK']);});
            }elseif($jenis==='prasarana'){
                $write(['Kode','Nama','Kategori','Gedung','Jurusan','Lantai','Panjang (m)','Lebar (m)','Luas (m2)','Status Verifikasi','Status Kepemilikan','Keterangan Sumber','Sumber','Data Dummy']);
                Prasarana::with(['gedung','jurusan'])->orderBy('id')->chunkById(200,function($items)use($write){foreach($items as $x)$write([$x->kode,$x->nama,$x->kategori_sementara,$x->gedung?->nama,$x->jurusan->pluck('kode')->implode(', '),$x->lantai,$x->panjang,$x->lebar,$x->luas,$x->status_verifikasi,$x->status_kepemilikan,$x->keterangan_sumber,$x->sumber,$x->is_dummy?'YA':'TIDAK']);});
            }else{
                $write(['Kode','Nama Barang','Lokasi','Jumlah Unit','Kondisi','Tahun Perolehan','Data Dummy','Keterangan']);
                Inventaris::with('prasarana')->orderBy('id')->chunkById(200,function($items)use($write){foreach($items as $x)$write([$x->kode,$x->nama,$x->prasarana?->nama,$x->jumlah,$x->kondisi,$x->tahun_perolehan,$x->is_dummy?'YA':'TIDAK',$x->keterangan]);});
            }
            fclose($out);
        },$filename,['Content-Type'=>'text/csv; charset=UTF-8']);
    }
}

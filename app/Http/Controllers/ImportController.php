<?php
namespace App\Http\Controllers;

use App\Models\Gedung;
use App\Models\Inventaris;
use App\Models\Jurusan;
use App\Models\Prasarana;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportController {
    private const TTL_MINUTES = 30;
    private const MAX_ROWS = 1000;

    public function index() {
        return view('laporan.import', ['importTypes'=>$this->importTypes()]);
    }

    public function template(string $jenis): StreamedResponse {
        $types=$this->importTypes();
        abort_unless(isset($types[$jenis]),404);
        $definition=$types[$jenis];
        return response()->streamDownload(function()use($definition){
            $out=fopen('php://output','wb');
            fwrite($out,"\xEF\xBB\xBF");
            fputcsv($out,$definition['headers'],';');
            fputcsv($out,$definition['example'],';');
            fclose($out);
        },'template-import-'.$jenis.'.csv',['Content-Type'=>'text/csv; charset=UTF-8']);
    }

    public function gedung(Request $request) { return $this->preview($request,'gedung'); }
    public function jurusan(Request $request) { return $this->preview($request,'jurusan'); }
    public function prasarana(Request $request) { return $this->preview($request,'prasarana'); }
    public function inventaris(Request $request) { return $this->preview($request,'inventaris'); }

    /** Tahap 1: validasi seluruh CSV dan siapkan preview; belum mengubah database. */
    private function preview(Request $request,string $jenis) {
        $types=$this->importTypes();
        abort_unless(isset($types[$jenis]),404);
        $request->validate(['file'=>['required','file','mimes:csv,txt','max:4096']]);
        $rows=$this->parseCsv($request->file('file')->getRealPath(),$jenis);

        $baseline=[];
        $preview=[];
        $stats=['baru'=>0,'ubah'=>0,'sama'=>0];
        foreach($rows as $row){
            $existing=$this->findExisting($jenis,$row['kode']);
            if($existing instanceof Inventaris && $existing->trashed()){
                throw ValidationException::withMessages(['file'=>'Kode '.$row['kode'].' berada di arsip inventaris. Pulihkan atau hapus permanen data tersebut sebelum impor.']);
            }
            $baseline[$row['kode']]=$this->fingerprint($existing);
            $action=$existing===null?'baru':($this->hasChanges($existing,$row)?'ubah':'sama');
            $stats[$action]++;
            if(count($preview)<50)$preview[]=['kode'=>$row['kode'],'nama'=>$row['nama'],'status'=>$action];
        }

        $token=Str::random(48);
        Cache::put($this->cacheKey($token),[
            'user_id'=>$request->user()->id,
            'jenis'=>$jenis,
            'rows'=>$rows,
            'baseline'=>$baseline,
            'expires_at'=>now()->addMinutes(self::TTL_MINUTES)->timestamp,
        ],now()->addMinutes(self::TTL_MINUTES));

        return view('laporan.import',compact('preview','stats','token','jenis')+['importTypes'=>$types]);
    }

    /** Tahap 2: cek pemilik token dan konflik, lalu simpan seluruh baris secara atomik. */
    public function konfirmasi(Request $request) {
        $data=$request->validate([
            'token'=>['required','string','size:48','alpha_num'],
            'setuju'=>['required','accepted'],
        ]);
        $key=$this->cacheKey($data['token']);
        $payload=Cache::get($key);
        if(!$payload || ($payload['user_id']??null)!==$request->user()->id ||
            ($payload['expires_at']??0)<now()->timestamp ||
            !isset($this->importTypes()[$payload['jenis']??''])){
            throw ValidationException::withMessages(['file'=>'Preview kedaluwarsa atau tidak sesuai sesi. Unggah ulang CSV.']);
        }

        $count=DB::transaction(function()use($payload,$request){
            $count=0;
            foreach($payload['rows'] as $row){
                $existing=$this->findExisting($payload['jenis'],$row['kode'],true);
                if($existing instanceof Inventaris && $existing->trashed()){
                    throw ValidationException::withMessages(['file'=>'Kode '.$row['kode'].' kini berada di arsip inventaris. Unggah ulang CSV.']);
                }
                if($this->fingerprint($existing)!==($payload['baseline'][$row['kode']]??null)){
                    throw ValidationException::withMessages(['file'=>'Data '.$row['kode'].' berubah sejak preview. Unggah ulang agar perubahan pengguna lain tidak tertimpa.']);
                }
                if($existing===null){
                    $this->modelClass($payload['jenis'])::create($row);
                    $count++;
                }elseif($this->hasChanges($existing,$row)){
                    $oldLocation=$existing instanceof Inventaris?$existing->prasarana_id:null;
                    $existing->update($row);
                    if($existing instanceof Inventaris && array_key_exists('prasarana_id',$row) &&
                        (string)($oldLocation??'')!==(string)($row['prasarana_id']??'')){
                        $existing->mutasi()->create([
                            'prasarana_asal_id'=>$oldLocation,
                            'prasarana_tujuan_id'=>$row['prasarana_id'],
                            'user_id'=>$request->user()->id,
                            'jumlah'=>$existing->jumlah,
                            'tanggal'=>now()->toDateString(),
                            'catatan'=>'Perubahan lokasi melalui impor CSV.',
                        ]);
                    }
                    $count++;
                }
            }
            return $count;
        });

        Cache::forget($key);
        $jenis=$payload['jenis'];
        Log::info(ucfirst($jenis).' CSV import disetujui',[
            'admin_id'=>$request->user()->id,'jenis'=>$jenis,
            'total_baris'=>count($payload['rows']),'baris_berubah'=>$count,
        ]);
        return redirect()->route($jenis.'.index')->with(
            'success',$count.' dari '.count($payload['rows']).' baris '.strtolower($this->importTypes()[$jenis]['label']).' telah ditambah/diperbarui.'
        );
    }

    private function importTypes(): array {
        return [
            'gedung'=>[
                'label'=>'Gedung','description'=>'Data gedung dan jumlah lantai.',
                'headers'=>['kode','nama','jumlah_lantai','keterangan','is_dummy'],
                'example'=>['GD-CONTOH-001','Gedung Contoh','2','Hapus baris contoh ini sebelum impor','0'],
                'required'=>['kode','nama'],
            ],
            'jurusan'=>[
                'label'=>'Jurusan','description'=>'Kode dan nama kompetensi/jurusan.',
                'headers'=>['kode','nama','is_dummy'],
                'example'=>['JRS-CONTOH-001','Jurusan Contoh','0'],
                'required'=>['kode','nama'],
            ],
            'prasarana'=>[
                'label'=>'Prasarana','description'=>'Ruang, fasilitas, ukuran, gedung, dan status verifikasi.',
                'headers'=>['kode','nama','kategori_sementara','panjang','lebar','gedung_kode','lantai','status_verifikasi','status_kepemilikan','keterangan_sumber','is_dummy'],
                'example'=>['PRS-CONTOH-001','Ruang Kelas Contoh','Ruang Kelas','9','7','','1','belum','','Hapus baris contoh ini sebelum impor','0'],
                'required'=>['kode','nama'],
            ],
            'inventaris'=>[
                'label'=>'Inventaris Barang','description'=>'Barang, lokasi prasarana, jumlah, kondisi, dan tahun perolehan.',
                'headers'=>['kode','nama','lokasi_kode','jumlah','kondisi','tahun_perolehan','keterangan','is_dummy'],
                'example'=>['INV-CONTOH-001','Proyektor Contoh','','1','baik','2026','Hapus baris contoh ini sebelum impor','0'],
                'required'=>['kode','nama','jumlah','kondisi'],
            ],
        ];
    }

    private function modelClass(string $jenis): string {
        return match($jenis){
            'gedung'=>Gedung::class,
            'jurusan'=>Jurusan::class,
            'prasarana'=>Prasarana::class,
            'inventaris'=>Inventaris::class,
        };
    }

    private function findExisting(string $jenis,string $kode,bool $lock=false): ?Model {
        $class=$this->modelClass($jenis);
        $query=$jenis==='inventaris'?$class::withTrashed():$class::query();
        if($lock)$query->lockForUpdate();
        return $query->where('kode',$kode)->first();
    }

    private function cacheKey(string $token): string { return 'inventaris-import-preview:'.$token; }

    private function fingerprint(?Model $item): ?string {
        if($item===null)return null;
        $attributes=$item->getAttributes();
        ksort($attributes);
        return hash('sha256',json_encode($attributes,JSON_THROW_ON_ERROR));
    }

    private function hasChanges(Model $existing,array $row): bool {
        foreach($row as $key=>$value){
            $current=$existing->getRawOriginal($key);
            if((string)($current??'')!==(string)($value??''))return true;
        }
        return false;
    }

    private function parseCsv(string $path,string $jenis): array {
        $stream=fopen($path,'rb');
        if(!$stream)throw ValidationException::withMessages(['file'=>'File gagal dibaca.']);
        try{
            $first=fgets($stream);
            if($first===false)throw ValidationException::withMessages(['file'=>'File kosong.']);
            $first=preg_replace('/^\xEF\xBB\xBF/','',$first);
            $delimiter=substr_count($first,';')>substr_count($first,',')?';':',';
            $header=array_map(fn($x)=>trim(mb_strtolower((string)$x)),str_getcsv($first,$delimiter));
            $definition=$this->importTypes()[$jenis];
            if(count($header)!==count(array_unique($header))){
                throw ValidationException::withMessages(['file'=>'Header CSV mengandung kolom duplikat.']);
            }
            if(array_diff($definition['required'],$header) || array_diff($header,$definition['headers'])){
                throw ValidationException::withMessages(['file'=>'Header CSV '.$definition['label'].' tidak valid. Kolom wajib: '.implode(', ',$definition['required']).'.']);
            }

            $rows=[];$line=1;$codes=[];
            while(($parts=fgetcsv($stream,0,$delimiter,'"','\\'))!==false){
                $line++;
                if(count($parts)===1 && trim((string)$parts[0])==='')continue;
                if(count($rows)>=self::MAX_ROWS)throw ValidationException::withMessages(['file'=>'Maksimal '.number_format(self::MAX_ROWS,0,',','.').' baris per impor.']);
                if(count($parts)!==count($header))throw ValidationException::withMessages(['file'=>"Baris $line: jumlah kolom tidak sesuai header."]);
                $raw=array_combine($header,array_map(static fn($value)=>trim((string)$value),$parts));
                $raw=array_map(static fn($value)=>$value===''?null:$value,$raw);
                $row=$this->validateRow($raw,$jenis,$line);

                $codeKey=mb_strtolower($row['kode']);
                if(isset($codes[$codeKey]))throw ValidationException::withMessages(['file'=>"Baris $line: kode {$row['kode']} duplikat di file."]);
                $codes[$codeKey]=true;
                $row=$this->resolveRelations($row,$jenis,$line);
                if($jenis==='prasarana')$row['sumber']='import CSV';
                $rows[]=$row;
            }
            if(!$rows)throw ValidationException::withMessages(['file'=>'Tidak ada baris data untuk diimpor.']);
            return $rows;
        }finally{fclose($stream);}
    }

    private function validateRow(array $raw,string $jenis,int $line): array {
        $common=[
            'kode'=>['required','string','max:'.($jenis==='inventaris'?35:30)],
            'nama'=>['required','string','max:255'],
            'is_dummy'=>['sometimes','nullable',Rule::in(['0','1','ya','tidak','true','false'])],
        ];
        $rules=match($jenis){
            'gedung'=>$common+[
                'jumlah_lantai'=>['sometimes','nullable','integer','min:1','max:100'],
                'keterangan'=>['sometimes','nullable','string','max:10000'],
            ],
            'jurusan'=>$common,
            'prasarana'=>$common+[
                'kategori_sementara'=>['sometimes','nullable','string','max:80'],
                'panjang'=>['sometimes','nullable','numeric','min:0','max:999999'],
                'lebar'=>['sometimes','nullable','numeric','min:0','max:999999'],
                'gedung_kode'=>['sometimes','nullable','string','max:30'],
                'lantai'=>['sometimes','nullable','integer','min:0','max:100'],
                'status_verifikasi'=>['sometimes','nullable',Rule::in(['belum','terverifikasi','perlu_koreksi'])],
                'status_kepemilikan'=>['sometimes','nullable','string','max:100'],
                'keterangan_sumber'=>['sometimes','nullable','string','max:10000'],
            ],
            'inventaris'=>$common+[
                'lokasi_kode'=>['sometimes','nullable','string','max:30'],
                'jumlah'=>['required','integer','min:1','max:100000000'],
                'kondisi'=>['required',Rule::in(['baik','rusak_ringan','rusak_berat','hilang'])],
                'tahun_perolehan'=>['sometimes','nullable','integer','min:1900','max:2100'],
                'keterangan'=>['sometimes','nullable','string','max:10000'],
            ],
        };
        $validator=Validator::make($raw,$rules);
        if($validator->fails()){
            throw ValidationException::withMessages(['file'=>'Baris '.$line.': '.$validator->errors()->first()]);
        }
        $row=$validator->validated();
        if(array_key_exists('is_dummy',$row)){
            if($row['is_dummy']===null)unset($row['is_dummy']);
            else $row['is_dummy']=in_array($row['is_dummy'],['1','ya','true'],true);
        }
        if(array_key_exists('status_verifikasi',$row) && $row['status_verifikasi']===null)unset($row['status_verifikasi']);
        return $row;
    }

    private function resolveRelations(array $row,string $jenis,int $line): array {
        if($jenis==='prasarana' && array_key_exists('gedung_kode',$row)){
            $kode=$row['gedung_kode'];
            $row['gedung_id']=$kode===null?null:Gedung::where('kode',$kode)->value('id');
            if($kode!==null && !$row['gedung_id'])throw ValidationException::withMessages(['file'=>"Baris $line: kode gedung tidak dikenal: $kode."]);
            unset($row['gedung_kode']);
        }
        if($jenis==='inventaris' && array_key_exists('lokasi_kode',$row)){
            $kode=$row['lokasi_kode'];
            $row['prasarana_id']=$kode===null?null:Prasarana::where('kode',$kode)->value('id');
            if($kode!==null && !$row['prasarana_id'])throw ValidationException::withMessages(['file'=>"Baris $line: kode lokasi prasarana tidak dikenal: $kode."]);
            unset($row['lokasi_kode']);
        }
        return $row;
    }
}

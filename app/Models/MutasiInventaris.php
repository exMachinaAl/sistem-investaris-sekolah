<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MutasiInventaris extends Model {
    protected $table='mutasi_inventaris'; protected $guarded=[];
    protected function casts(): array {return ['tanggal'=>'date'];}
    public function asal(){return $this->belongsTo(Prasarana::class,'prasarana_asal_id');}
    public function tujuan(){return $this->belongsTo(Prasarana::class,'prasarana_tujuan_id');}
    public function user(){return $this->belongsTo(User::class,'user_id');}
}

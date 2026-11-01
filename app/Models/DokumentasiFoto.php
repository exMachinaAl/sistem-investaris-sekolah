<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DokumentasiFoto extends Model {
    protected $table='dokumentasi_foto';
    protected $guarded=[];

    protected function casts(): array {
        return ['tanggal_foto'=>'date'];
    }

    public function prasarana(){return $this->belongsTo(Prasarana::class,'prasarana_id');}
    public function inventaris(){return $this->belongsTo(Inventaris::class,'inventaris_id');}
    public function user(){return $this->belongsTo(User::class,'user_id');}
    public function getUrlAttribute(): string {return route('dokumentasi-foto.show',$this);}
}

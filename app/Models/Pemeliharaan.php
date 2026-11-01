<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Pemeliharaan extends Model {
    protected $table='pemeliharaan'; protected $guarded=[];
    protected function casts(): array {return ['tanggal'=>'date','biaya'=>'decimal:2'];}
}

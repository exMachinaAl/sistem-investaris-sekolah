<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Gedung extends Model {
    protected $table='gedung'; protected $guarded=[];
    protected function casts(): array { return ['is_dummy'=>'boolean']; }
    public function prasarana(): HasMany { return $this->hasMany(Prasarana::class,'gedung_id'); }
}

<?php
namespace App\Models;
use App\Models\Concerns\GeneratesAutomaticCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class Jurusan extends Model {
    use GeneratesAutomaticCode;
    protected $table='jurusan'; protected $guarded=[];
    protected function casts(): array { return ['is_dummy'=>'boolean']; }
    protected function automaticCodePrefix(): string { return 'JRS'; }
    public function prasarana(): BelongsToMany {return $this->belongsToMany(Prasarana::class,'prasarana_jurusan','jurusan_id','prasarana_id')->withPivot('keterangan');}
}

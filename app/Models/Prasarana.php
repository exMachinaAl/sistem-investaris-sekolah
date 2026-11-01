<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Prasarana extends Model {
    protected $table='prasarana'; protected $guarded=[];
    protected function casts(): array { return ['is_dummy'=>'boolean','panjang'=>'decimal:2','lebar'=>'decimal:2']; }
    public function gedung(): BelongsTo { return $this->belongsTo(Gedung::class,'gedung_id'); }
    public function jurusan(): BelongsToMany { return $this->belongsToMany(Jurusan::class,'prasarana_jurusan','prasarana_id','jurusan_id')->withPivot('keterangan'); }
    public function inventaris(): HasMany { return $this->hasMany(Inventaris::class,'prasarana_id'); }
    public function dokumentasiFoto(): HasMany { return $this->hasMany(DokumentasiFoto::class,'prasarana_id')->latest('tanggal_foto')->latest('id'); }
    public function getLuasAttribute(): ?float {return $this->panjang !== null && $this->lebar !== null ? round((float)$this->panjang*(float)$this->lebar,2):null;}
}

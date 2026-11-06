<?php
namespace App\Models;
use App\Models\Concerns\GeneratesAutomaticCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Inventaris extends Model {
    use GeneratesAutomaticCode, SoftDeletes;
    protected $table='inventaris'; protected $guarded=[];
    protected function casts(): array { return ['is_dummy'=>'boolean']; }
    protected function automaticCodePrefix(): string { return 'INV'; }
    public function prasarana(): BelongsTo {return $this->belongsTo(Prasarana::class,'prasarana_id');}
    public function mutasi(): HasMany {return $this->hasMany(MutasiInventaris::class,'inventaris_id')->latest('tanggal');}
    public function pemeliharaan(): HasMany {return $this->hasMany(Pemeliharaan::class,'inventaris_id')->latest('tanggal');}
    public function dokumentasiFoto(): HasMany {return $this->hasMany(DokumentasiFoto::class,'inventaris_id')->latest('tanggal_foto')->latest('id');}
}

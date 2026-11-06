<?php
namespace App\Models;
use App\Models\Concerns\GeneratesAutomaticCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Gedung extends Model {
    use GeneratesAutomaticCode;
    protected $table='gedung'; protected $guarded=[];
    protected function casts(): array { return ['is_dummy'=>'boolean','panjang'=>'decimal:2','lebar'=>'decimal:2','tanggal_pengukuran'=>'date']; }
    protected function automaticCodePrefix(): string { return 'GDG'; }
    public function prasarana(): HasMany { return $this->hasMany(Prasarana::class,'gedung_id'); }
    public function getLuasTapakAttribute(): ?float {return $this->panjang !== null && $this->lebar !== null ? round((float)$this->panjang*(float)$this->lebar,2):null;}
}

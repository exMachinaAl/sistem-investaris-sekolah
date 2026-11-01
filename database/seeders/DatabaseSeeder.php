<?php
namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
class DatabaseSeeder extends Seeder {
    public function run(): void {
        $name=env('SEED_ADMIN_NAME','Admin Sarpras');
        $email=env('SEED_ADMIN_EMAIL','admin@sekolah.test');
        $password=env('SEED_ADMIN_PASSWORD');
        // Seeder tidak boleh mereset kata sandi atau hak akses akun yang sudah ada.
        // Password hanya diperlukan saat membuat admin untuk pertama kalinya.
        if (!User::where('email',$email)->exists()) {
            if (!$password || strlen($password)<12) {
                throw new RuntimeException('Admin belum ada. Isi SEED_ADMIN_PASSWORD minimal 12 karakter pada .env untuk seeding pertama.');
            }
            User::firstOrCreate(['email'=>$email],['name'=>$name,'password'=>$password,'role'=>'admin']);
        }
        $data=json_decode(file_get_contents(database_path('data/prasarana.json')),true,512,JSON_THROW_ON_ERROR);
        foreach (array_chunk($data,100) as $chunk) {
            $items=array_map(static function ($row) {
                $row['status_verifikasi']='belum'; $row['is_dummy']=false;
                $row['created_at']=now(); $row['updated_at']=now();
                return $row;
            },$chunk);
            // Pada proses seed ulang, koreksi manual di data lama TIDAK ditimpa.
            DB::table('prasarana')->insertOrIgnore($items);
        }
        $this->command?->info('148 prasarana dari lampiran tersedia (tanpa menimpa koreksi manual).');
    }
}

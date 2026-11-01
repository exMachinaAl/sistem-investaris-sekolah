<?php
namespace Tests\Feature;

use App\Models\Inventaris;
use App\Models\Prasarana;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class UpgradeV11Test extends TestCase {
    use RefreshDatabase;

    private function pengguna(string $role, string $email='uji@sekolah.test'): User {
        return User::create(['name'=>'Pengguna Uji','email'=>$email,'password'=>'KataSandiUjiPanjang!123','role'=>$role]);
    }
    private function ruang(string $code='R-101'): Prasarana {
        return Prasarana::create(['kode'=>$code,'nama'=>'Ruang Uji','status_verifikasi'=>'belum']);
    }
    private function inventaris(Prasarana $ruang): Inventaris {
        return Inventaris::create(['kode'=>'AST-TEST-01','nama'=>'Proyektor Uji','prasarana_id'=>$ruang->id,'jumlah'=>2,'kondisi'=>'baik']);
    }
    private function csv(string $text): UploadedFile {
        return UploadedFile::fake()->createWithContent('ruang.csv',$text);
    }

    public function test_arsip_menyimpan_histori_mutasi_dan_pemeliharaan_dan_bisa_dipulihkan(): void {
        $admin=$this->pengguna('admin');
        $ruang=$this->ruang();
        $item=$this->inventaris($ruang);
        $other=$this->ruang('R-102');
        $item->mutasi()->create(['prasarana_asal_id'=>$ruang->id,'prasarana_tujuan_id'=>$other->id,
            'jumlah'=>2,'tanggal'=>'2026-10-17','user_id'=>$admin->id]);
        $item->pemeliharaan()->create(['tanggal'=>'2026-10-17','jenis'=>'Servis rutin','status'=>'selesai']);
        $this->actingAs($admin)->delete(route('inventaris.destroy',$item))->assertRedirect();
        $this->assertSoftDeleted('inventaris',['id'=>$item->id]);
        $this->assertDatabaseHas('mutasi_inventaris',['inventaris_id'=>$item->id]);
        $this->assertDatabaseHas('pemeliharaan',['inventaris_id'=>$item->id]);
        $this->get(route('inventaris.show',$item->id))->assertNotFound();
        $this->get(route('inventaris.arsip'))->assertOk()->assertSee('AST-TEST-01');
        $this->post(route('inventaris.pulihkan',$item->id))->assertRedirect(route('inventaris.show',$item->id));
        $this->assertDatabaseHas('inventaris',['id'=>$item->id,'deleted_at'=>null]);
    }

    public function test_prasarana_dengan_barang_arsip_atau_riwayat_tidak_dihapus(): void {
        $admin=$this->pengguna('admin');
        $room=$this->ruang();
        $item=$this->inventaris($room);
        $this->actingAs($admin)->delete(route('inventaris.destroy',$item))->assertRedirect();
        $this->delete(route('prasarana.destroy',$room))->assertRedirect();
        $this->assertDatabaseHas('prasarana',['id'=>$room->id]);
    }

    public function test_import_hanya_admin_dan_preview_tidak_menulis_database(): void {
        $petugas=$this->pengguna('petugas');
        $this->actingAs($petugas)->get(route('import.index'))->assertForbidden();
        $this->post(route('import.prasarana'),['file'=>$this->csv("kode,nama\nP-A,Ruang A\n")])->assertForbidden();
        $admin=$this->pengguna('admin','admin@uji.test');
        $this->actingAs($admin)->post(route('import.prasarana'),[
            'file'=>$this->csv("kode,nama\nP-A,Ruang A\n")
        ])->assertOk()->assertSee('Konfirmasi &amp; Simpan Perubahan',false);
        $this->assertDatabaseMissing('prasarana',['kode'=>'P-A']);
    }

    public function test_import_konfirmasi_menyimpan_hanya_setelah_persetujuan(): void {
        $admin=$this->pengguna('admin');
        $response=$this->actingAs($admin)->post(route('import.prasarana'),[
            'file'=>$this->csv("kode,nama\nP-B,Ruang B\n")
        ]);
        $response->assertOk();
        $html=$response->getContent();
        preg_match('/name="token" value="([a-zA-Z0-9]{48})"/', $html, $matches);
        $this->assertNotEmpty($matches[1]??null);
        $this->post(route('import.konfirmasi'),['token'=>$matches[1],'setuju'=>'1'])->assertRedirect(route('prasarana.index'));
        $this->assertDatabaseHas('prasarana',['kode'=>'P-B','nama'=>'Ruang B']);
        $this->post(route('import.konfirmasi'),['token'=>$matches[1],'setuju'=>'1'])->assertSessionHasErrors();
    }

    public function test_import_menolak_header_duplikat(): void {
        $this->actingAs($this->pengguna('admin'))->post(route('import.prasarana'),[
            'file'=>$this->csv("kode,nama,nama\nP-C,Ruang C,Ruang C\n")
        ])->assertSessionHasErrors();
        $this->assertDatabaseMissing('prasarana',['kode'=>'P-C']);
    }

    public function test_admin_arsip_dibatasi_role(): void {
        $this->actingAs($this->pengguna('petugas'))->get(route('inventaris.arsip'))->assertForbidden();
    }

    public function test_seed_ulang_tidak_mengganti_password_role_dan_nama_admin(): void {
        $admin=$this->pengguna('admin',env('SEED_ADMIN_EMAIL','admin@sekolah.test'));
        $admin->update(['name'=>'Admin Sudah Disesuaikan','password'=>'PasswordTetapAman!123']);
        $hash=$admin->fresh()->password;
        $this->seed(DatabaseSeeder::class);
        $after=$admin->fresh();
        $this->assertSame($hash,$after->password);
        $this->assertSame('Admin Sudah Disesuaikan',$after->name);
        $this->assertSame('admin',$after->role);
    }
}

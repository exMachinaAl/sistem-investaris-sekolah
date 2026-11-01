<?php
namespace Tests\Feature;
use App\Models\Gedung;
use App\Models\Prasarana;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class SarprasFeatureTest extends TestCase {
    use RefreshDatabase;
    private function user(string $role='admin'): User {
        return User::create([
            'name'=>'Petugas Uji','email'=>$role.'@testing.test',
            'password'=>'PasswordUjiYangKuat2026!','role'=>$role,
        ]);
    }
    public function test_login_diperlukan_untuk_dashboard():void {
        $this->get('/dashboard')->assertRedirect('/login');
    }
    public function test_admin_bisa_membuat_gedung_dan_prasarana():void {
        $this->actingAs($this->user());
        $this->post(route('gedung.store'),['kode'=>'TEST-GD','nama'=>'Gedung Uji','jumlah_lantai'=>2])->assertRedirect(route('gedung.index'));
        $gedung=Gedung::where('kode','TEST-GD')->firstOrFail();
        $this->post(route('prasarana.store'),['kode'=>'TEST-PRS','nama'=>'Ruang Uji','kategori_sementara'=>'Ruang Kelas','status_verifikasi'=>'belum','gedung_id'=>$gedung->id,'panjang'=>8,'lebar'=>7])->assertRedirect();
        $this->assertDatabaseHas('prasarana',['kode'=>'TEST-PRS','gedung_id'=>$gedung->id]);
        $this->assertEquals(56.0,Prasarana::where('kode','TEST-PRS')->firstOrFail()->luas);
    }
    public function test_akun_pimpinan_hanya_baca():void {
        $this->actingAs($this->user('pimpinan'));
        $this->get(route('gedung.index'))->assertOk();
        $this->post(route('gedung.store'),['kode'=>'GD-TIDAK','nama'=>'Tidak Boleh'])->assertForbidden();
        $this->assertDatabaseMissing('gedung',['kode'=>'GD-TIDAK']);
    }
    public function test_admin_dapat_mengunduh_laporan_csv():void {
        $this->actingAs($this->user());
        $this->get(route('laporan.export','prasarana'))->assertOk()->assertHeader('content-type','text/csv; charset=UTF-8');
    }
}

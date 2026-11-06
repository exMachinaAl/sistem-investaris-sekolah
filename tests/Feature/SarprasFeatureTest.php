<?php
namespace Tests\Feature;
use App\Models\Gedung;
use App\Models\Inventaris;
use App\Models\Jurusan;
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
        $this->post(route('gedung.store'),[
            'nama'=>'Gedung Uji','jumlah_lantai'=>2,'panjang'=>30,'lebar'=>12,
            'status_verifikasi'=>'terverifikasi','tanggal_pengukuran'=>'2026-10-20',
        ])->assertRedirect(route('gedung.index'));
        $gedung=Gedung::where('nama','Gedung Uji')->firstOrFail();
        $this->assertMatchesRegularExpression('/^GDG-\d{6}$/',$gedung->kode);
        $this->assertEquals(360.0,$gedung->luas_tapak);

        $this->post(route('prasarana.store'),[
            'nama'=>'Ruang Uji','kategori_sementara'=>'Ruang Kelas','status_verifikasi'=>'belum',
            'gedung_id'=>$gedung->id,'lantai'=>2,'panjang'=>8,'lebar'=>7,
        ])->assertRedirect();
        $ruang=Prasarana::where('nama','Ruang Uji')->firstOrFail();
        $this->assertMatchesRegularExpression('/^PRS-\d{6}$/',$ruang->kode);
        $this->assertSame($gedung->id,$ruang->gedung_id);
        $this->assertEquals(56.0,$ruang->luas);
    }
    public function test_kode_internal_dibuat_otomatis_dan_kode_impor_tetap_dipertahankan():void {
        $jurusan=Jurusan::create(['nama'=>'Teknik Komputer']);
        $barang=Inventaris::create(['nama'=>'Proyektor','jumlah'=>1,'kondisi'=>'baik']);
        $impor=Gedung::create(['kode'=>'GEDUNG-LAMA','nama'=>'Gedung Hasil Impor']);

        $this->assertMatchesRegularExpression('/^JRS-\d{6}$/',$jurusan->kode);
        $this->assertMatchesRegularExpression('/^INV-\d{6}$/',$barang->kode);
        $this->assertSame('GEDUNG-LAMA',$impor->kode);
    }
    public function test_lantai_ruang_harus_sesuai_dengan_jumlah_lantai_gedung():void {
        $this->actingAs($this->user());
        $gedung=Gedung::create(['nama'=>'Gedung Dua Lantai','jumlah_lantai'=>2]);

        $this->post(route('prasarana.store'),[
            'nama'=>'Ruang Tidak Valid','status_verifikasi'=>'belum',
            'gedung_id'=>$gedung->id,'lantai'=>3,
        ])->assertSessionHasErrors('lantai');

        $this->assertDatabaseMissing('prasarana',['nama'=>'Ruang Tidak Valid']);

        Prasarana::create(['nama'=>'Ruang Lantai Dua','gedung_id'=>$gedung->id,'lantai'=>2]);
        $this->put(route('gedung.update',$gedung),[
            'nama'=>$gedung->nama,'jumlah_lantai'=>1,'status_verifikasi'=>'belum',
        ])->assertSessionHasErrors('jumlah_lantai');
        $this->assertSame(2,$gedung->fresh()->jumlah_lantai);
    }
    public function test_akun_pimpinan_hanya_baca():void {
        $this->actingAs($this->user('pimpinan'));
        $this->get(route('gedung.index'))->assertOk();
        $this->post(route('gedung.store'),['nama'=>'Tidak Boleh','status_verifikasi'=>'belum'])->assertForbidden();
        $this->assertDatabaseMissing('gedung',['nama'=>'Tidak Boleh']);
    }
    public function test_admin_dapat_mengunduh_laporan_csv():void {
        $this->actingAs($this->user());
        $this->get(route('laporan.export','prasarana'))->assertOk()->assertHeader('content-type','text/csv; charset=UTF-8');
    }
}

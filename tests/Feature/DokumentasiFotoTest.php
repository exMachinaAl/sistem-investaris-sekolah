<?php
namespace Tests\Feature;

use App\Models\DokumentasiFoto;
use App\Models\Inventaris;
use App\Models\Prasarana;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DokumentasiFotoTest extends TestCase {
    use RefreshDatabase;

    private function user(string $role='admin',string $email='foto@testing.test'): User {
        return User::create([
            'name'=>'Petugas Dokumentasi','email'=>$email,
            'password'=>'PasswordFotoYangKuat!2026','role'=>$role,
        ]);
    }

    private function ruang(string $kode='R-FOTO'): Prasarana {
        return Prasarana::create(['kode'=>$kode,'nama'=>'Ruang Dokumentasi','status_verifikasi'=>'belum']);
    }

    public function test_petugas_dapat_mengunggah_dan_melihat_foto_ruangan_privat(): void {
        Storage::fake('local');
        $petugas=$this->user('petugas');
        $ruang=$this->ruang();

        $this->actingAs($petugas)->post(route('prasarana.foto.store',$ruang),[
            'foto'=>UploadedFile::fake()->image('ruang.jpg',800,600),
            'kategori'=>'ruangan',
            'jumlah_tercatat'=>24,
            'tanggal_foto'=>'2026-10-19',
            'keterangan'=>'Verifikasi kondisi ruang dan kursi.',
        ])->assertRedirect()->assertSessionHas('success');

        $foto=DokumentasiFoto::firstOrFail();
        $this->assertSame($ruang->id,$foto->prasarana_id);
        $this->assertSame(24,$foto->jumlah_tercatat);
        $this->assertSame($petugas->id,$foto->user_id);
        Storage::disk('local')->assertExists($foto->path);

        $this->get(route('dokumentasi-foto.show',$foto))
            ->assertOk()
            ->assertHeader('content-type','image/jpeg');
        $this->get(route('prasarana.show',$ruang))
            ->assertOk()
            ->assertSee('Dokumentasi Foto Ruangan')
            ->assertSee('24 unit tercatat');
    }

    public function test_foto_bukti_jumlah_inventaris_mewajibkan_jumlah_tercatat(): void {
        Storage::fake('local');
        $admin=$this->user();
        $ruang=$this->ruang();
        $item=Inventaris::create([
            'kode'=>'INV-FOTO','nama'=>'Kursi Siswa','prasarana_id'=>$ruang->id,
            'jumlah'=>20,'kondisi'=>'baik',
        ]);

        $this->actingAs($admin)->post(route('inventaris.foto.store',$item),[
            'foto'=>UploadedFile::fake()->image('kursi.png',640,480),
            'kategori'=>'jumlah_barang',
            'tanggal_foto'=>'2026-10-19',
        ])->assertSessionHasErrors('jumlah_tercatat');
        $this->assertDatabaseCount('dokumentasi_foto',0);

        $this->post(route('inventaris.foto.store',$item),[
            'foto'=>UploadedFile::fake()->image('kursi.png',640,480),
            'kategori'=>'jumlah_barang',
            'jumlah_tercatat'=>18,
            'tanggal_foto'=>'2026-10-19',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('dokumentasi_foto',[
            'inventaris_id'=>$item->id,'kategori'=>'jumlah_barang','jumlah_tercatat'=>18,
        ]);
    }

    public function test_penghapusan_foto_menghapus_data_dan_file(): void {
        Storage::fake('local');
        $admin=$this->user();
        $ruang=$this->ruang();
        $path=UploadedFile::fake()->image('hapus.webp',500,500)->store('dokumentasi/prasarana','local');
        $foto=$ruang->dokumentasiFoto()->create([
            'path'=>$path,'kategori'=>'kondisi','tanggal_foto'=>'2026-10-19','user_id'=>$admin->id,
        ]);

        $this->actingAs($admin)->delete(route('prasarana.foto.destroy',[$ruang,$foto]))
            ->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseMissing('dokumentasi_foto',['id'=>$foto->id]);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_pimpinan_hanya_dapat_melihat_dokumentasi(): void {
        Storage::fake('local');
        $pimpinan=$this->user('pimpinan','pimpinan-foto@testing.test');
        $ruang=$this->ruang();

        $this->actingAs($pimpinan)->post(route('prasarana.foto.store',$ruang),[
            'foto'=>UploadedFile::fake()->image('ruang.jpg',800,600),
            'kategori'=>'ruangan','tanggal_foto'=>'2026-10-19',
        ])->assertForbidden();
        $this->get(route('prasarana.show',$ruang))
            ->assertOk()
            ->assertSee('Dokumentasi Foto Ruangan')
            ->assertDontSee('Tambah Foto Ruangan');
    }

    public function test_menu_dokumentasi_menampilkan_ruang_tanpa_foto_sebagai_prioritas(): void {
        $admin=$this->user();
        $ruang=$this->ruang();
        $this->actingAs($admin)->get(route('dokumentasi.index'))
            ->assertOk()
            ->assertSee('Dokumentasi Foto')
            ->assertSee('Ruang Tanpa Foto')
            ->assertSee($ruang->nama)
            ->assertSee('0 foto')
            ->assertSee('Tambah / Lihat Foto');
    }

    public function test_file_bukan_gambar_ditolak(): void {
        Storage::fake('local');
        $this->actingAs($this->user())->post(route('prasarana.foto.store',$this->ruang()),[
            'foto'=>UploadedFile::fake()->createWithContent('bukan-gambar.jpg','isi teks'),
            'kategori'=>'ruangan','tanggal_foto'=>'2026-10-19',
        ])->assertSessionHasErrors('foto');
        $this->assertDatabaseCount('dokumentasi_foto',0);
    }
}

<?php
namespace Tests\Feature;

use App\Models\Inventaris;
use App\Models\Prasarana;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CsvImportExportTest extends TestCase {
    use RefreshDatabase;

    private function admin(): User {
        return User::create([
            'name'=>'Admin CSV',
            'email'=>'admin-csv@testing.test',
            'password'=>'PasswordUjiCSV!2026',
            'role'=>'admin',
        ]);
    }

    private function csv(string $name,string $content): UploadedFile {
        return UploadedFile::fake()->createWithContent($name,$content);
    }

    private function confirmPreview(string $route,string $csv): void {
        $response=$this->post(route($route),['file'=>$this->csv('data.csv',$csv)]);
        $response->assertOk()->assertSee('Konfirmasi &amp; Simpan Perubahan',false);
        preg_match('/name="token" value="([a-zA-Z0-9]{48})"/',$response->getContent(),$matches);
        $this->assertNotEmpty($matches[1]??null);
        $this->post(route('import.konfirmasi'),['token'=>$matches[1],'setuju'=>'1'])->assertRedirect();
    }

    public function test_admin_dapat_mengunduh_template_semua_jenis_data(): void {
        $this->actingAs($this->admin());
        foreach([
            'gedung'=>'kode;nama;jumlah_lantai;keterangan;is_dummy',
            'jurusan'=>'kode;nama;is_dummy',
            'prasarana'=>'kode;nama;kategori_sementara;panjang;lebar;gedung_kode',
            'inventaris'=>'kode;nama;lokasi_kode;jumlah;kondisi;tahun_perolehan',
        ] as $jenis=>$header){
            $response=$this->get(route('import.template',$jenis));
            $response->assertOk()->assertHeader('content-type','text/csv; charset=UTF-8');
            $this->assertStringContainsString($header,$response->streamedContent());
        }
    }

    public function test_import_empat_jenis_data_menggunakan_template_dan_konfirmasi(): void {
        $this->actingAs($this->admin());
        $this->confirmPreview('import.gedung',"kode;nama;jumlah_lantai\nGD-CSV;Gedung CSV;3\n");
        $this->confirmPreview('import.jurusan',"kode,nama\nJRS-CSV,Jurusan CSV\n");
        $this->confirmPreview('import.prasarana',"kode;nama;gedung_kode;status_verifikasi\nPRS-CSV;Ruang CSV;GD-CSV;terverifikasi\n");
        $this->confirmPreview('import.inventaris',"kode;nama;lokasi_kode;jumlah;kondisi;tahun_perolehan\nINV-CSV;Laptop CSV;PRS-CSV;5;baik;2026\n");

        $this->assertDatabaseHas('gedung',['kode'=>'GD-CSV','jumlah_lantai'=>3]);
        $this->assertDatabaseHas('jurusan',['kode'=>'JRS-CSV','nama'=>'Jurusan CSV']);
        $ruang=Prasarana::where('kode','PRS-CSV')->firstOrFail();
        $this->assertDatabaseHas('inventaris',[
            'kode'=>'INV-CSV','prasarana_id'=>$ruang->id,'jumlah'=>5,'kondisi'=>'baik',
        ]);
    }

    public function test_perubahan_lokasi_inventaris_dari_csv_membuat_riwayat_mutasi(): void {
        $this->actingAs($admin=$this->admin());
        $asal=Prasarana::create(['kode'=>'R-ASAL','nama'=>'Ruang Asal']);
        $tujuan=Prasarana::create(['kode'=>'R-TUJUAN','nama'=>'Ruang Tujuan']);
        $item=Inventaris::create([
            'kode'=>'INV-PINDAH','nama'=>'Barang Pindah','prasarana_id'=>$asal->id,
            'jumlah'=>2,'kondisi'=>'baik',
        ]);

        $this->confirmPreview(
            'import.inventaris',
            "kode;nama;lokasi_kode;jumlah;kondisi\nINV-PINDAH;Barang Pindah;R-TUJUAN;2;baik\n"
        );

        $this->assertSame($tujuan->id,$item->fresh()->prasarana_id);
        $this->assertDatabaseHas('mutasi_inventaris',[
            'inventaris_id'=>$item->id,
            'prasarana_asal_id'=>$asal->id,
            'prasarana_tujuan_id'=>$tujuan->id,
            'user_id'=>$admin->id,
            'jumlah'=>2,
        ]);
    }

    public function test_import_menolak_kode_referensi_yang_tidak_dikenal(): void {
        $this->actingAs($this->admin())
            ->post(route('import.inventaris'),[
                'file'=>$this->csv('barang.csv',"kode,nama,lokasi_kode,jumlah,kondisi\nINV-X,Barang X,TIDAK-ADA,1,baik\n"),
            ])
            ->assertSessionHasErrors('file');
        $this->assertDatabaseMissing('inventaris',['kode'=>'INV-X']);
    }
}

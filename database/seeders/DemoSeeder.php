<?php
namespace Database\Seeders;
use App\Models\Gedung;
use App\Models\Jurusan;
use App\Models\Prasarana;
use App\Models\Inventaris;
use Illuminate\Database\Seeder;
class DemoSeeder extends Seeder {
    public function run(): void {
        foreach ([['GD-D01','Gedung A / Kelas',2],['GD-D02','Gedung B / Praktik',1],['GD-D03','Gedung C / Laboratorium',2],['GD-D04','Gedung Administrasi',1],['GD-D05','Gedung Serbaguna',1]] as [$kode,$nama,$lantai]) {
            Gedung::firstOrCreate(['kode'=>$kode],['nama'=>$nama,'jumlah_lantai'=>$lantai,'keterangan'=>'DATA DUMMY — silakan ganti setelah survei','is_dummy'=>true]);
        }
        foreach ([['APL','Analisis Pengujian Laboratorium'],['KA','Kimia Analisis'],['KI','Kimia Industri'],['MM','Multimedia'],['TB','Tata Boga'],['TITL','Teknik Instalasi Tenaga Listrik'],['TKJ','Teknik Komputer dan Jaringan'],['TOI','Teknik Otomasi Industri'],['TPTU','Teknik Pendinginan dan Tata Udara']] as [$kode,$nama]) {
            Jurusan::firstOrCreate(['kode'=>$kode],['nama'=>$nama,'is_dummy'=>true]);
        }
        $demo=[['AST-D001','Meja Siswa','PRS-105',24,'baik'],['AST-D002','Kursi Siswa','PRS-105',24,'baik'],['AST-D003','Papan Tulis','PRS-105',1,'baik'],['AST-D004','Proyektor','PRS-105',1,'rusak_ringan'],['AST-D005','PC Laboratorium','PRS-061',20,'baik'],['AST-D006','Monitor','PRS-061',20,'baik'],['AST-D007','Switch Jaringan','PRS-062',2,'baik'],['AST-D008','AC','PRS-056',2,'baik'],['AST-D009','Meja Praktik','PRS-076',10,'baik'],['AST-D010','Printer','PRS-045',2,'rusak_berat'],['AST-D011','Kursi Guru','PRS-043',20,'baik'],['AST-D012','Lemari Arsip','PRS-045',3,'baik']];
        foreach($demo as [$kode,$nama,$lokasi,$jumlah,$kondisi]){
            $id=Prasarana::where('kode',$lokasi)->value('id');
            Inventaris::firstOrCreate(['kode'=>$kode],['nama'=>$nama,'prasarana_id'=>$id,'jumlah'=>$jumlah,'kondisi'=>$kondisi,'keterangan'=>'DATA DUMMY — tidak mewakili stok riil','is_dummy'=>true]);
        }
        $this->command?->warn('Data gedung, jurusan dan barang demo ditambahkan. Semua ditandai sebagai DUMMY.');
    }
}

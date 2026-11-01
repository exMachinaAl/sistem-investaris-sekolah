<?php
namespace App\Http\Controllers;
use App\Models\Gedung;
use App\Models\Jurusan;
use App\Models\Prasarana;
use App\Models\Inventaris;
class DashboardController {
    public function __invoke(){
        $stats=[
            'gedung'=>Gedung::count(),
            'prasarana'=>Prasarana::count(),
            'jurusan'=>Jurusan::count(),
            'jenis_barang'=>Inventaris::count(),
            'unit_barang'=>Inventaris::sum('jumlah'),
            'belum_lokasi'=>Prasarana::whereNull('gedung_id')->count(),
            'belum_verifikasi'=>Prasarana::where('status_verifikasi','!=','terverifikasi')->count(),
            'rusak'=>Inventaris::whereIn('kondisi',['rusak_ringan','rusak_berat'])->sum('jumlah'),
            'gedung_dummy'=>Gedung::where('is_dummy',true)->count(),
            'barang_dummy'=>Inventaris::where('is_dummy',true)->count(),
        ];
        $kategori=Prasarana::selectRaw('kategori_sementara as kategori, COUNT(*) as jumlah')
            ->groupBy('kategori_sementara')->orderByDesc('jumlah')->limit(7)->get();
        $gedung=Gedung::withCount('prasarana')->orderBy('kode')->limit(7)->get();
        $terbaru=Prasarana::with('gedung')->latest('updated_at')->limit(6)->get();
        return view('dashboard.index',compact('stats','kategori','gedung','terbaru'));
    }
}

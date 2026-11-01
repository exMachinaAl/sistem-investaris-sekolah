<?php
namespace App\Http\Controllers;

use App\Models\DokumentasiFoto;
use App\Models\Inventaris;
use App\Models\Prasarana;

class DokumentasiController {
    public function index(){
        $ruang=Prasarana::with('gedung')->withCount('dokumentasiFoto')
            ->orderBy('dokumentasi_foto_count')->orderBy('nama')
            ->paginate(10,['*'],'ruang_page')->withQueryString();
        $barang=Inventaris::with('prasarana')->withCount('dokumentasiFoto')
            ->orderBy('dokumentasi_foto_count')->orderBy('nama')
            ->paginate(10,['*'],'barang_page')->withQueryString();
        $ringkasan=[
            'foto_ruang'=>DokumentasiFoto::whereNotNull('prasarana_id')->count(),
            'ruang_tanpa_foto'=>Prasarana::whereDoesntHave('dokumentasiFoto')->count(),
            'foto_barang'=>DokumentasiFoto::whereNotNull('inventaris_id')->count(),
            'barang_tanpa_foto'=>Inventaris::whereDoesntHave('dokumentasiFoto')->count(),
        ];
        return view('dokumentasi.index',compact('ruang','barang','ringkasan'));
    }
}

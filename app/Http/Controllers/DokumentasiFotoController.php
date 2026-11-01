<?php
namespace App\Http\Controllers;

use App\Models\DokumentasiFoto;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DokumentasiFotoController {
    public function show(DokumentasiFoto $foto): BinaryFileResponse {
        abort_unless($foto->prasarana_id || $foto->inventaris_id,404);
        $disk=Storage::disk('local');
        abort_unless($disk->exists($foto->path),404);
        return response()->file($disk->path($foto->path),[
            'Content-Type'=>$disk->mimeType($foto->path)?:'application/octet-stream',
            'Cache-Control'=>'private, max-age=3600',
            'X-Content-Type-Options'=>'nosniff',
        ]);
    }
}

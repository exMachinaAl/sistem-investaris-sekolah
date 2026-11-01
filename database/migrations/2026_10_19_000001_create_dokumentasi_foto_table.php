<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('dokumentasi_foto',function(Blueprint $table){
            $table->id();
            $table->foreignId('prasarana_id')->nullable()->constrained('prasarana')->cascadeOnDelete();
            $table->foreignId('inventaris_id')->nullable()->constrained('inventaris')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('path',500);
            $table->enum('kategori',['ruangan','jumlah_barang','ketersediaan','kondisi']);
            $table->unsignedInteger('jumlah_tercatat')->nullable();
            $table->date('tanggal_foto');
            $table->string('keterangan',500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('dokumentasi_foto');
    }
};

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('gedung',function(Blueprint $table){
            $table->id(); $table->string('kode',30)->unique(); $table->string('nama',160);
            $table->unsignedSmallInteger('jumlah_lantai')->nullable(); $table->text('keterangan')->nullable();
            $table->boolean('is_dummy')->default(false); $table->timestamps();
        });
        Schema::create('jurusan',function(Blueprint $table){
            $table->id(); $table->string('kode',30)->unique(); $table->string('nama',160);
            $table->boolean('is_dummy')->default(false); $table->timestamps();
        });
        Schema::create('prasarana',function(Blueprint $table){
            $table->id(); $table->unsignedSmallInteger('nomor_sumber')->unique()->nullable();
            $table->string('kode',30)->unique(); $table->string('nama',255);
            $table->string('kategori_sementara',80)->nullable();
            $table->foreignId('gedung_id')->nullable()->constrained('gedung')->nullOnDelete();
            $table->unsignedSmallInteger('lantai')->nullable();
            $table->text('keterangan_sumber')->nullable();
            $table->decimal('panjang',10,2)->nullable(); $table->decimal('lebar',10,2)->nullable();
            $table->string('status_kepemilikan',100)->nullable();
            $table->enum('status_verifikasi',['belum','terverifikasi','perlu_koreksi'])->default('belum');
            $table->string('sumber',200)->nullable(); $table->boolean('is_dummy')->default(false);
            $table->timestamps(); $table->index('kategori_sementara');
        });
        Schema::create('prasarana_jurusan',function(Blueprint $table){
            $table->foreignId('prasarana_id')->constrained('prasarana')->cascadeOnDelete();
            $table->foreignId('jurusan_id')->constrained('jurusan')->cascadeOnDelete();
            $table->string('keterangan',200)->nullable(); $table->primary(['prasarana_id','jurusan_id']);
        });
        Schema::create('inventaris',function(Blueprint $table){
            $table->id(); $table->string('kode',35)->unique(); $table->string('nama',255);
            $table->foreignId('prasarana_id')->nullable()->constrained('prasarana')->nullOnDelete();
            $table->unsignedInteger('jumlah')->default(1);
            $table->enum('kondisi',['baik','rusak_ringan','rusak_berat','hilang'])->default('baik');
            $table->unsignedSmallInteger('tahun_perolehan')->nullable();
            $table->text('keterangan')->nullable(); $table->boolean('is_dummy')->default(false); $table->timestamps();
        });
        Schema::create('mutasi_inventaris',function(Blueprint $table){
            $table->id(); $table->foreignId('inventaris_id')->constrained('inventaris')->cascadeOnDelete();
            $table->foreignId('prasarana_asal_id')->nullable()->constrained('prasarana')->nullOnDelete();
            $table->foreignId('prasarana_tujuan_id')->nullable()->constrained('prasarana')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('jumlah'); $table->date('tanggal'); $table->text('catatan')->nullable();
            $table->timestamps();
        });
        Schema::create('pemeliharaan',function(Blueprint $table){
            $table->id(); $table->foreignId('inventaris_id')->constrained('inventaris')->cascadeOnDelete();
            $table->date('tanggal'); $table->string('jenis',150);
            $table->enum('status',['dijadwalkan','diproses','selesai'])->default('dijadwalkan');
            $table->decimal('biaya',14,2)->nullable(); $table->text('catatan')->nullable(); $table->timestamps();
        });
    }
    public function down(): void {
        foreach(['pemeliharaan','mutasi_inventaris','inventaris','prasarana_jurusan','prasarana','jurusan','gedung'] as $table){Schema::dropIfExists($table);}
    }
};

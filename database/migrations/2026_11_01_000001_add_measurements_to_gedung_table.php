<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('gedung', function (Blueprint $table) {
            $table->decimal('panjang', 10, 2)->nullable()->after('jumlah_lantai');
            $table->decimal('lebar', 10, 2)->nullable()->after('panjang');
            $table->enum('status_verifikasi', ['belum', 'terverifikasi', 'perlu_koreksi'])
                ->default('belum')->after('lebar');
            $table->date('tanggal_pengukuran')->nullable()->after('status_verifikasi');
            $table->string('sumber_ukuran', 200)->nullable()->after('tanggal_pengukuran');
        });
    }

    public function down(): void
    {
        Schema::table('gedung', function (Blueprint $table) {
            $table->dropColumn([
                'panjang',
                'lebar',
                'status_verifikasi',
                'tanggal_pengukuran',
                'sumber_ukuran',
            ]);
        });
    }
};

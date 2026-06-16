<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemeliharaan', function (Blueprint $table) {
            $table->id();
            $table->string('alat', 200);
            $table->date('tanggal');
            $table->string('petugas', 150)->nullable();
            $table->enum('status', ['Dijadwalkan', 'Sedang Berjalan', 'Selesai'])->default('Dijadwalkan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemeliharaan');
    }
};

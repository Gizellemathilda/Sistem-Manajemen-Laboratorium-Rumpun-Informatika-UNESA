<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kerusakan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('inventaris_id')->constrained('inventaris')->cascadeOnDelete();
            $table->text('deskripsi');
            $table->enum('tingkat', ['Ringan', 'Sedang', 'Berat'])->default('Ringan');
            $table->string('foto')->nullable();
            $table->enum('status', ['Menunggu Teknisi', 'Dalam Perbaikan', 'Selesai'])->default('Menunggu Teknisi');
            $table->text('catatan_tangani')->nullable();
            $table->foreignId('ditangani_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kerusakan');
    }
};

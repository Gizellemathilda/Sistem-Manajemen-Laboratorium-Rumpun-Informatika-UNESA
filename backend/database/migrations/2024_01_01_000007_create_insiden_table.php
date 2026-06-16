<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insiden', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul', 200);
            $table->text('deskripsi');
            $table->string('lokasi', 150)->nullable();
            $table->enum('tingkat', ['Rendah', 'Sedang', 'Tinggi'])->default('Rendah');
            $table->string('foto')->nullable();
            $table->enum('status', ['Dalam Penanganan', 'Ditangani', 'Selesai'])->default('Dalam Penanganan');
            $table->text('catatan_tangani')->nullable();
            $table->foreignId('ditangani_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insiden');
    }
};

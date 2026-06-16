<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('inventaris_id')->constrained('inventaris')->cascadeOnDelete();
            $table->integer('jumlah')->default(1);
            $table->date('tgl_pinjam');
            $table->date('tgl_kembali')->nullable();
            $table->date('tgl_dikembalikan')->nullable();
            $table->text('keperluan')->nullable();
            $table->enum('status', ['Menunggu Validasi', 'Disetujui', 'Ditolak', 'Selesai'])->default('Menunggu Validasi');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman');
    }
};

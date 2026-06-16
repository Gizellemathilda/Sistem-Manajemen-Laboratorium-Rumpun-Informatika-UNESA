<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Kolom tgl_pinjam dan tgl_kembali sudah ada di create_peminjaman_table
        // Migration ini tidak diperlukan
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No operation - columns already defined in initial migration
    }
};

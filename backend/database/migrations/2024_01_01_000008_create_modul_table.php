<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modul', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul', 200);
            $table->string('mata_kuliah', 150)->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('nama_file', 255);
            $table->string('mime_type', 100);
            $table->integer('ukuran')->unsigned()->comment('ukuran dalam KB');
            $table->longText('data_base64')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modul');
    }
};

<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Pemeliharaan extends Model {
    protected $table = 'pemeliharaan';
    protected $fillable = ['alat','tanggal','petugas','status'];
}

<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Notifikasi extends Model {
    protected $table = 'notifikasi';
    protected $fillable = ['user_id','judul','pesan','tipe','dibaca','dibaca_at'];
    protected $casts = ['dibaca' => 'boolean','dibaca_at' => 'datetime'];
}

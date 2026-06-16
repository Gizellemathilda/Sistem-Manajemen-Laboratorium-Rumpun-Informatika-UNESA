<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Modul extends Model {
    protected $table = 'modul';
    protected $fillable = ['user_id','judul','mata_kuliah','deskripsi','nama_file','mime_type','ukuran','data_base64'];
    public function uploader() { return $this->belongsTo(User::class, 'user_id'); }
}

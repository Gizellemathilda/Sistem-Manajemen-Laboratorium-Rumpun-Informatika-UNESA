<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Insiden extends Model {
    protected $table = 'insiden';
    protected $fillable = ['user_id','judul','deskripsi','lokasi','tingkat','foto','status','catatan_tangani','ditangani_oleh'];
    public function user() { return $this->belongsTo(User::class); }
}

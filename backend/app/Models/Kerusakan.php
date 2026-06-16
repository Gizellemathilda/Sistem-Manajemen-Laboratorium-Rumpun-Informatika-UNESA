<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Kerusakan extends Model {
    protected $table = 'kerusakan';
    protected $fillable = ['user_id','inventaris_id','deskripsi','tingkat','foto','status','catatan_tangani','ditangani_oleh'];
    public function user() { return $this->belongsTo(User::class); }
    public function inventaris() { return $this->belongsTo(Inventaris::class); }
}

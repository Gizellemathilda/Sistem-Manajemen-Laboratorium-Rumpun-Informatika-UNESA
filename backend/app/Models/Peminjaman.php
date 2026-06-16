<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Peminjaman extends Model {
    protected $table = 'peminjaman';
    protected $fillable = ['user_id','inventaris_id','jumlah','tgl_pinjam','tgl_kembali','tgl_dikembalikan','keperluan','status','catatan'];
    public function user() { return $this->belongsTo(User::class); }
    public function inventaris() { return $this->belongsTo(Inventaris::class); }
}

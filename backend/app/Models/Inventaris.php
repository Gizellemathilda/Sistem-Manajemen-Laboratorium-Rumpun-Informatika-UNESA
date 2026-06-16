<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Inventaris extends Model {
    protected $table = 'inventaris';
    protected $fillable = ['nama','kategori','jumlah','kondisi','ruangan_id','keterangan'];
    public function ruangan() { return $this->belongsTo(Ruangan::class); }
}

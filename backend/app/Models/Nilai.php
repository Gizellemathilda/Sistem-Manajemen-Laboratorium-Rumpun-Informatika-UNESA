<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Nilai extends Model {
    protected $table = 'nilai';
    protected $fillable = ['mahasiswa','matkul','user_id','tugas','uts','uas'];
    protected $appends = ['akhir'];
    public function getAkhirAttribute() {
        return round(($this->tugas*0.3) + ($this->uts*0.3) + ($this->uas*0.4), 2);
    }
}

<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Jadwal extends Model {
    protected $table = 'jadwal';
    protected $fillable = ['hari','jam','matkul','ruang','dosen'];
}

<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use Illuminate\Http\Request;

class JadwalController extends Controller {
    public function index() { return Jadwal::orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat')")->orderBy('jam')->get(); }
    public function store(Request $r) {
        return Jadwal::create($r->validate([
            'hari'=>'required','jam'=>'required','matkul'=>'required','ruang'=>'required','dosen'=>'nullable',
        ]));
    }
    public function destroy($id) { Jadwal::destroy($id); return response()->noContent(); }
}

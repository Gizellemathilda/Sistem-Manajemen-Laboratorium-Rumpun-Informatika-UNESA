<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use Illuminate\Http\Request;

class AbsensiController extends Controller {
    public function index() { return Absensi::orderByDesc('tanggal')->get(); }
    public function store(Request $r) {
        return Absensi::create($r->validate([
            'tanggal'=>'required','matkul'=>'required','mahasiswa'=>'required',
            'user_id'=>'nullable','status'=>'required',
        ]));
    }
    public function update(Request $r, $id) {
        $a = Absensi::findOrFail($id);
        $a->update($r->only('status'));
        return $a;
    }
}

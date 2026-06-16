<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Matkul;
use Illuminate\Http\Request;

class MatkulController extends Controller {
    public function index() { return Matkul::orderBy('kode')->get(); }
    public function store(Request $r) {
        $d = $r->validate(['kode'=>'required','nama'=>'required','sks'=>'required|integer','dosen'=>'nullable']);
        return Matkul::create($d);
    }
    public function update(Request $r, $id) {
        $m = Matkul::findOrFail($id);
        $m->update($r->only('kode','nama','sks','dosen'));
        return $m;
    }
    public function destroy($id) { Matkul::destroy($id); return response()->noContent(); }
}

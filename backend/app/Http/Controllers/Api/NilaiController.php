<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Nilai;
use Illuminate\Http\Request;

class NilaiController extends Controller {
    public function index() { return Nilai::orderBy('matkul')->get(); }
    public function store(Request $r) {
        return Nilai::create($r->validate([
            'mahasiswa'=>'required','matkul'=>'required',
            'user_id'=>'nullable','tugas'=>'numeric','uts'=>'numeric','uas'=>'numeric',
        ]));
    }
    public function update(Request $r, $id) {
        $n = Nilai::findOrFail($id);
        $n->update($r->only('tugas','uts','uas','mahasiswa','matkul'));
        return $n;
    }
}

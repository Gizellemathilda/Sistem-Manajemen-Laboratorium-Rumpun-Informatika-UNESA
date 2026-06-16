<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use Illuminate\Http\Request;

class RuanganController extends Controller {
    private function authorizeRoomAccess(Request $r)
    {
        if (!in_array($r->user()->role, ['aslab', 'dosen', 'admin'])) {
            abort(403, 'Hanya aslab, dosen, dan admin yang dapat mengubah data ruangan.');
        }
    }

    public function index() { return Ruangan::orderBy('nama')->get(); }
    public function store(Request $r) {
        $this->authorizeRoomAccess($r);
        $d = $r->validate(['nama'=>'required','lokasi'=>'nullable','kapasitas'=>'integer']);
        $d['status'] = 'Tersedia';
        return Ruangan::create($d);
    }
    public function update(Request $r, $id) {
        $this->authorizeRoomAccess($r);
        $rn = Ruangan::findOrFail($id);
        $data = $r->validate([
            'nama' => 'sometimes|required',
            'lokasi' => 'sometimes|nullable',
            'kapasitas' => 'sometimes|integer',
            'status' => 'sometimes|in:Tersedia,Terpakai,Tidak Tersedia',
        ]);
        $rn->update($data);
        return $rn;
    }
    public function toggle(Request $r, $id) {
        $this->authorizeRoomAccess($r);
        $rn = Ruangan::findOrFail($id);
        $rn->status = $rn->status === 'Tersedia' ? 'Terpakai' : 'Tersedia';
        $rn->save();
        return $rn;
    }
    public function destroy(Request $r, $id) {
        $this->authorizeRoomAccess($r);
        Ruangan::destroy($id);
        return response()->noContent();
    }
}

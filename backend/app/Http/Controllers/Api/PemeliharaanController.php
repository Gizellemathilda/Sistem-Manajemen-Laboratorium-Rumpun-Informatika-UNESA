<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Pemeliharaan;
use Illuminate\Http\Request;

class PemeliharaanController extends Controller {
    public function index() { return Pemeliharaan::orderByDesc('tanggal')->get(); }
    public function store(Request $r) {
        $d = $r->validate(['alat'=>'required','tanggal'=>'required|date','petugas'=>'required']);
        $d['status'] = 'Dijadwalkan';
        return Pemeliharaan::create($d);
    }
    public function selesai($id) {
        $p = Pemeliharaan::findOrFail($id);
        $p->status = 'Selesai'; $p->save();
        return $p;
    }
}

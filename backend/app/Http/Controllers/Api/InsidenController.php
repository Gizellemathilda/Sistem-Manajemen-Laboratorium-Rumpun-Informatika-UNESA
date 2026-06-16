<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Insiden;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\Request;

class InsidenController extends Controller
{
    public function index(Request $request)
    {
        $q = Insiden::with('user');
        if (in_array($request->user()->role, ['mahasiswa','asprak'])) {
            $q->where('user_id', $request->user()->id);
        }
        return $q->latest()->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'judul'      => 'required|string|max:160',
            'deskripsi'  => 'required|string',
            'lokasi'     => 'required|string|max:120',
            'tingkat'    => 'required|in:Rendah,Sedang,Berat',
            'foto'       => 'nullable|string',
        ]);
        $data['user_id'] = $request->user()->id;
        $data['status']  = 'Dalam Penanganan';
        $i = Insiden::create($data);

        Notifikasi::create([
            'user_id' => $request->user()->id,
            'judul'   => 'Laporan insiden terkirim',
            'pesan'   => 'Laporan insiden Anda telah diterima.',
            'tipe'    => 'insiden',
        ]);

        $petugas = User::whereIn('role', ['aslab','admin'])->pluck('id');
        foreach ($petugas as $uid) {
            Notifikasi::create([
                'user_id' => $uid,
                'judul'   => 'Insiden baru dilaporkan',
                'pesan'   => "Insiden #{$i->id}: {$i->judul}",
                'tipe'    => 'insiden',
            ]);
        }

        return response()->json($i, 201);
    }

    public function show($id) { return Insiden::with('user')->findOrFail($id); }

    public function update(Request $request, $id)
    {
        $i = Insiden::findOrFail($id);
        $i->update($request->all());
        return response()->json($i);
    }

    public function destroy($id)
    {
        Insiden::findOrFail($id)->delete();
        return response()->json(['message' => 'Insiden dihapus']);
    }

    public function tangani(Request $request, $id)
    {
        $i = Insiden::findOrFail($id);
        $i->update([
            'status'         => 'ditangani',
            'catatan_tangani'=> $request->input('catatan'),
            'ditangani_oleh' => $request->user()->id,
        ]);
        Notifikasi::create([
            'user_id' => $i->user_id,
            'judul'   => 'Laporan insiden ditangani',
            'pesan'   => "Insiden #{$i->id} sedang ditangani.",
            'tipe'    => 'insiden',
        ]);
        return response()->json($i);
    }
}

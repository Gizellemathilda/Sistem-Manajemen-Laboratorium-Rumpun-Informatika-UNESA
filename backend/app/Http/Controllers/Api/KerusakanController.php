<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kerusakan;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\Request;

class KerusakanController extends Controller
{
    public function index(Request $request)
    {
        $q = Kerusakan::with(['user','inventaris']);
        if (in_array($request->user()->role, ['mahasiswa','asprak'])) {
            $q->where('user_id', $request->user()->id);
        }
        return $q->latest()->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'inventaris_id' => 'required|exists:inventaris,id',
            'deskripsi'     => 'required|string',
            'tingkat'       => 'required|in:ringan,sedang,berat',
            'foto'          => 'nullable|string', // base64
        ]);
        $data['user_id'] = $request->user()->id;
        $data['status']  = 'Menunggu Teknisi';
        $k = Kerusakan::create($data);

        // Notifikasi untuk pelapor (konfirmasi terkirim) -- frontend juga menampilkan pop-up
        Notifikasi::create([
            'user_id' => $request->user()->id,
            'judul'   => 'Laporan kerusakan terkirim',
            'pesan'   => 'Laporan kerusakan Anda telah diterima dan akan ditinjau.',
            'tipe'    => 'kerusakan',
        ]);

        // Notifikasi ke aslab/admin
        $petugas = User::whereIn('role', ['aslab','admin'])->pluck('id');
        foreach ($petugas as $uid) {
            Notifikasi::create([
                'user_id' => $uid,
                'judul'   => 'Kerusakan baru dilaporkan',
                'pesan'   => "Kerusakan #{$k->id} menunggu penanganan.",
                'tipe'    => 'kerusakan',
            ]);
        }

        return response()->json($k, 201);
    }

    public function show($id) { return Kerusakan::with(['user','inventaris'])->findOrFail($id); }

    public function update(Request $request, $id)
    {
        $k = Kerusakan::findOrFail($id);
        $k->update($request->all());
        return response()->json($k);
    }

    public function destroy($id)
    {
        Kerusakan::findOrFail($id)->delete();
        return response()->json(['message' => 'Laporan kerusakan dihapus']);
    }

    public function tangani(Request $request, $id)
    {
        $k = Kerusakan::findOrFail($id);
        $k->update([
            'status'        => 'Dalam Perbaikan',
            'catatan_tangani' => $request->input('catatan'),
            'ditangani_oleh'  => $request->user()->id,
        ]);
        Notifikasi::create([
            'user_id' => $k->user_id,
            'judul'   => 'Laporan kerusakan ditangani',
            'pesan'   => "Kerusakan #{$k->id} sedang ditangani.",
            'tipe'    => 'kerusakan',
        ]);
        return response()->json($k);
    }
}

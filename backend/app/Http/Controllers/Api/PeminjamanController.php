<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class PeminjamanController extends Controller
{
    public function index(Request $request)
    {
        $q = Peminjaman::with(['user', 'inventaris']);
        if ($request->user()->role === 'mahasiswa' || $request->user()->role === 'asprak') {
            $q->where('user_id', $request->user()->id);
        }
        return $q->latest()->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'inventaris_id' => 'required|exists:inventaris,id',
            'jumlah'        => 'nullable|integer|min:1',
            'tgl_pinjam'    => 'required|date',
            'tgl_kembali'   => 'required|date|after_or_equal:tgl_pinjam',
            'keperluan'     => 'required|string',
        ]);
        $data['jumlah']  = $data['jumlah'] ?? 1; 
        $data['user_id'] = $request->user()->id;
        $data['status']  = 'Menunggu Validasi';
        $p = Peminjaman::create($data);

        Notifikasi::create([
            'user_id' => $request->user()->id,
            'judul'   => 'Peminjaman dikirim',
            'pesan'   => 'Permintaan peminjaman Anda menunggu validasi.',
            'tipe'    => 'peminjaman',
        ]);

        return response()->json($p, 201);
    }

    public function show($id) { return Peminjaman::with(['user','inventaris'])->findOrFail($id); }

    public function update(Request $request, $id)
    {
        $p = Peminjaman::findOrFail($id);
        $p->update($request->all());
        return response()->json($p);
    }

    public function destroy($id)
    {
        Peminjaman::findOrFail($id)->delete();
        return response()->json(['message' => 'Peminjaman dihapus']);
    }

    public function setujui($id)
    {
        $p = Peminjaman::findOrFail($id);
        $p->update(['status' => 'disetujui']);
        Notifikasi::create([
            'user_id' => $p->user_id,
            'judul'   => 'Peminjaman disetujui',
            'pesan'   => "Peminjaman #{$p->id} telah disetujui.",
            'tipe'    => 'peminjaman',
        ]);
        return response()->json($p);
    }

    public function tolak(Request $request, $id)
    {
        $p = Peminjaman::findOrFail($id);
        $p->update(['status' => 'ditolak', 'catatan' => $request->input('catatan')]);
        Notifikasi::create([
            'user_id' => $p->user_id,
            'judul'   => 'Peminjaman ditolak',
            'pesan'   => "Peminjaman #{$p->id} ditolak.",
            'tipe'    => 'peminjaman',
        ]);
        return response()->json($p);
    }

    public function kembalikan($id)
    {
        $p = Peminjaman::findOrFail($id);
        $p->update(['status' => 'dikembalikan', 'tgl_dikembalikan' => now()]);
        return response()->json($p);
    }
}

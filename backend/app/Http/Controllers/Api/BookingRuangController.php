<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookingRuang;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\Request;

class BookingRuangController extends Controller
{
    public function index(Request $request)
    {
        $q = BookingRuang::with(['user', 'ruangan']);
        if (in_array($request->user()->role, ['mahasiswa', 'asprak'])) {
            $q->where('user_id', $request->user()->id);
        }
        return $q->latest()->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ruangan_id' => 'required|exists:ruangan,id',
            'tanggal'    => 'required|date',
            'waktu'      => 'required|string|max:30',
            'keperluan'  => 'required|string',
        ]);
        $data['user_id'] = $request->user()->id;
        $data['status']  = 'Menunggu';
        $b = BookingRuang::create($data);

        Notifikasi::create([
            'user_id' => $request->user()->id,
            'judul'   => 'Booking ruang dikirim',
            'pesan'   => 'Permintaan booking ruang Anda menunggu persetujuan.',
            'tipe'    => 'booking',
        ]);

        $petugas = User::whereIn('role', ['aslab', 'admin'])->pluck('id');
        foreach ($petugas as $uid) {
            Notifikasi::create([
                'user_id' => $uid,
                'judul'   => 'Booking ruang baru',
                'pesan'   => "Booking #{$b->id} menunggu persetujuan.",
                'tipe'    => 'booking',
            ]);
        }

        return response()->json($b->load(['user', 'ruangan']), 201);
    }

    public function show($id)
    {
        return BookingRuang::with(['user', 'ruangan'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $b = BookingRuang::findOrFail($id);
        $b->update($request->only(['tanggal', 'waktu', 'keperluan', 'status']));
        return response()->json($b);
    }

    public function destroy($id)
    {
        BookingRuang::findOrFail($id)->delete();
        return response()->json(['message' => 'Booking dihapus']);
    }

    public function setujui($id)
    {
        $b = BookingRuang::findOrFail($id);
        $b->update(['status' => 'Disetujui']);
        Notifikasi::create([
            'user_id' => $b->user_id,
            'judul'   => 'Booking ruang disetujui',
            'pesan'   => "Booking ruang #{$b->id} telah disetujui.",
            'tipe'    => 'booking',
        ]);
        return response()->json($b);
    }

    public function tolak(Request $request, $id)
    {
        $b = BookingRuang::findOrFail($id);
        $b->update(['status' => 'Ditolak']);
        Notifikasi::create([
            'user_id' => $b->user_id,
            'judul'   => 'Booking ruang ditolak',
            'pesan'   => "Booking ruang #{$b->id} ditolak.",
            'tipe'    => 'booking',
        ]);
        return response()->json($b);
    }
}

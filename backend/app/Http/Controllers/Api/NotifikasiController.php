<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        return Notifikasi::where('user_id', $request->user()->id)
            ->latest()->limit(100)->get();
    }

    public function unreadCount(Request $request)
    {
        $c = Notifikasi::where('user_id', $request->user()->id)
            ->where('dibaca', false)->count();
        return response()->json(['unread' => $c]);
    }

    public function tandaiBaca($id, Request $request)
    {
        $n = Notifikasi::where('user_id', $request->user()->id)->findOrFail($id);
        $n->update(['dibaca' => true, 'dibaca_at' => now()]);
        return response()->json($n);
    }

    public function tandaiSemua(Request $request)
    {
        Notifikasi::where('user_id', $request->user()->id)
            ->where('dibaca', false)
            ->update(['dibaca' => true, 'dibaca_at' => now()]);
        return response()->json(['message' => 'Semua notifikasi ditandai dibaca']);
    }
}

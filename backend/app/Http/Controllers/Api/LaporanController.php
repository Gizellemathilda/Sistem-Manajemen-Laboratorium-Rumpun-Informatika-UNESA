<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\Kerusakan;
use App\Models\Insiden;
use App\Models\Inventaris;
use App\Models\BookingRuang;
use App\Models\User;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'total_user'          => User::count(),
            'total_inventaris'    => Inventaris::count(),
            'peminjaman_aktif'    => Peminjaman::whereNotIn('status', ['Selesai', 'Ditolak'])->count(),
            'kerusakan_aktif'     => Kerusakan::where('status', '!=', 'Selesai')->count(),
            'insiden_aktif'       => Insiden::where('status', '!=', 'Selesai')->count(),
            'booking_menunggu'    => BookingRuang::where('status', 'Menunggu')->count(),
            'peminjaman_per_bulan' => Peminjaman::selectRaw('MONTH(created_at) as bulan, COUNT(*) as total')
                ->whereYear('created_at', now()->year)
                ->groupBy('bulan')
                ->orderBy('bulan')
                ->get(),
            'kerusakan_per_status' => Kerusakan::selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->get(),
        ]);
    }
}

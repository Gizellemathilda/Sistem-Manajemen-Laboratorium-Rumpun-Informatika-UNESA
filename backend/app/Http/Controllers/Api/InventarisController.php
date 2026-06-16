<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventaris;
use Illuminate\Http\Request;

class InventarisController extends Controller
{
    public function index()
    {
        return Inventaris::with('ruangan')->orderBy('nama')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'        => 'required|string|max:120',
            'kategori'    => 'required|string|max:60',
            'jumlah'      => 'required|integer|min:0',
            'kondisi'     => 'required|in:Baik,Rusak Ringan,Rusak Berat',
            'ruangan_id'  => 'nullable|exists:ruangan,id',
            'keterangan'  => 'nullable|string',
        ]);
        return response()->json(Inventaris::create($data), 201);
    }

    public function show($id) { return Inventaris::with('ruangan')->findOrFail($id); }

    public function update(Request $request, $id)
    {
        $inv = Inventaris::findOrFail($id);
        $inv->update($request->all());
        return response()->json($inv);
    }

    public function destroy($id)
    {
        Inventaris::findOrFail($id)->delete();
        return response()->json(['message' => 'Inventaris dihapus']);
    }
}

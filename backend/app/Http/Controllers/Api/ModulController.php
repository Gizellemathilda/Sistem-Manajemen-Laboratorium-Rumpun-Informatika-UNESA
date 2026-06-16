<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Modul;
use Illuminate\Http\Request;

class ModulController extends Controller
{
    public function index()
    {
        return Modul::with('uploader')->latest()->get(['id','judul','mata_kuliah','deskripsi','nama_file','mime_type','ukuran','user_id','created_at']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'judul'       => 'required|string|max:160',
            'mata_kuliah' => 'required|string|max:120',
            'deskripsi'   => 'nullable|string',
            'nama_file'   => 'required|string|max:200',
            'mime_type'   => 'required|string|max:120',
            'ukuran'      => 'required|numeric|min:0',
            'data_base64' => 'required|string', // konten file base64
        ]);
        $data['user_id'] = $request->user()->id;
        $m = Modul::create($data);
        return response()->json($m->only(['id','judul','mata_kuliah','nama_file','mime_type']), 201);
    }

    public function show($id)
    {
        return Modul::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $m = Modul::findOrFail($id);
        $m->update($request->only(['judul','mata_kuliah','deskripsi']));
        return response()->json($m);
    }

    public function destroy($id)
    {
        Modul::findOrFail($id)->delete();
        return response()->json(['message' => 'Modul dihapus']);
    }

    public function unduh($id)
    {
        $m = Modul::findOrFail($id);
        return response()->json([
            'nama_file'   => $m->nama_file,
            'mime_type'   => $m->mime_type,
            'data_base64' => $m->data_base64,
        ]);
    }
}

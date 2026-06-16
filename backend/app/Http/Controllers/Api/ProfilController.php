<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($request->user());
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'nama'     => 'sometimes|string|max:120',
            'email'    => 'sometimes|email|unique:users,email,' . $user->id,
            'nim_nip'  => 'nullable|string|max:30',
            'no_hp'    => 'nullable|string|max:20',
        ]);
        $user->update($data);
        return response()->json($user);
    }

    public function uploadFoto(Request $request)
    {
        $data = $request->validate([
            'foto_base64' => 'required|string', // data:image/...;base64,xxx
        ]);
        $user = $request->user();
        $user->update(['foto' => $data['foto_base64']]);
        return response()->json(['foto' => $user->foto]);
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'password_lama' => 'required|string',
            'password_baru' => 'required|string|min:6',
        ]);
        $user = $request->user();
        if (! Hash::check($data['password_lama'], $user->password)) {
            return response()->json(['message' => 'Password lama salah'], 422);
        }
        $user->update(['password' => Hash::make($data['password_baru'])]);
        return response()->json(['message' => 'Password diperbarui']);
    }
}

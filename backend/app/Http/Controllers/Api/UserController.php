<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return User::orderBy('nama')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'     => 'required|string|max:120',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:mahasiswa,asprak,aslab,dosen,admin',
            'nim_nip'  => 'nullable|string|max:30',
        ]);
        $data['password'] = Hash::make($data['password']);
        return response()->json(User::create($data), 201);
    }

    public function show($id)
    {
        return User::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'nama'     => 'sometimes|string|max:120',
            'email'    => 'sometimes|email|unique:users,email,' . $id,
            'role'     => 'sometimes|in:mahasiswa,asprak,aslab,dosen,admin',
            'nim_nip'  => 'nullable|string|max:30',
            'password' => 'nullable|string|min:6',
        ]);
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
        return response()->json($user);
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return response()->json(['message' => 'User dihapus']);
    }

    public function toggle($id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'status' => $user->status === 'Aktif' ? 'Nonaktif' : 'Aktif'
        ]);
        return response()->json($user);
    }
}

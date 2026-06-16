<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => ['Kredensial tidak valid.']]);
        }

        $token = $user->createToken('simlab')->plainTextToken;
        return response()->json(['user' => $user, 'token' => $token]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'nama'     => 'required|string|max:120',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:mahasiswa,asprak,aslab,dosen,admin',
            'nim_nip'  => 'nullable|string|max:30',
        ]);
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        $token = $user->createToken('simlab')->plainTextToken;
        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    /**
     * Google OAuth login.
     *
     * Frontend mengirim ID token / email Google hasil OAuth.
     * Controller ini menentukan role berdasarkan domain email Google
     * sehingga akun mahasiswa, asprak, aslab, dosen, dan admin dapat
     * dibedakan secara otomatis.
     *
     * Mapping domain (whitelist):
     *   - admin.unesa.ac.id   -> admin
     *   - dosen.unesa.ac.id   -> dosen
     *   - aslab.unesa.ac.id   -> aslab
     *   - asprak.unesa.ac.id  -> asprak
     *   - mhs.unesa.ac.id / unesa.ac.id (default) -> mahasiswa
     *
     * Bila user belum terdaftar, akan dibuat otomatis dengan role tersebut.
     * Bila user sudah ada, role yang tersimpan di database tetap dipakai
     * (sehingga admin dapat meng-override role lewat manajemen pengguna).
     */
    public function googleLogin(Request $request)
    {
        $data = $request->validate([
            'email'      => 'required|email',
            'name'       => 'nullable|string|max:120',
            'google_id'  => 'nullable|string|max:64',
            'id_token'   => 'nullable|string',
        ]);

        $email = strtolower($data['email']);
        $role  = $this->resolveRoleFromEmail($email);

        $user = User::where('email', $email)->first();
        if (! $user) {
            $user = User::create([
                'nama'     => $data['name'] ?? Str::headline(Str::before($email, '@')),
                'email'    => $email,
                'password' => Hash::make(Str::random(32)), // random — login hanya via Google
                'role'     => $role,
                'nim_nip'  => null,
            ]);
        }
        // Catatan: jika user sudah ada, gunakan role yang tersimpan di DB
        // (memungkinkan admin mempromosikan/mendemosikan akun secara manual).

        $token = $user->createToken('simlab-google')->plainTextToken;
        return response()->json([
            'user'  => $user,
            'token' => $token,
            'via'   => 'google',
            'resolved_role' => $user->role,
        ]);
    }

    /**
     * Tentukan role default dari domain email Google.
     */
    protected function resolveRoleFromEmail(string $email): string
    {
        $domain = Str::after($email, '@');

        return match (true) {
            str_ends_with($domain, 'admin.unesa.ac.id')  => 'admin',
            str_ends_with($domain, 'dosen.unesa.ac.id')  => 'dosen',
            str_ends_with($domain, 'aslab.unesa.ac.id')  => 'aslab',
            str_ends_with($domain, 'asprak.unesa.ac.id') => 'asprak',
            default                                       => 'mahasiswa',
        };
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil']);
    }
}

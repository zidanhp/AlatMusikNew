<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:pengguna,email,'.$user->id,
            'username' => 'required|string|max:255|unique:pengguna,username,'.$user->id,
            'telepon' => 'required|string|max:15|unique:pengguna,telepon,'.$user->id,
            'alamat' => 'required|string|max:255',
        ], [
            'Telepon.unique' => 'Nomor handphone sudah terdaftar',
            'username.unique' => 'Username sudah digunakan',
            'email.unique' => 'Email sudah terdaftar',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'telepon' => $validated['telepon'],
            'alamat' => $validated['alamat'],
        ];

        $user->update($updateData);

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    public function updateFotoProfil(Request $request)
    {
        $request->validate([
            'foto_profil' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = auth()->user();
        $uploadPath = public_path('fotoprofil');

        // Buat folder jika belum ada
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Hapus foto lama jika ada
        if ($user->foto_profil && file_exists(public_path($user->foto_profil))) {
            unlink(public_path($user->foto_profil));
        }

        // Upload foto baru
        $image = $request->file('foto_profil');
        $fileName = time().'_'.$image->getClientOriginalName();
        $image->move($uploadPath, $fileName);
        
        // Update hanya foto profil
        $user->update([
            'foto_profil' => 'fotoprofil/'.$fileName
        ]);

        return back()->with('success', 'Foto Profil berhasil diubah!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        auth()->user()->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Password berhasil diubah!');
    }
}
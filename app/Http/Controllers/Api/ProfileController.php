<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    // GET: Ambil data profile user yang sedang login
    public function getProfile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'message' => 'Profile retrieved successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'gender' => $user->gender,
                'ttl' => $user->ttl,
                'photo_url' => $user->photo ? asset('storage/' . $user->photo) : null,
            ]
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user(); // Mengambil data user yang sedang login

        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:255',
            'email'  => 'required|string|email|max:255|unique:users,email,' . $user->id, // Kecualikan email user saat ini
            'gender' => 'nullable|string|in:Laki-laki,Perempuan,Lainnya',
            'ttl'    => 'nullable|string|max:255',
            'photo'  => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Maksimal 2MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Logika Upload Foto
        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada di storage
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }

            // Simpan foto baru ke folder 'storage/app/public/profiles'
            $path = $request->file('photo')->store('profiles', 'public');
            $user->photo = $path;
        }

        // 3. Update Data Lainnya
        $user->name = $request->name;
        $user->email = $request->email;
        $user->gender = $request->gender;
        $user->ttl = $request->ttl;
        $user->save();

        // Mengembalikan response sukses beserta full URL foto
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'gender' => $user->gender,
                'ttl' => $user->ttl,
                'photo_url' => $user->photo ? asset('storage/' . $user->photo) : null,
            ]
        ], 200);
    }
}
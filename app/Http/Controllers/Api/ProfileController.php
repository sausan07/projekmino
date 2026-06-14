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
                'avatar' => $user->avatar,
            ]
        ], 200);
    }

public function updateProfile(Request $request)
    {
        // ❌ HAPUS ATAU KOMENTARI dd() ini agar tidak menyumbat API Flutter!
        // dd($request->all());

        $user = $request->user();

        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:255',
            'email'  => 'required|string|email|max:255|unique:users,email,' . $user->id, 
            'gender' => 'nullable|string|in:Laki-laki,puan,Lainnya',
            'ttl'    => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Maksimal 2MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Logika Upload Foto (SEKARANG SUDAH DIISI ✅)
        if ($request->hasFile('avatar')) {
            // Hapus avatar lama di storage jika sebelumnya sudah ada (opsional, biar hemat space server)
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Simpan file fisik baru ke folder 'storage/app/public/avatars'
            $path = $request->file('avatar')->store('avatars', 'public');
            
            // Set property avatar berupa STRING path-nya saja untuk disimpan ke DB
            $user->avatar = $path;
        }

        // 3. Update Data Lainnya
        $user->name = $request->name;
        $user->email = $request->email;
        $user->gender = $request->gender;
        $user->ttl = $request->ttl;
        
        // ❌ JANGAN gunakan $user->avatar = $request->avatar; secara langsung di sini lagi!
        // Karena logic upload-nya sudah ditangani secara aman oleh Blok Nomor 2 di atas.

        $user->save();

        // Mengembalikan response sukses beserta full URL foto ke Flutter
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'gender' => $user->gender,
                'ttl' => $user->ttl,
                // Mengubah path lokal database (misal: avatars/abc.png) menjadi Full URL Internet yang bisa dibaca Image.network Flutter
                'avatar' => $user->avatar 
            ? asset('storage/' . $user->avatar) 
            : asset('storage/avatars/Dn7JHIxFCBcz7rFaD3OjtuB0qmYgYnZz6b0CpDKj.png'),
            ]
        ], 200);
    }
}
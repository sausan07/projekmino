<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\UserChallenge;
use App\Models\DiamondTransaction;
use App\Models\User; // 1. TAMBAHAN: Panggil model User di sini agar ringkas
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Resources\UserChallengeResource;

class UserChallengeController extends Controller
{
    // 1. Ambil list challenge aktif milik user (untuk dipasang di Home Flutter)
    public function index()
    {
        $user = Auth::user();
        
        $data = UserChallenge::with('challenge')
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'failed'])
            ->get();

        // Mengembalikan list data pakai Collection Resource
        return response()->json([
            'status' => 'success',
            'data' => UserChallengeResource::collection($data)
        ]);
    }

    // 2. Fungsi saat user klik "Ikuti Challenge"
    public function join(Request $request)
    {
        $request->validate([
            'challenge_id' => 'required|exists:challenges,id'
        ]);

        $user = Auth::user();

        $exists = UserChallenge::where('user_id', $user->id)
            ->where('challenge_id', $request->challenge_id)
            ->whereIn('status', ['active', 'failed'])
            ->exists();

        if ($exists) {
            return response()->json(['status' => 'error', 'message' => 'Kamu sedang mengikuti challenge ini'], 400);
        }

        $userChallenge = UserChallenge::create([
            'user_id' => $user->id,
            'challenge_id' => $request->challenge_id,
            'progress_days' => 0,
            'last_progress_date' => null,
            'status' => 'active'
        ]);

        return response()->json(['status' => 'success', 'data' => $userChallenge], 201);
    }

    // 3. Fungsi utama: Tombol CENTANG Harian di Flutter
    public function checkIn($id)
    {
        $userChallenge = UserChallenge::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $challenge = Challenge::find($userChallenge->challenge_id);
        $today = Carbon::today();

        if ($userChallenge->status === 'failed') {
            return response()->json(['status' => 'error', 'message' => 'Challenge kamu hangus, silahkan revive pakai diamond!'], 400);
        }

        if ($userChallenge->last_progress_date && Carbon::parse($userChallenge->last_progress_date)->isToday()) {
            return response()->json(['status' => 'error', 'message' => 'Kamu sudah mencentang hari ini!'], 400);
        }

        if ($userChallenge->last_progress_date) {
            $lastDate = Carbon::parse($userChallenge->last_progress_date);
            if ($lastDate->diffInDays($today) > 1) {
                $userChallenge->update(['status' => 'failed']);
                return response()->json(['status' => 'failed_streak', 'message' => 'Challenge kamu hangus karena lupa mencentang!'], 400);
            }
        }

        $newProgress = $userChallenge->progress_days + 1;
        $isCompleted = $newProgress >= $challenge->required_days;

        $userChallenge->progress_days = $newProgress;
        $userChallenge->last_progress_date = $today->toDateString();
        
        if ($isCompleted) {
            $userChallenge->status = 'completed';
            
            // 2. PERBAIKAN: Ambil data user dari Model agar variabel $user terdefinisi
            $user = User::find(Auth::id());
            $user->increment('diamonds', 10); // Menambah 10 diamond murni lewat model User

            // Catat transaksi diamond masuk
            DiamondTransaction::create([
                'user_id' => $user->id, // Sekarang tidak akan eror lagi karena $user sudah ada
                'amount' => 10,
                'type' => 'credit',
                'source' => 'challenge_completed',
                'description' => "Hadiah menyelesaikan tantangan: {$challenge->name}"
            ]);
        }

        $userChallenge->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil!',
            'data' => new UserChallengeResource($userChallenge) // Menggunakan single resource
        ]);
    }

    // 4. Fungsi RE-VIVE: Bayar 5 Diamond buat ngidupin lagi
    public function revive($id)
    {
        $userChallenge = UserChallenge::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        
        // 3. PERBAIKAN: Ganti Auth::user() menjadi User::find() agar fungsi decrement() dikenali
        $user = User::find(Auth::id()); 

        if ($userChallenge->status !== 'failed') {
            return response()->json(['status' => 'error', 'message' => 'Challenge ini tidak sedang hangus'], 400);
        }

        // CEK SALDO DIAMOND USER
        if ($user->diamonds < 5) {
            return response()->json(['status' => 'error', 'message' => 'Diamond kamu tidak cukup!'], 400);
        }

        // POTONG DIAMOND (Sekarang pasti sukses karena dipanggil via model User asli)
        $user->decrement('diamonds', 5);

        // CATAT TRANSAKSI DIAMOND KELUAR
        DiamondTransaction::create([
            'user_id' => $user->id,
            'amount' => -5,
            'type' => 'debit',
            'source' => 'challenge_revive',
            'description' => "Menebus kegagalan challenge harian"
        ]);

        // PULIHKAN STATUS CHALLENGE
        $userChallenge->update([
            'status' => 'active',
            'last_progress_date' => Carbon::yesterday()->toDateString()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Challenge berhasil diaktifkan kembali!',
            'data' => $userChallenge
        ]);
    }
}
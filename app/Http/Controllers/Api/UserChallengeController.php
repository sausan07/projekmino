<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\UserChallenge;
use App\Models\DiamondTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Resources\UserChallengeResource;

class UserChallengeController extends Controller
{
    // 1. Ambil list challenge aktif milik user (TERMASUK yang sudah completed agar tidak hilang dari home)
    public function index()
    {
        $user = Auth::user();
        
        $data = UserChallenge::with('challenge')
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'failed', 'completed']) 
            ->get();

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
            return response()->json([
                'status' => 'error', 
                'message' => 'Kamu sedang mengikuti challenge ini'
            ], 400);
        }

        $userChallenge = UserChallenge::create([
            'user_id' => $user->id,
            'challenge_id' => $request->challenge_id,
            'progress_days' => 0,
            'last_progress_date' => null,
            'status' => 'active'
        ]);

        $userChallenge->load('challenge');

        return response()->json([
            'status' => 'success', 
            'data' => new UserChallengeResource($userChallenge)
        ], 201);
    }

    // 3. Tombol centang harian
    public function checkIn($id)
    {
        $userChallenge = UserChallenge::where('user_id', Auth::id())
            ->where(function($query) use ($id) {
                $query->where('id', $id)
                      ->orWhere('challenge_id', $id);
            })
            ->whereIn('status', ['active', 'failed', 'completed'])
            ->first();

        if (!$userChallenge) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kamu belum mengikuti tantangan ini! (ID yang dikirim: '.$id.')'
            ], 404);
        }

        $challenge = Challenge::find($userChallenge->challenge_id);
        $today = Carbon::today();

        // ── KONDISI 1: UNCEKLIS (Jika diklik lagi padahal sudah centang hari ini) ──
        if ($userChallenge->last_progress_date && Carbon::parse($userChallenge->last_progress_date)->isToday()) {
            
            $newProgress = max(0, $userChallenge->progress_days - 1);
            $userChallenge->progress_days = $newProgress;
            $userChallenge->last_progress_date = $newProgress > 0 ? Carbon::yesterday()->toDateString() : null;
            
            if ($userChallenge->status === 'completed') {
                $userChallenge->status = 'active';
                
                $user = User::find(Auth::id());
                if ($user->diamonds >= 10) {
                    $user->decrement('diamonds', 10);
                    
                    DiamondTransaction::create([
                        'user_id' => $user->id,
                        'amount' => -10,
                        'type' => 'debit',
                        'source' => 'challenge_uncompleted',
                        'description' => "Pembatalan hadiah karena uncheck tantangan: {$challenge->name}"
                    ]);
                }
            }

            $userChallenge->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil membatalkan centang hari ini!',
                'data' => new UserChallengeResource($userChallenge)
            ]);
        }

        // ── KONDISI 2: CEKLIS BARU (Jika belum mencentang hari ini) ──
        if ($userChallenge->status === 'failed') {
            return response()->json(['status' => 'error', 'message' => 'Challenge kamu hangus, silahkan revive pakai diamond!'], 400);
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
            
            $user = User::find(Auth::id());
            $user->increment('diamonds', 10);

            DiamondTransaction::create([
                'user_id' => $user->id,
                'amount' => 10,
                'type' => 'credit',
                'source' => 'challenge_completed',
                'description' => "Hadiah menyelesaikan tantangan: {$challenge->name}"
            ]);
        }

        $userChallenge->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mencentang tantangan!',
            'data' => new UserChallengeResource($userChallenge)
        ]);
    }

    // 4. Fungsi RE-VIVE: Bayar 5 Diamond buat ngidupin lagi (🔥 TELAH DIPERBAIKI)
    public function revive($id)
    {
        // Perbaikan pencarian ID agar sefleksibel fungsi checkIn
        $userChallenge = UserChallenge::where('user_id', Auth::id())
            ->where(function($query) use ($id) {
                $query->where('id', $id)
                      ->orWhere('challenge_id', $id);
            })
            ->first();

        if (!$userChallenge) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tantangan tidak ditemukan'
            ], 404);
        }
        
        $user = User::find(Auth::id()); 

        if ($userChallenge->status !== 'failed') {
            return response()->json(['status' => 'error', 'message' => 'Challenge ini tidak sedang hangus'], 400);
        }

        if ($user->diamonds < 5) {
            return response()->json(['status' => 'error', 'message' => 'Diamond kamu tidak cukup!'], 400);
        }

        $user->decrement('diamonds', 5);

        DiamondTransaction::create([
            'user_id' => $user->id,
            'amount' => -5,
            'type' => 'debit',
            'source' => 'challenge_revive',
            'description' => "Menebus kegagalan challenge harian"
        ]);

        $userChallenge->update([
            'status' => 'active',
            'last_progress_date' => Carbon::yesterday()->toDateString()
        ]);

        // Muat ulang relasi datanya agar tidak kosong saat dibaca resource
        $userChallenge->load('challenge');

        return response()->json([
            'status' => 'success',
            'message' => 'Challenge berhasil diaktifkan kembali!',
            // Menggunakan Resource agar output JSON rapi dan seragam untuk Flutter
            'data' => new UserChallengeResource($userChallenge) 
        ]);
    }

    // 5. Fungsi DELETE: Keluar atau menghapus tantangan (🔥 BARU DITAMBAHKAN)
    public function destroy($id)
    {
        $userChallenge = UserChallenge::where('user_id', Auth::id())
            ->where(function($query) use ($id) {
                $query->where('id', $id)
                      ->orWhere('challenge_id', $id);
            })
            ->first();

        if (!$userChallenge) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tantangan tidak ditemukan atau bukan milikmu'
            ], 404);
        }

        $userChallenge->delete(); 

        return response()->json([
            'status' => 'success',
            'message' => 'Challenge berhasil dihapus'
        ]);
    }
}

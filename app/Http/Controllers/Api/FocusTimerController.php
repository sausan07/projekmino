<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FocusTimer;
// use App\Models\UserHabit;      // Aktifkan jika butuh akses model Habit
// use App\Models\UserChallenge;  // Aktifkan jika butuh akses model Challenge
use App\Models\DiamondTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FocusTimerController extends Controller
{
    /**
     * START FOCUS
     */
    public function start(Request $request)
    {
        // 🔥 TAMBAHAN: Validasi user_challenge_id
        $request->validate([
            'user_habit_id' => 'nullable|exists:user_habits,id',
            'user_challenge_id' => 'nullable|exists:user_challenges,id' // Sesuaikan nama tabelnya
        ]);

        $timer = FocusTimer::create([
            'user_id' => Auth::id(),
            'user_habit_id' => $request->user_habit_id,
            'user_challenge_id' => $request->user_challenge_id, // 🔥 TAMBAHAN: Simpan ID Challenge
            'is_completed' => false,
            'duration_minutes' => null,
            'created_at' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Focus dimulai',
            'data' => $timer
        ], 201);
    }

    /**
     * COMPLETE FOCUS
     */
    public function complete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:focus_timers,id',
            'duration_minutes' => 'required|integer|min:1'
        ]);

        $timer = FocusTimer::find($request->id);

        if (!$timer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Timer tidak ditemukan'
            ], 404);
        }

        // Pastikan timer milik user yang sedang login
        if ($timer->user_id !== Auth::id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($timer->is_completed) {
            return response()->json([
                'status' => 'error',
                'message' => 'Timer sudah diselesaikan'
            ], 400);
        }

        // Update timer menjadi selesai
        $timer->update([
            'duration_minutes' => $request->duration_minutes,
            'is_completed' => true
        ]);

        // 🔥 LOGIKA REWARD (MODE HYBRID: FOKUS + HABIT + CHALLENGE)
        $user = Auth::user();
        $totalDiamondEarned = 0;

        // 1. Berikan Diamond Dasar (Reward Fokus)
        $focusReward = 2;
        $totalDiamondEarned += $focusReward;

        DiamondTransaction::create([
            'user_id' => $user->id,
            'amount' => $focusReward,
            'source' => 'focus_timer',
            'description' => 'Completed focus session (' . $request->duration_minutes . ' mins)'
        ]);

        // 2. Cek Jika Timer Terikat dengan Habit
        if ($timer->user_habit_id) {
            // Berikan bonus tambahan karena menyelesaikan habit
            $habitReward = 5; // Misal dapat tambahan 5 diamond
            $totalDiamondEarned += $habitReward;

            DiamondTransaction::create([
                'user_id' => $user->id,
                'amount' => $habitReward,
                'source' => 'habit_completed',
                'description' => 'Completed habit via focus timer'
            ]);
        }

        // 3. 🔥 Cek Jika Timer Terikat dengan Challenge
        if ($timer->user_challenge_id) {
            // Opsional: Kode untuk mengupdate status challenge di database jadi selesai
            // $challenge = UserChallenge::find($timer->user_challenge_id);
            // if ($challenge) { $challenge->update(['status' => 'done']); }

            // Berikan bonus tambahan karena menyelesaikan challenge (biasanya lebih besar dari habit)
            $challengeReward = 10; 
            $totalDiamondEarned += $challengeReward;

            DiamondTransaction::create([
                'user_id' => $user->id,
                'amount' => $challengeReward,
                'source' => 'challenge_completed',
                'description' => 'Completed challenge via focus timer'
            ]);
        }

        // 4. Update Saldo Diamond User (PENTING!)
        $user->increment('diamonds', $totalDiamondEarned);

        return response()->json([
            'status' => 'success',
            'message' => "Focus selesai, kamu mendapat +{$totalDiamondEarned} diamonds!",
            'diamond_earned' => $totalDiamondEarned, 
            'data' => $timer
        ]);
    }

    /**
     * HISTORY FOCUS
     */
    public function history(Request $request)
    {
        $query = FocusTimer::where('user_id', Auth::id());

        // optional filter: hanya yang selesai
        if ($request->has('is_completed')) {
            $query->where('is_completed', $request->is_completed);
        }

        $timers = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $timers
        ]);
    }
}
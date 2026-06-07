<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FocusTimer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DiamondTransaction;

class FocusTimerController extends Controller
{
    /**
     * START FOCUS
     */
    public function start(Request $request)
    {
        $request->validate([
            'user_habit_id' => 'nullable|exists:user_habits,id'
        ]);

        $timer = FocusTimer::create([
            'user_id' => Auth::id(),
            'user_habit_id' => $request->user_habit_id,
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

        // 2. LOGIKA REWARD (TAMBAHKAN INI)
        DiamondTransaction::create([
            'user_id' => Auth::id(),
            'amount' => 2,
            'source' => 'focus_timer',
            'description' => 'Completed focus session (' . $request->duration_minutes . ' mins)'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Focus selesai, kamu mendapat +2 diamonds!',
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
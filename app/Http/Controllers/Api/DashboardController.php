<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserHabit;
use App\Models\HabitLog;
use App\Models\DiamondTransaction;
use App\Models\FocusTimer;
use App\Models\Reflection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        // 1. Hitung Total Diamond User
        $diamonds = DiamondTransaction::where('user_id', $user->id)->sum('amount');

        // 2. Ambil semua Habit yang sedang dijalankan user ini
        $activeHabits = UserHabit::with('habit')
            ->where('user_id', $user->id)
            ->get();
        
        $totalHabits = $activeHabits->count();

        // 3. Cari tau habit mana saja yang SUDAH dikerjakan hari ini
        $completedHabitIdsToday = HabitLog::where('date', $today)
            ->where('is_completed', true)
            ->whereIn('user_habit_id', $activeHabits->pluck('id'))
            ->pluck('user_habit_id')
            ->toArray();

        $completedHabitsCount = count($completedHabitIdsToday);

        // 4. Hitung Total Menit Fokus HARI INI
        $focusMinutesToday = FocusTimer::where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->where('is_completed', true)
            ->sum('duration_minutes');

        // 5. Cek apakah user sudah mengisi Reflection hari ini?
        $hasReflectedToday = Reflection::where('user_id', $user->id)
            ->where('date', $today)
            ->exists();

        // BUNGKUS SEMUA DATA MENJADI 1 PAKET RAPI
        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => [
                    'name' => $user->name,
                    'diamonds' => (int) $diamonds,
                ],
                'summary' => [
                    'habits_total' => $totalHabits,
                    'habits_completed_today' => $completedHabitsCount,
                    'focus_minutes_today' => (int) $focusMinutesToday,
                    'has_reflected_today' => $hasReflectedToday,
                ],
                // Kirim juga daftar habit beserta status checklist hari ini
                'today_habits' => $activeHabits->map(function($userHabit) use ($completedHabitIdsToday) {
                    return [
                        'user_habit_id' => $userHabit->id,
                        'habit_name' => $userHabit->habit->name,
                        'streak' => $userHabit->streak,
                        'is_completed_today' => in_array($userHabit->id, $completedHabitIdsToday),
                    ];
                })
            ]
        ]);
    }
}
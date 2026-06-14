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
    
    $selectedMonthInput = $request->query('month'); 
    
    if ($selectedMonthInput) {
        $currentDateObj = Carbon::create(Carbon::now()->year, $selectedMonthInput, 1)->startOfMonth();
    } else {
        $currentDateObj = Carbon::now();
    }

    $startOfMonth = $currentDateObj->copy()->startOfMonth();
    $endOfMonth = $currentDateObj->copy()->endOfMonth();
    
    $today = Carbon::today();
    $todayString = $today->toDateString();
    $filterEndDateString = ($currentDateObj->isCurrentMonth()) ? $todayString : $endOfMonth->toDateString();

    // 1. Hitung Total Diamond User
    $diamonds = DiamondTransaction::where('user_id', $user->id)->sum('amount');

    // 2. Ambil semua Habit Aktif
    $activeHabits = UserHabit::with('habit')->where('user_id', $user->id)->get();
    $totalHabits = $activeHabits->count();
    $activeHabitIds = $activeHabits->pluck('id')->toArray();

    // 3. Habit selesai HARI INI
    $completedHabitIdsToday = [];
    if ($currentDateObj->isCurrentMonth()) {
        $completedHabitIdsToday = HabitLog::where('date', $todayString)
            ->where('is_completed', true)
            ->whereIn('user_habit_id', $activeHabitIds)
            ->pluck('user_habit_id')
            ->toArray();
    }
    $completedHabitsCount = count($completedHabitIdsToday);

    // 4. Kalkulasi Progress Ringkasan Mingguan & Bulanan
    $startOfWeek = Carbon::now()->startOfWeek(); // Ambil minggu berjalan saat ini
    $daysPassedInWeek = Carbon::now()->dayOfWeekIso; // 1 (Senin) sampai 7 (Minggu)
    $totalTargetWeekly = $totalHabits * $daysPassedInWeek;

    $completedWeeklyCount = HabitLog::whereBetween('date', [$startOfWeek->toDateString(), $todayString])
        ->where('is_completed', true)
        ->whereIn('user_habit_id', $activeHabitIds)
        ->count();
    $weeklyGoalPercent = $totalTargetWeekly > 0 ? round($completedWeeklyCount / $totalTargetWeekly, 2) : 0.0;

    $daysInMonthToCount = $currentDateObj->isCurrentMonth() ? Carbon::now()->day : $currentDateObj->daysInMonth;
    $totalTargetMonthly = $totalHabits * $daysInMonthToCount;

    $completedMonthlyCount = HabitLog::whereBetween('date', [$startOfMonth->toDateString(), $filterEndDateString])
        ->where('is_completed', true)
        ->whereIn('user_habit_id', $activeHabitIds)
        ->count();
    $monthlyGoalPercent = $totalTargetMonthly > 0 ? round($completedMonthlyCount / $totalTargetMonthly, 2) : 0.0;


    // =========================================================================
    // 🔥 PERBAIKAN 1: MEMBUAT DATA GRAFIK MINGGUAN (7 HARI: Minggu - Sabtu)
    // =========================================================================
    $weeklyBars = [];
    // Sesuaikan startOfWeek bawaan Carbon (biasanya Senin) atau paksa ke Minggu agar sinkron dengan Flutter
    $startOfGraphWeek = Carbon::now()->startOfWeek(Carbon::SUNDAY); 

    for ($i = 0; $i < 7; $i++) {
        $dayDate = $startOfGraphWeek->copy()->addDays($i)->toDateString();
        
        $dayLogsCount = HabitLog::where('date', $dayDate)
            ->where('is_completed', true)
            ->whereIn('user_habit_id', $activeHabitIds)
            ->count();

        // Nilai maksimal adalah jumlah habit aktif user di hari itu
        $weeklyBars[] = $totalHabits > 0 ? round($dayLogsCount / $totalHabits, 2) : 0.0;
    }


    // =========================================================================
    // 🔥 PERBAIKAN 2: MEMBUAT DATA GRAFIK BULANAN (4 MINGGU PADA BULAN PILIHAN)
    // =========================================================================
    $monthlyBars = [];
    $monthWeekStart = $startOfMonth->copy();
    for ($i = 0; $i < 4; $i++) {
        $startW = $monthWeekStart->copy()->addWeeks($i)->startOfWeek()->toDateString();
        $endW = $monthWeekStart->copy()->addWeeks($i)->endOfWeek()->toDateString();
        
        $weeklyLogsCount = HabitLog::whereBetween('date', [$startW, $endW])
            ->where('is_completed', true)
            ->whereIn('user_habit_id', $activeHabitIds)
            ->count();
            
        $maxLogsInWeek = $totalHabits * 7;
        $monthlyBars[] = $maxLogsInWeek > 0 ? round($weeklyLogsCount / $maxLogsInWeek, 2) : 0.0;
    }


    // 5. Hitung Total Menit Fokus
    $focusMinutesToday = FocusTimer::where('user_id', $user->id)
        ->whereDate('created_at', $todayString)
        ->where('is_completed', true)
        ->sum('duration_minutes');

    // 6. Cek Refleksi
    $hasReflectedToday = Reflection::where('user_id', $user->id)
        ->where('date', $todayString)
        ->exists();

    $allMonthsList = [
        ['id' => 1, 'name' => 'January'], ['id' => 2, 'name' => 'February'],
        ['id' => 3, 'name' => 'March'], ['id' => 4, 'name' => 'April'],
        ['id' => 5, 'name' => 'May'], ['id' => 6, 'name' => 'June'],
        ['id' => 7, 'name' => 'July'], ['id' => 8, 'name' => 'August'],
        ['id' => 9, 'name' => 'September'], ['id' => 10, 'name' => 'October'],
        ['id' => 11, 'name' => 'November'], ['id' => 12, 'name' => 'December'],
    ];

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
            'progress' => [
                'selected_month_id' => (int) $currentDateObj->month,
                'month' => $currentDateObj->format('F'),
                'weekly_goal_percent' => (double) $weeklyGoalPercent,
                'monthly_goal_percent' => (double) $monthlyGoalPercent,
                'weekly_bars' => $weeklyBars,   // Sekarang berisi 7 data harian (Sun - Sat)
                'monthly_bars' => $monthlyBars, // Sekarang berisi 4 data mingguan (Week 1 - 4)
                'all_months' => $allMonthsList,
            ],
            'today_habits' => $activeHabits->map(function($userHabit) use ($completedHabitIdsToday) {
                return [
                    'user_habit_id' => $userHabit->id,
                    'habit_name' => $userHabit->habit->name,
                    'custom_name' => $userHabit->custom_name, 
                    'streak' => $userHabit->streak,
                    'is_completed_today' => in_array($userHabit->id, $completedHabitIdsToday),
                ];
            })
        ]
    ]);
}



}
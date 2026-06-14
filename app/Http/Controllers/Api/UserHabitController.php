<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserHabit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\HabitLog;
use App\Models\DiamondTransaction;
use App\Models\Habit;
use Illuminate\Support\Facades\Auth;

class UserHabitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $userhabits = UserHabit::with('habit')
            ->where('user_id', $request->user()->id)
            ->orderBy('id', 'ASC')
            ->get();

        $today = Carbon::today()->toDateString(); // 🔥 Ambil tanggal hari ini

        // Merapikan data sebelum dikirim ke Flutter
        $formattedData = $userhabits->map(function ($item) use ($today) {
            // 🔥 LOGIKA BARU: Cek apakah habit ini sudah diceklis hari ini
            $isCompletedToday = HabitLog::where('user_habit_id', $item->id)
                ->where('date', $today)
                ->where('is_completed', true)
                ->exists();

            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'habit_id' => $item->habit_id,
                
                // LOGIKA UTAMA: Jika custom_name ada isinya, kirim itu. Jika tidak, kirim nama bawaan (master).
                'name' => $item->custom_name ? $item->custom_name : ($item->habit->name ?? 'Unknown'),
                
                'start_date' => $item->start_date,
                'current_day' => $item->current_day,
                'streak' => $item->streak,
                'status' => $item->status,
                'is_completed_today' => $isCompletedToday, // 🔥 SEKARANG DI SINI JUGA SUDAH ADA!
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formattedData
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $habitName = $request->name;
        $masterHabit = Habit::where('name', $habitName)->first();

        if ($masterHabit) {
            $habitId = $masterHabit->id;
            $customName = null;
        } else {
            $habitId = 1; 
            $customName = $habitName;
        }

        $exists = UserHabit::where('user_id', $request->user()->id)
            ->where('habit_id', $habitId)
            ->where('custom_name', $customName)
            ->where('status', 'active')
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kamu sudah menambahkan habit ini di dashboard!'
            ], 400);
        }

        $userHabit = UserHabit::create([
            'user_id'     => $request->user()->id,
            'habit_id'    => $habitId,
            'start_date'  => Carbon::today()->toDateString(),
            'current_day' => 1,
            'streak'      => 0,
            'status'      => 'active',
            'custom_name' => $customName,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Habit baru berhasil ditambahkan!',
            'data'    => $userHabit->load('habit')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    {
        $userhabit = UserHabit::with('habit')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$userhabit) {
            return response()->json([
                'status' => 'error',
                'message' => 'Habit tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $userhabit
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $userHabit = UserHabit::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$userHabit) {
            return response()->json(['message' => 'Habit tidak ditemukan'], 404);
        }

        $userHabit->custom_name = $request->name;
        $userHabit->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Habit berhasil diupdate'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {
        $userhabit = UserHabit::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$userhabit) {
            return response()->json([
                'status' => 'error',
                'message' => 'Habit tidak ditemukan'
            ], 404);
        }

        $userhabit->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Habit dihapus'
        ]);
    }

    /**
     * CHECK habit (progress, streak, log, dan reward)
     */
    /**
     * CHECK habit (progress, streak, log, dan reward)
     */
    public function check(Request $request, string $id)
    {
        $user = $request->user();
        $userhabit = UserHabit::with('habit')->where('id', $id) // Ditambahkan with('habit') biar ga null
            ->where('user_id', $user->id)
            ->first();

        if (!$userhabit) {
            return response()->json([
                'status' => 'error',
                'message' => 'Habit tidak ditemukan'
            ], 404);
        }

        $today = Carbon::today()->toDateString();

        $alreadyChecked = HabitLog::where('user_habit_id', $userhabit->id)
            ->where('date', $today)
            ->where('is_completed', true)
            ->exists();

        if ($alreadyChecked) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sudah check hari ini'
            ], 400);
        }

        $lastLog = HabitLog::where('user_habit_id', $userhabit->id)
            ->where('is_completed', true)
            ->orderBy('date', 'desc')
            ->first();

        if ($lastLog) {
            $lastLogDate = Carbon::parse($lastLog->date);
            if ($lastLogDate->diffInDays(Carbon::today()) == 1) {
                $userhabit->streak += 1;
            } else {
                $userhabit->streak = 1;
            }
        } else {
            $userhabit->streak = 1;
        }

        $userhabit->current_day += 1;
        $userhabit->save();

        HabitLog::updateOrCreate(
    ['user_habit_id' => $userhabit->id, 'date' => $today],
    ['is_completed' => true]
);

$diamondAmount = 1;

DiamondTransaction::create([
    'user_id' => $user->id,
    'amount' => $diamondAmount,
    'source' => 'habit_check',
]);

        // 🔥 OPSI A: Jika kamu punya kolom 'diamonds' di tabel users, tambahkan ini:
        // $user->increment('diamonds', $diamondAmount);

        return response()->json([
            'status' => 'success',
            'message' => "Habit dicheck, progress tersimpan, dan kamu mendapat +{$diamondAmount} diamonds!",
            'streak' => $userhabit->streak
        ]);
    }

    /**
     * 🔥 UTAMA: FUNGSI UNCHECK (Membatalkan Ceklis)
     */
    public function uncheck(Request $request, string $id)
    {
        $user = $request->user();
        $userhabit = UserHabit::with('habit')->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$userhabit) {
            return response()->json([
                'status' => 'error',
                'message' => 'Habit tidak ditemukan'
            ], 404);
        }

        $today = Carbon::today()->toDateString();

        // 1. Cek apakah memang sudah diceklis hari ini
        $logToday = HabitLog::where('user_habit_id', $userhabit->id)
            ->where('date', $today)
            ->where('is_completed', true)
            ->first();

        if (!$logToday) {
            return response()->json([
                'status' => 'error',
                'message' => 'Habit belum diceklis hari ini'
            ], 400);
        }

        // 2. Hapus log ceklis hari ini
        $logToday->delete();
        
        // 3. Tarik kembali reward diamond yang didapat dari habit SPESIFIK ini hari ini
        $habitName = $userhabit->habit->name ?? 'Unknown Habit';
        $transaction = DiamondTransaction::where('user_id', $user->id)
            ->where('source', 'habit_check')
            // ->where('description', 'Completed habit: ' . $habitName) // Cari yang deskripsinya pas
            ->whereDate('created_at', $today)
            ->first();

        if ($transaction) {
            $amountToDeduct = $transaction->amount;

           

            // Hapus record transaksi lama
            $transaction->delete();
        }

        // 4. Koreksi / Kurangi kembali angka Current Day dan Streak
        if ($userhabit->current_day > 1) {
            $userhabit->current_day -= 1;
        }

        if ($userhabit->streak > 0) {
            $userhabit->streak -= 1;
        }

        $userhabit->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Ceklis berhasil dibatalkan, diamond dan streak dikoreksi.',
            'streak' => $userhabit->streak
        ]);
    }
}
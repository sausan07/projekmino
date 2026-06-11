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

        // Merapikan data sebelum dikirim ke Flutter
        $formattedData = $userhabits->map(function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'habit_id' => $item->habit_id,
                
                // 🔥 LOGIKA UTAMA: Jika custom_name ada isinya, kirim itu. Jika tidak, kirim nama bawaan (master).
                'name' => $item->custom_name ? $item->custom_name : ($item->habit->name ?? 'Unknown'),
                
                'start_date' => $item->start_date,
                'current_day' => $item->current_day,
                'streak' => $item->streak,
                'status' => $item->status,
                // Tambahkan field lain yang sekiranya dibutuhkan di Flutter (seperti is_completed kalau ada)
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
    //Tambah habit ke user
public function store(Request $request)
{
    // 1. Validasi input: Cukup minta 'name' berupa string dari Flutter
    $request->validate([
        'name' => 'required|string|max:255',
    ]);

    $habitName = $request->name;

    // 2. OTOMATISASI: Cari di tabel master 'habits', apakah nama ini cocok dengan template?
    $masterHabit = Habit::where('name', $habitName)->first();

    if ($masterHabit) {
        // Jika COCOK dengan template master (misal: "Membaca")
        $habitId = $masterHabit->id;
        $customName = null; // Kosongkan custom_name karena pakai nama template asli
    } else {
        // Jika TIDAK COCOK (berarti habit unik yang diketik sendiri oleh user)
        // 🔥 PENTING: Pastikan kamu sudah membuat 1 baris data di tabel 'habits' dengan id = 1 untuk template 'Custom'
        $habitId = 1; 
        $customName = $habitName; // Masukkan tulisan uniknya ke custom_name
    }

    // 3. Cek duplikasi agar user tidak menambah habit aktif yang sama persis
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

    // 4. Simpan data habit baru
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

// Fungsi untuk mengedit nama habit
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        // Cari data habit milik user tersebut
        $userHabit = UserHabit::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$userHabit) {
            return response()->json(['message' => 'Habit tidak ditemukan'], 404);
        }

        // 🔥 SIMPAN NAMA BARUNYA KE KOLOM custom_name
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

    //CHECK habit (progress dn streak)
    // CHECK habit (progress, streak, log, dan reward)
    public function check(Request $request, string $id)
    {
        $user = $request->user();
        $userhabit = UserHabit::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$userhabit) {
            return response()->json([
                'status' => 'error',
                'message' => 'Habit tidak ditemukan'
            ], 404);
        }

        $today = Carbon::today()->toDateString();

        // 1. Cek apakah sudah diselesaikan hari ini
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

        // 2. Cek Streak berdasarkan log sukses yang terakhir
        $lastLog = HabitLog::where('user_habit_id', $userhabit->id)
            ->where('is_completed', true)
            ->orderBy('date', 'desc')
            ->first();

        if ($lastLog) {
            $lastLogDate = Carbon::parse($lastLog->date);
            if ($lastLogDate->diffInDays(Carbon::today()) == 1) {
                // Lanjut streak jika kemarin mengerjakan
                $userhabit->streak += 1;
            } else {
                // Reset streak jika bolong
                $userhabit->streak = 1;
            }
        } else {
            // Hari pertama
            $userhabit->streak = 1;
        }

        $userhabit->current_day += 1;
        $userhabit->save();

        // 3. Simpan atau Update ke HabitLog (is_completed = true)
        HabitLog::updateOrCreate(
            [
                'user_habit_id' => $userhabit->id,
                'date' => $today,
            ],
            [
                'is_completed' => true,
            ]
        );

        // 4. Tambahkan Reward +5 Diamonds
        DiamondTransaction::create([
            'user_id' => $user->id,
            'amount' => 5,
            'source' => 'habit_check',
            'description' => 'Completed habit: ' . ($userhabit->habit->name ?? 'Unknown Habit')
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Habit dicheck, progress tersimpan, dan kamu mendapat +5 diamonds!',
            'streak' => $userhabit->streak
        ]);
    }
}

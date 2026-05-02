<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserHabit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class UserHabitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $userhabit = UserHabit::with('habit')
            ->where('user_id', $request->user()->id)
            ->orderBy('id', 'ASC')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $userhabit
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    //Tambah habit ke user
    public function store(Request $request)
    {
        $request->validate([
            'habit_id' => 'required|exists:habits,id'
        ]);

        $user = $request->user();

        //biar gak dobel
        $exists = UserHabit::where('user_id', $user->id)
            ->where('habit_id', $request->habit_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Habit sudah ada'
            ], 400);
        }

        $data = UserHabit::create([
            'user_id' => $user->id,
            'habit_id' => $request->habit_id,
            'start_date' => now()->toDateString(),
            'current_day' => 0,
            'streak' => 0,
            'status' => 'active',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $data
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

    public function update(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:active,paused'
        ]);

        $userhabit = UserHabit::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$userhabit) {
            return response()->json([
                'status' => 'error',
                'message' => 'Habit tidak ditemukan'
            ], 404);
        }

        $userhabit->update([
            'status' => $request->status
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $userhabit
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
    public function check(Request $request, string $id)
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

        $today = Carbon::today();
        $last = $userhabit->updated_at ? Carbon::parse($userhabit->updated_at) : null;

        //sudah check hari ini
        if ($last && $last->isSameDay($today)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sudah check hari ini'
            ], 400);
        }

        //streak logic
        if ($last && $last->diffInDays($today) == 1) {
            $userhabit->streak += 1;
        } else {
            $userhabit->streak = 1;
        }

        $userhabit->current_day += 1;
        $userhabit->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Habit dicheck',
            'streak' => $userhabit->streak
        ]);
    }
}

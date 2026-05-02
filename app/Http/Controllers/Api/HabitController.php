<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Habit;
use Illuminate\Http\Request;

class HabitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Habit::query();

        // filter unlocked (default true)
        if ($request->has('is_unlocked')) {
            $query->where('is_unlocked', $request->is_unlocked);
        }

        $habit = $query->orderBy('id', 'ASC')->get();

        return response()->json([
            'status' => 'success',
            'data' => $habit
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'is_unlocked' => 'boolean'
        ]);

        $habit = Habit::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_unlocked' => $request->is_unlocked ?? false
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $habit
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $habit = Habit::find($id);

        if (!$habit) {
            return response()->json([
                'status' => 'error',
                'message' => 'Habit tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $habit
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $habit = Habit::find($id);

        if (!$habit) {
            return response()->json([
                'status' => 'error',
                'message' => 'Habit tidak ditemukan'
            ], 404);
        }

        $habit->update([
            'name' => $request->name ?? $habit->name,
            'description' => $request->description ?? $habit->description,
            'is_unlocked' => $request->is_unlocked ?? $habit->is_unlocked,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $habit
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $habit = Habit::find($id);

        if (!$habit) {
            return response()->json([
                'status' => 'error',
                'message' => 'Habit tidak ditemukan'
            ], 404);
        }

        $habit->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Habit berhasil dihapus'
        ]);
    }
}

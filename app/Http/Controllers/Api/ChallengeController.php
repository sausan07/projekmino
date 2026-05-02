<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use Illuminate\Http\Request;

class ChallengeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Challenge::orderBy('id', 'ASC')->get();

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'duration_days' => 'required|integer|min:1',
            'required_days' => 'required|integer|min:1',
            'reward_habit_id' => 'nullable|exists:habits,id'
        ]);

        // optional: validasi logic
        if ($request->required_days > $request->duration_days) {
            return response()->json([
                'status' => 'error',
                'message' => 'required_days tidak boleh lebih besar dari duration_days'
            ], 400);
        }

        $data = Challenge::create($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $data
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Challenge::find($id);

        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Challenge tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $challenge = Challenge::find($id);

        if (!$challenge) {
            return response()->json([
                'status' => 'error',
                'message' => 'Challenge tidak ditemukan'
            ], 404);
        }

        $challenge->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Challenge berhasil dihapus'
        ]);
    }
}

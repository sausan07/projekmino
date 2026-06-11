<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reflection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ReflectionController extends Controller
{
    // GET /reflections — semua reflection milik user, terbaru dulu
    public function index()
    {
        $reflections = Reflection::where('user_id', Auth::id())
            ->orderBy('date', 'desc')
            ->get();

        return response()->json($reflections);
    }

    // POST /reflections — simpan atau update reflection hari ini
// POST /reflections — simpan atau update reflection hari ini
    public function store(Request $request)
    {
        $request->validate([
            'mood'    => 'required|in:amazing,good,okey,unusual,bad',
            'title'   => 'nullable|string|max:255',
            'content' => 'nullable|string',
        ]);

        $today = now()->toDateString();

        $reflection = Reflection::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'date'    => $today,
            ],
            [
                'mood'    => $request->input('mood'),
                'title'   => $request->input('title', 'Daily Reflection'), // Default jika kosong
                'content' => $request->input('content', ''),
            ]
        );

        return response()->json([
            'message'    => $reflection->wasRecentlyCreated ? 'Journal berhasil dibuat' : 'Journal diperbarui',
            'reflection' => $reflection,
        ], $reflection->wasRecentlyCreated ? 201 : 200);
    }

    // GET /reflections/today — reflection hari ini
    public function today()
    {
        $reflection = Reflection::where('user_id', Auth::id())
            ->where('date', now()->toDateString())
            ->first();

        if (!$reflection) {
            return response()->json(['message' => 'Belum ada reflection hari ini'], 404);
        }

        return response()->json($reflection);
    }

    // GET /reflections/{date} — reflection by tanggal (format: Y-m-d)
    public function show(string $date)
    {
        $reflection = Reflection::where('user_id', Auth::id())
            ->where('date', $date)
            ->first();

        if (!$reflection) {
            return response()->json(['message' => 'Reflection tidak ditemukan'], 404);
        }

        return response()->json($reflection);
    }

    // PUT /reflections/{date} — update reflection by tanggal
    public function update(Request $request, string $date)
    {
        $request->validate([
            'mood'    => 'sometimes|in:amazing,good,okey,unusual,bad',
            'content' => 'sometimes|nullable|string',
        ]);

        $reflection = Reflection::where('user_id', Auth::id())
            ->where('date', $date)
            ->firstOrFail();

        $reflection->update($request->only(['mood', 'content']));

        return response()->json([
            'message'    => 'Reflection diperbarui',
            'reflection' => $reflection,
        ]);
    }

    // DELETE /reflections/{date}
    public function destroy(string $date)
    {
        $reflection = Reflection::where('user_id', Auth::id())
            ->where('date', $date)
            ->firstOrFail();

        $reflection->delete();

        return response()->json(['message' => 'Reflection dihapus']);
    }
}

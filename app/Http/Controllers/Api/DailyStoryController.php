<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyStory;
use App\Models\Reflection;
use App\Models\HabitLog;
use App\Models\FocusTimer;
use Illuminate\Support\Facades\Auth;

class DailyStoryController extends Controller
{
    /**
     * GET /api/daily-story/{date}
     */
    public function show($date)
    {
        $story = DailyStory::where('user_id', Auth::id())
            ->where('date', $date)
            ->first();
 
        if (!$story) {
            return response()->json([
                'status' => 'error',
                'message' => 'Story not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $story
        ]);
    }

    /**
     * POST /api/daily-story/generate
     */
    public function generate(Request $request)
    {
        $date = $request->input('date', now()->toDateString());

        // 🔹 Ambil data
        $reflection = Reflection::where('user_id', Auth::id())
            ->where('date', $date)
            ->first();

        $completedHabits = HabitLog::whereHas('userHabit', function ($q) {
            $q->where('user_id', Auth::id());
        })
    ->where('date', $date)
    ->get();

        $focusMinutes = FocusTimer::where('user_id', Auth::id())
            ->whereDate('created_at', $date)
            ->where('is_completed', true)
            ->sum('duration_minutes');

        // 🔥 SCORE (simple logic)
        $score = ($completedHabits->count() * 10) + ($focusMinutes / 5);

        // 🧠 AI STYLE TEXT GENERATION (manual logic dulu)
        $mood = $reflection->mood ?? 'neutral';

        $story = $this->generateNarrative(
            $completedHabits,
            $focusMinutes,
            $mood,
            $reflection->content ?? null
        );

        $dailyStory = DailyStory::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'date' => $date
            ],
            [
                'story_text' => $story,
                'score' => round($score),
                'generated_at' => now()
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Daily story generated',
            'data' => $dailyStory
        ]);
    }

    /**
     * ✨ Generate Narrative (AI-style)
     */
    private function generateNarrative($habit, $focus, $mood, $reflectionText)
    {
        $moodText = match ($mood) {
            'happy' => 'You felt happy and positive today.',
            'sad' => 'It was a tough day emotionally.',
            'angry' => 'There were moments of frustration.',
            default => 'It was a balanced day.'
        };

        $productivity = match (true) {
            $habit >= 3 && $focus >= 60 => 'You had an incredibly productive day!',
            $habit >= 2 => 'You made good progress today.',
            $habit >= 1 => 'You took small but meaningful steps.',
            default => 'Today was a slow start, but that’s okay.'
        };

        return "
Today, you completed {$habit} habit(s) and focused for {$focus} minutes.

{$productivity}

{$moodText}

Reflection:
" . ($reflectionText ?? 'No reflection written today, but every day is a chance to grow.');
    }
}
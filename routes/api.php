<?php

use App\Http\Controllers\Api\UserChallengeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\HabitController;
use App\Http\Controllers\Api\UserHabitController;
use App\Http\Controllers\Api\ChallengeController;
use App\Http\Controllers\Api\FocusTimerController;
use App\Http\Controllers\Api\ReflectionController;
use App\Http\Controllers\Api\DailyStoryController;
use App\Http\Controllers\Api\DiamondTransactionController;
use App\Http\Controllers\Api\ProfileController;

// ── Public routes (Bisa diakses tanpa login) ───────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/auth/google', [AuthController::class, 'googleLogin']);

// ── Protected routes (Wajib bawa Token Sanctum) ────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Dashboard Utama
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Profile (GET untuk ambil data, POST untuk update)
    Route::get('/profile', [ProfileController::class, 'getProfile']);
    Route::post('/profile/update', [ProfileController::class, 'updateProfile']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Habits (Master Data / Katalog Habit)
    Route::get('/habits', [HabitController::class, 'index']);
    Route::get('/habits/{id}', [HabitController::class, 'show']);

    // User Habits (Habit yang sedang dijalankan oleh user)
    Route::get('/user-habits', [UserHabitController::class, 'index']);            // TAMBAHAN: Untuk melihat list habit user
    Route::post('/user-habits', [UserHabitController::class, 'store']);           // TAMBAHAN: Untuk mengambil/pilih habit baru
    Route::post('/user-habits/check/{id}', [UserHabitController::class, 'check']); // Untuk nyentang habit hari ini
    Route::delete('/user-habits/{id}', [UserHabitController::class, 'destroy']);
    Route::put('/user-habits/{id}', [UserHabitController::class, 'update']);

    // Challenges
    Route::get('/challenges', [ChallengeController::class, 'index']);
    Route::get('/challenges/{id}', [ChallengeController::class, 'show']);

    // ── User Challenges (Logika utama di Flutter kamu nanti) ───────────
Route::get('/user-challenges', [UserChallengeController::class, 'index']);       // Menampilkan challenge yang sedang diikuti di Home
Route::post('/user-challenges/join', [UserChallengeController::class, 'join']);   // Ikut challenge baru
Route::post('/user-challenges/check/{id}', [UserChallengeController::class, 'checkIn']); // Tombol centang harian
Route::post('/user-challenges/revive/{id}', [UserChallengeController::class, 'revive']); // Tebus pakai 5 diamond

    // Focus Timer
    Route::post('/focus/start',      [FocusTimerController::class, 'start']);
    Route::post('/focus/complete',   [FocusTimerController::class, 'complete']);
    Route::get('/focus/history',     [FocusTimerController::class, 'history']);

    // Reflections (Mood + Jurnal Harian)
    Route::get('/reflections/today',    [ReflectionController::class, 'today']);
    Route::get('/reflections',          [ReflectionController::class, 'index']);
    Route::post('/reflections',         [ReflectionController::class, 'store']);
    Route::get('/reflections/{date}',   [ReflectionController::class, 'show']);
    Route::put('/reflections/{date}',   [ReflectionController::class, 'update']);
    Route::delete('/reflections/{date}',[ReflectionController::class, 'destroy']);

    // Daily Story (Cerita AI)
    Route::get('/daily-story/{date}',   [DailyStoryController::class, 'show']);
    Route::post('/daily-story/generate', [DailyStoryController::class, 'generate']);

    // Diamonds & Transaksi
    Route::get('/diamonds',      [DiamondTransactionController::class, 'diamonds']);
    Route::get('/transactions',  [DiamondTransactionController::class, 'transactions']);

}); // <--- Sudah ditutup dengan benar di sini
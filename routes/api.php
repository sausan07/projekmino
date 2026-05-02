<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChallengeController;
use App\Http\Controllers\Api\HabitController;
use App\Http\Controllers\Api\UserHabitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum'); //yg butuh token tambahin sancum, logout
Route::post('/register', [AuthController::class, 'register']);

// Route::middleware('auth:sanctum')->group(function () {
//     Route::apiResource('userhabit', UserHabitController::class);
//     //route chek user habit
//     Route::post('/userhabit/{id}/check', [UserHabitController::class, 'check']);

//     Route::post('/habit', [HabitController::class, 'store']);
//     Route::patch('/habit/{id}', [HabitController::class, 'update']);
//     Route::delete('/habit/{id}', [HabitController::class, 'destroy']);
// });

Route::apiResource('/api-habit', HabitController::class)->middleware('auth:sanctum');
Route::apiResource('/api-userhabit', UserHabitController::class)->middleware('auth:sanctum');
Route::apiResource('/api-challenge', ChallengeController::class)->middleware('auth:sanctum');
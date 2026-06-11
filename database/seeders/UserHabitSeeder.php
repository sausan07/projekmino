<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserHabitSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('user_habits')->insert([
            [
                'user_id' => 1,     // Pastikan User ID 1 ada di UserSeeder
                'habit_id' => 2,    // 🔥 PERBAIKAN: Gunakan ID yang valid (2 = Minum Air)
                'start_date' => now()->toDateString(),
                'current_day' => 1,
                'streak' => 0,
                'status' => 'active',
                'custom_name' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'habit_id' => 3,    // 🔥 PERBAIKAN: Gunakan ID yang valid (3 = Olahraga)
                'start_date' => now()->toDateString(),
                'current_day' => 1,
                'streak' => 0,
                'status' => 'active',
                'custom_name' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'habit_id' => 1,    // 🔥 Contoh jika ingin membuat dummy habit kustom buatan user sendiri
                'start_date' => now()->toDateString(),
                'current_day' => 1,
                'streak' => 0,
                'status' => 'active',
                'custom_name' => 'Belajar Flutter Terus', // Tulis nama kustomnya di sini
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
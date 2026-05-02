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
                'user_id' => 1,
                'habit_id' => 1,
                'start_date' => now()->toDateString(),
                'current_day' => 2,
                'streak' => 2,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'habit_id' => 2,
                'start_date' => now()->toDateString(),
                'current_day' => 1,
                'streak' => 1,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'habit_id' => 3,
                'start_date' => now()->toDateString(),
                'current_day' => 0,
                'streak' => 0,
                'status' => 'paused',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
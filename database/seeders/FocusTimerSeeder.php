<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FocusTimerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('focus_timers')->insert([
            [
                'user_id' => 1,
                'user_habit_id' => 1,
                'duration_minutes' => 25,
                'is_completed' => true,
                'created_at' => now()->subDays(2),
            ],
            [
                'user_id' => 1,
                'user_habit_id' => 1,
                'duration_minutes' => 30,
                'is_completed' => true,
                'created_at' => now()->subDay(),
            ],
            [
                'user_id' => 1,
                'user_habit_id' => 2,
                'duration_minutes' => 15,
                'is_completed' => true,
                'created_at' => now(),
            ],
            [
                'user_id' => 1,
                'user_habit_id' => 2,
                'duration_minutes' => null,
                'is_completed' => false,
                'created_at' => now(),
            ],       
        ]);
    }
}

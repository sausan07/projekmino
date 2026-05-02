<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChallengeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('challenges')->insert([
            [
                'name' => '7 Hari Konsisten',
                'description' => 'Selesaikan habit selama 7 hari berturut-turut',
                'duration_days' => 7,
                'required_days' => 7,
                'reward_habit_id' => 4, // unlock Bangun Pagi
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '14 Hari Produktif',
                'description' => 'Lakukan aktivitas produktif selama 14 hari',
                'duration_days' => 14,
                'required_days' => 10,
                'reward_habit_id' => 5, // unlock Tidur Cepat
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '30 Hari Disiplin',
                'description' => 'Bangun konsistensi selama 30 hari',
                'duration_days' => 30,
                'required_days' => 25,
                'reward_habit_id' => null, // reward bisa diamond / future feature
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
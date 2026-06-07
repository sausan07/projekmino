<?php

namespace Database\Seeders;

use App\Models\Reflection;
use Illuminate\Database\Seeder;

class ReflectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
            Reflection::insert([
            [
                'user_id' => 1,
                'date' => now()->subDays(2)->toDateString(),
                'content' => 'Hari ini cukup produktif, fokus belajar Flutter.',
                'mood' => 'happy',
                'created_at' => now(),
            ],
            [
                'user_id' => 1,
                'date' => now()->subDay()->toDateString(),
                'content' => 'Agak capek tapi tetap menyelesaikan task.',
                'mood' => 'tired',
                'created_at' => now(),
            ],
        ]);
    }
}

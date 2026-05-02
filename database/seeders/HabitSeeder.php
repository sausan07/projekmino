<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HabitSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('habits')->insert([
            [
                'id' => 1,
                'name' => 'Minum Air',
                'description' => 'Minum 8 gelas air setiap hari',
                'is_unlocked' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Olahraga',
                'description' => 'Olahraga minimal 20 menit',
                'is_unlocked' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Belajar',
                'description' => 'Belajar minimal 1 jam',
                'is_unlocked' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Bangun Pagi',
                'description' => 'Bangun sebelum jam 6 pagi',
                'is_unlocked' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Tidur Cepat',
                'description' => 'Tidur sebelum jam 22.00',
                'is_unlocked' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
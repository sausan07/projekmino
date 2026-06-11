<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HabitSeeder extends Seeder
{
    public function run(): void
    {
        // Membersihkan data lama agar ID kembali dari 1
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('habits')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('habits')->insert([
            [
                'id' => 1, // Penampung habit kustom
                'name' => 'Custom',
                'description' => 'Template for unique custom habits',
                'is_unlocked' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // ── Kategori: Practice self-care ──
            [
                'id' => 2,
                'name' => 'Cold shower',
                'description' => 'Take a refreshing cold shower in the morning',
                'is_unlocked' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Hair care',
                'description' => 'Maintain healthy hair routine',
                'is_unlocked' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // ── Kategori: Become active ──
            [
                'id' => 4,
                'name' => 'Practice yoga',
                'description' => '15 minutes of mindfulness yoga stretching',
                'is_unlocked' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Gym workout',
                'description' => 'Hit the gym or do home workout',
                'is_unlocked' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'Walk 10,000 steps',
                'description' => 'Keep moving and stay active',
                'is_unlocked' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'name' => 'Pilates class',
                'description' => 'Improve core strength with pilates',
                'is_unlocked' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // ── Kategori: Start living healthier ──
            [
                'id' => 8,
                'name' => 'Wake up early',
                'description' => 'Wake up before 6 AM',
                'is_unlocked' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'name' => 'Drink water',
                'description' => 'Drink 8 glasses of water daily',
                'is_unlocked' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 10,
                'name' => 'Take vitamin',
                'description' => 'Keep your immune system strong',
                'is_unlocked' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
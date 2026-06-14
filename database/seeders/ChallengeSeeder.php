<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChallengeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nonaktifkan foreign key checks untuk mengosongkan tabel dengan aman
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('challenges')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $challenges = [
            [
                'id' => 1,
                'name' => 'Benefits of a Clean Home.',
                'description' => 'Cleaning your home is not just about neatness, it also has a direct impact on your health and overall comfort. A clean home helps reduce dust, germs, and allergens that can cause illnesses such as coughs, flu, or allergies. In addition, a tidy environment makes your mind feel calmer and more focused, allowing you to be more productive when studying or working. Cleaning activities also count as light physical exercise, which is beneficial for your body, especially when done regularly. Just as important, a clean home creates a comfortable and pleasant atmosphere to live in with your family. So, start making cleaning a regular habit, because the benefits are immediately felt by both your body and mind.',
                'duration_days' => 20,
                'required_days' => 20,
                'image_asset' => 'assets/images/clean.png',
                'detail_image_asset' => 'assets/images/cleans.png',
                'diamond_reward' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Digital detox',
                'description' => 'Take a break from constant notifications and endless scrolling. This challenge helps you reduce screen time and reconnect with the real world around you. By limiting digital distractions, your mind becomes calmer, your focus improves, and you gain more control over how you spend your time. Use this moment to rest your eyes, clear your thoughts, and be more present in your daily life.',
                'duration_days' => 30,
                'required_days' => 30,
                'image_asset' => 'assets/images/detox.png',
                'detail_image_asset' => 'assets/images/detoxs.png',
                'diamond_reward' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Morning routine',
                'description' => 'Start your day with intention and structure. This challenge helps you build a consistent morning routine that sets the tone for the rest of your day. By doing simple activities like planning your tasks, stretching, or enjoying a quiet moment, you create a sense of control and clarity. A good morning routine can boost your productivity, improve your mood, and help you feel more prepared to face the day ahead.',
                'duration_days' => 20,
                'required_days' => 20,
                'image_asset' => 'assets/images/routine.png',
                'detail_image_asset' => 'assets/images/routines.png',
                'diamond_reward' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Eat Healthy',
                'description' => 'Fuel your body with the nutrients it needs to function at its best. This challenge encourages you to make healthier food choices and be more mindful of what you eat. A balanced diet supports your energy, focus, and long-term health. Small changes in your eating habits can lead to meaningful results.',
                'duration_days' => 20,
                'required_days' => 20,
                'image_asset' => 'assets/images/eat.png',
                'detail_image_asset' => 'assets/images/eats.png',
                'diamond_reward' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Morning run',
                'description' => 'Start your day with energy and a clear mind through a refreshing morning run. This challenge encourages you to build a healthy routine by moving your body early in the day. Running in the morning helps improve your stamina, boost your mood, and increase your focus for the rest of the day. The fresh air and quiet atmosphere can also give you a sense of calm and motivation. It\'s not about speed or distance—it\'s about consistency and showing up for yourself.',
                'duration_days' => 20,
                'required_days' => 20,
                'image_asset' => 'assets/images/run.png',
                'detail_image_asset' => 'assets/images/runs.png',
                'diamond_reward' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'Drink 8 Glasses',
                'description' => 'Stay hydrated and take care of your body from within. This challenge helps you build the simple yet powerful habit of drinking enough water every day. Proper hydration supports your energy, focus, and overall health. It may seem small, but consistency in this habit can make a big difference in how you feel daily.',
                'duration_days' => 30,
                'required_days' => 30,
                'image_asset' => 'assets/images/drink.png',
                'detail_image_asset' => 'assets/images/drinks.png',
                'diamond_reward' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'name' => 'No phone before bed',
                'description' => 'Give your mind a chance to rest before sleep by staying away from your phone at least 30 minutes before bedtime. Instead of scrolling through social media or watching videos, use this time to relax, read a book, reflect on your day, or prepare for tomorrow. This simple habit can help improve sleep quality, reduce mental fatigue, and create a healthier nighttime routine.',
                'duration_days' => 30,
                'required_days' => 30,
                'image_asset' => 'assets/images/nophone.png',
                'detail_image_asset' => 'assets/images/phones.png',
                'diamond_reward' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'name' => 'Deep Work',
                'description' => 'Train yourself to work without distractions by dedicating a focused block of time to a single task. Turn off notifications, avoid multitasking, and give your full attention to what matters most. Deep work helps improve concentration, productivity, and the quality of your results while reducing the stress caused by constant interruptions.',
                'duration_days' => 30,
                'required_days' => 30,
                'image_asset' => 'assets/images/deepwork.png',
                'detail_image_asset' => 'assets/images/works.png',
                'diamond_reward' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'name' => 'Gratitude Challenge',
                'description' => 'Take a few moments each day to appreciate the good things in your life, no matter how small they may seem. Write down things you\'re grateful for, meaningful moments, or acts of kindness you experienced. Practicing gratitude helps shift your focus from what is missing to what is already valuable, leading to a more positive mindset and greater emotional well-being.',
                'duration_days' => 20,
                'required_days' => 20,
                'image_asset' => 'assets/images/gratitude.png',
                'detail_image_asset' => 'assets/images/grats.png',
                'diamond_reward' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 10,
                'name' => 'Face Yoga',
                'description' => 'Take a moment to care for your skin in a natural and relaxing way. This challenge introduces simple facial exercises that help improve your skin\'s health and appearance over time. By gently stimulating your facial muscles, you can boost blood circulation, improve skin elasticity, and reduce signs of aging such as fine lines and wrinkles. Face yoga is not just about beauty—it\'s also about relaxation. It helps release tension in your face while giving you a calm and mindful self-care moment. With consistency, your skin will look healthier, brighter, and more radiant.',
                'duration_days' => 30,
                'required_days' => 30,
                'image_asset' => 'assets/images/face.png',
                'detail_image_asset' => 'assets/images/yogas.png',
                'diamond_reward' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 11,
                'name' => 'Skincare Challenge',
                'description' => 'Give your hair the attention it deserves. This challenge focuses on maintaining healthy hair through simple care routines like using treatments or reducing damage. Taking care of your hair regularly helps improve its strength, shine, and overall appearance. Small efforts can make a noticeable difference.',
                'duration_days' => 20,
                'required_days' => 20,
                'image_asset' => 'assets/images/skincare.png',
                'detail_image_asset' => 'assets/images/skincares.png',
                'diamond_reward' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('challenges')->insert($challenges);
    }
}
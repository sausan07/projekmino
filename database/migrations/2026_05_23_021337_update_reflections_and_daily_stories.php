<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix mood enum di reflections (dari happy/sad/neutral/angry → amazing/good/okey/unusual/bad)
        DB::statement("ALTER TABLE reflections MODIFY COLUMN mood ENUM('amazing','good','okey','unusual','bad') NOT NULL DEFAULT 'good'");

        // Tambah kolom generated_at di daily_stories yang hilang dari migration lama
        Schema::table('daily_stories', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_stories', 'generated_at')) {
                $table->timestamp('generated_at')->nullable()->after('score');
            }
        });

        // Fix focus_timers: buat user_habit_id nullable
        DB::statement("ALTER TABLE focus_timers MODIFY COLUMN user_habit_id BIGINT UNSIGNED NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE reflections MODIFY COLUMN mood ENUM('amazing','good','okey','unusual','bad') NOT NULL DEFAULT 'good'");

        Schema::table('daily_stories', function (Blueprint $table) {
            $table->dropColumn('generated_at');
        });

        DB::statement("ALTER TABLE focus_timers MODIFY COLUMN user_habit_id BIGINT UNSIGNED NOT NULL");
    }
};

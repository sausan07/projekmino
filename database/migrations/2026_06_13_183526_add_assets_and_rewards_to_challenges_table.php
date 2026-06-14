<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->string('image_asset')->nullable();
            $table->string('detail_image_asset')->nullable();
            $table->integer('diamond_reward')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn(['image_asset', 'detail_image_asset', 'diamond_reward']);
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('email');
            $table->string('gender')->nullable()->after('photo'); // Bisa diisi 'Laki-laki' / 'Perempuan'
            $table->string('ttl')->nullable()->after('gender'); // Tempat, DD-MM-YYYY
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['photo', 'gender', 'ttl']);
        });
    }
};
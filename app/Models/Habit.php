<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Habit extends Model
{
    protected $table = 'habits';
    protected $fillable = [
        'name',
        'description',
        'is_unlocked'
    ];

    // RELASI
    public function userHabits()
    {
        return $this->hasMany(UserHabit::class);
    }
}
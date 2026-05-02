<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HabitLog extends Model
{
    protected $table = 'habit_logs';
    protected $fillable = [
        'user_habit_id',
        'date',
        'is_completed'
    ];

    // RELASI
    public function userHabit()
    {
        return $this->belongsTo(UserHabit::class);
    }
}
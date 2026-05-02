<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FocusTimer extends Model
{
    protected $table = 'focus_timers';
    protected $fillable = [
        'user_id',
        'user_habit_id',
        'duration_minutes',
        'is_completed',
        'created_at'
    ];


    // RELASI
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userHabit()
    {
        return $this->belongsTo(UserHabit::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserHabit extends Model
{
    protected $table = 'user_habits';
    protected $fillable = [
        'user_id',
        'habit_id',
        'start_date',
        'current_day',
        'streak',
        'status'
    ];
    // RELASI
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function habit()
    {
        return $this->belongsTo(Habit::class);
    }

    public function habitLogs()
    {
        return $this->hasMany(HabitLog::class);
    }

    public function reflections()
    {
        return $this->hasMany(Reflection::class);
    }

    public function focusTimers()
    {
        return $this->hasMany(FocusTimer::class);
    }
}
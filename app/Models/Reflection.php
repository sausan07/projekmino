<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reflection extends Model
{
    protected $table = 'reflections';
    protected $fillable = [
        'user_id',
        'user_habit_id',
        'date',
        'content',
        'mood'
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
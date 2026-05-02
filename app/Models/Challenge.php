<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    protected $table = 'challenges';
        protected $fillable = [
        'name',
        'description',
        'duration_days',
        'required_days',
        'reward_habit_id'
    ];


    // RELASI
    public function userChallenges()
    {
        return $this->hasMany(UserChallenge::class);
    }

    public function rewardHabit()
    {
        return $this->belongsTo(Habit::class, 'reward_habit_id');
    }
}
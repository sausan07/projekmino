<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserChallenge extends Model
{
    protected $table = 'user_challenges';
    protected $fillable = [
        'user_id',
        'challenge_id',
        'progress_days',
        'last_progress_date',
        'is_completed'
    ];

    // RELASI
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }
}
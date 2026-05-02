<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyStory extends Model
{
    protected $table = 'daily_stories';
    protected $fillable = [
        'user_id',
        'date',
        'story_text',
        'score',
        'generated_at'
    ];

    // RELASI
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
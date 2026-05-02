<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // RELASI
    public function userHabits()
    {
        return $this->hasMany(UserHabit::class);
    }

    public function reflections()
    {
        return $this->hasMany(Reflection::class);
    }

    public function dailyStories()
    {
        return $this->hasMany(DailyStory::class);
    }

    public function diamondTransactions()
    {
        return $this->hasMany(DiamondTransaction::class);
    }

    public function focusTimers()
    {
        return $this->hasMany(FocusTimer::class);
    }

    public function userChallenges()
    {
        return $this->hasMany(UserChallenge::class);
    }
}

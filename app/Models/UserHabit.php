<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute; // 🔥 Tambahkan baris ini!
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
        'status',
        'custom_name',
    ];

    /**
     * 🔥 ACCESSOR LOGIC: 
     * Saat Flutter mengambil data, kita buat logic otomatis:
     * Jika custom_name diisi, gunakan custom_name.
     * Jika custom_name kosong, otomatis ambil dari relasi nama aslinya (habit->name).
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                // Jika kolom custom_name tidak null dan tidak kosong, pakai custom_name
                if (!empty($attributes['custom_name'])) {
                    return $attributes['custom_name'];
                }
                
                // Jika kosong, ambil nama dari relasi tabel habits (jika ada)
                return $this->habit ? $this->habit->name : 'Tanpa Nama';
            }
        );
    }


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
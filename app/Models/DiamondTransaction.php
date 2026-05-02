<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiamondTransaction extends Model
{
    protected $table = 'diamond_transactions';
    protected $fillable = [
        'user_id',
        'amount',
        'source'
    ];

    // RELASI
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class UserChallengeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'challenge_id' => $this->challenge_id,
            'name' => $this->challenge->name, // Langsung ambil nama challenge
            'description' => $this->challenge->description,
            'progress_days' => $this->progress_days,
            'required_days' => $this->challenge->required_days, // Target hari (misal: 30)
            'status' => $this->status, // active, failed, completed
            'last_progress_date' => $this->last_progress_date,
            // Bonus: Kasih penanda ke Flutter apakah hari ini user sudah klik centang atau belum
            'is_checked_today' => $this->last_progress_date ? Carbon::parse($this->last_progress_date)->isToday() : false,
        ];
    }
}
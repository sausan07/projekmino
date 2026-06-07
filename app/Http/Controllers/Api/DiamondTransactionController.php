<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DiamondTransaction;
use Illuminate\Support\Facades\Auth;

class DiamondTransactionController extends Controller
{
    // GET /api/diamonds
    public function diamonds()
    {
        $total = DiamondTransaction::where('user_id', Auth::id())
            ->sum('amount');

        return response()->json([
            'status' => 'success',
            'total' => $total
        ]);
    }

    // GET /api/transactions
    public function transactions()
    {
        $data = DiamondTransaction::where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }
}
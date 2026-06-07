<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;


class AuthController extends Controller
{
    public function login(Request $request){
        $user = User::where('email', $request->email)->first();
        if(!$user){
            return response([
                'message' => ['User not found']
            ], 404);
        }

        if(!Hash::check($request->password, $user->password)){
            return response([
                'message' => ['Incorrect password']
            ], 404);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response([
            'user' => $user,
            'token' => $token
        ], 200);
    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return response([
            'message' => 'Logged out'
        ], 200);
    }

    public function register(Request $request){
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response([
            'message' => 'user regis',
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken,
        ], 200);
    }


public function googleLogin(Request $request)
{
    $request->validate([
        'id_token' => 'required'
    ]);

    $googleResponse = Http::get(
        'https://oauth2.googleapis.com/tokeninfo?id_token=' . $request->id_token
    );

    if ($googleResponse->failed()) {
        return response()->json(['message' => 'Invalid Google Token'], 401);
    }

    $googleUser = $googleResponse->json();

    $email = $googleUser['email'];
    $name = $googleUser['name'] ?? $email;

    $user = User::firstOrCreate(
        ['email' => $email],
        [
            'name' => $name,
            'password' => Hash::make('google-login')
        ]
    );

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'user' => $user,
        'token' => $token
    ]);
}
}
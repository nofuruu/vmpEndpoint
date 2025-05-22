<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');
        $user = \App\Models\User::where('name', $credentials['username'])->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Username tidak ditemukan'
            ], 400);
        }

        if (!auth()->attempt(['name' => $credentials['username'], 'password' => $credentials['password']])) {
            return response()->json([
                'status' => false,
                'message' => 'Username atau password salah'
            ], 401);
        }

        return response()->json([
            'status' => true,
            'message' => 'Login Success',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'status' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    public function profile()
    {
        $user = auth()->user();
        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'profile_picture' => $user->profile_picture,
            'bio' => $user->bio
        ]);
    }
}

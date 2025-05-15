<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

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
            'message' => 'Login success',
            'redirect' => 'dashboard'
        ]);
    }

    public function me()
    {
        try {
            return response()->json(JWTauth::user());
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
    }


    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json(['message' => 'Logout berhasil']);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal logout'
            ], 500);
        }
    }


    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:users,name',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string|min:6',
            'email' => [
                'required',
                'unique:users,email',
                'regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', // Menambahkan regex untuk validasi format email yang lebih ketat
            ],
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'password' => bcrypt($request->password),
            'email' => $request->email,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Register berhasil',
            'redirect' => 'login'
        ], 201);
    }
}

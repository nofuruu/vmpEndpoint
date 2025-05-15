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
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'password_confirm' => 'required|same:password',
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Register berhasil',
            'redirect' => '/login',
        ], 201);
    }
}

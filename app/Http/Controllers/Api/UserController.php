<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    protected $user;

    protected $model;
    // Menampilkan semua pengguna

    public function __construct() {}
    public function index()
    {
        $users = User::all();
        return response()->json([
            'status' => true,
            'users' => $users
        ]);
    }

    // Menyimpan data user baru (Register)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:users,name',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6',
            'password_confirm' => 'required|same:password',
        ]);

        $user = User::create([
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

    // Menampilkan detail pengguna berdasarkan ID
    public function show($id)
    {
        try {
            $user = User::lockForUpdate()->find($id);

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pengguna tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data pengguna'
            ], 500);
        }
    }

    // Update data pengguna berdasarkan ID
    public function update(Request $request, string $id)
    {
        try {
            $user = $this->model->findOrFail($id);

            $data = [
                'name' => $request->input('name'),
                'bio' => $request->input('bio'),
                'email' => $request->input('email'),
                'profile_picture' => $request->input('profile_picture'),
            ];

            $validator = Validator::make($data, [
                'name' => 'required|string|max:255',
                'email' => 'required|string',
                'bio' => 'string:max:255',
                'profile_picture' => 'string'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 422);
            }

            $user->update($validator->validated());
            return $this->successResponse($user, 'User Updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('error updating user', 500);
        }
    }

    //Menghapus pengguna berdasarkan ID
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Pengguna tidak ditemukan'], 404);
        }

        $user->delete();

        return response()->json(['status' => true, 'message' => 'Pengguna berhasil dihapus']);
    }

    //Menghitung total pengguna
    public function count()
    {
        $total = User::count();
        return response()->json([
            'status' => true,
            'total_users' => $total
        ]);
    }
}

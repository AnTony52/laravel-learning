<?php

namespace App\Http\Controllers;

use App\Models\User; // sử dụng mô hình User để tương tác với bảng users trong cơ sở dữ liệu
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // $data['password'] = bcrypt($data['password']);
        // Nếu User model đã có cast thì 'password' => 'hashed',
        // thì không cần bcrypt ở đây nữa
        $user = User::create($data);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
        
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // if (!auth()->attempt($data)) { 
        //     return response()->json(['message' => 'Invalid credentials'], 401);
        // }

        // $user = auth()->user();

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }
        // Token-based auth: revoke current token
        if ($user->currentAccessToken()) {
            $user->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Log out successfully',
            ]);
        }

        // // Session-based auth fallback
        // if ($request->hasSession()) {
        //     auth()->logout();
        //     $request->session()->invalidate();
        //     $request->session()->regenerateToken();
        // }

        // $user->currentAccessToken()->delete(); 

        return response()->json(['message' => 'Logged out successfully']);
    }
}
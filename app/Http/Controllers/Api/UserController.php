<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Helpers\JwtHelper;

class UserController extends Controller
{
    // 1️⃣ Unique username
    public function isUniqueUser($username)
    {
        $exists = User::where('username', $username)->exists();

        return response()->json([
            'status'  => !$exists,
            'message' => $exists ? 'Username already exists' : 'Username available',
            'data'    => ['unique' => !$exists]
        ]);
    }

    // 2️⃣ Register
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users,username',
            'name'     => 'required',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8'
        ]);

        User::create([
            'username' => $request->username,
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'User registered successfully',
            'data'    => 
             ['username' => $request->username,
                'email'    => $request->email
              ]
        ]);
    }

    // 3️⃣ Login (CUSTOM JWT)
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid credentials',
                'data'    => null
            ], 401);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Login successful',
            'data'    => [
                'token' => JwtHelper::generateToken($user),
                'user'  => $user
            ]
        ]);
    }

    // 4️⃣ Complete profile (CUSTOM JWT)
    public function completeProfile(Request $request)
{
    $authHeader = $request->header('Authorization');

    if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
        return response()->json([
            'status' => false,
            'message' => 'Token missing',
            'data' => null
        ], 401);
    }

    $token = str_replace('Bearer ', '', $authHeader);
    $user = JwtHelper::verifyToken($token);

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid or expired token',
            'data' => null
        ], 401);
    }
    // 🔐 TOKEN CHECK KHATAM

    // ✅ Ab $user available hai
    $request->validate([
        'phone'  => 'required|digits_between:10,15',
        'dob'    => 'required|date|before:today',
        'gender' => 'required|in:Male,Female',
        'city'   => 'required|string',
        'bio'    => 'nullable|string|max:500',
        'profile_pic' => 'nullable|url'
    ]);

    $user->update($request->only([
        'phone','dob','gender','city','bio','profile_pic'
    ]));

    return response()->json([
        'status'  => true,
        'message' => 'Profile updated',
        'data'    => $user
    ]);
}

    // 5️⃣ Get profile
    //old he ye
    // public function getProfile(Request $request)
    // {
    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'User profile',
    //         'data'    => $request->get('auth_user')
    //     ]);
    // }

    public function getProfile(Request $request)
{
    // 🔐 TOKEN CHECK — YAHI DALNA HAI
    $authHeader = $request->header('Authorization');

    if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
    return response()->json([
        'status' => false,
        'message' => 'Token missing',
        'data' => null
    ], 401);
}

    $token = substr($authHeader, 7);
    $user = JwtHelper::verifyToken($token);

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid or expired token',
            'data' => null
        ], 401);
    }
    // 🔐 TOKEN CHECK KHATAM

    return response()->json([
        'status'  => true,
        'message' => 'User profile fetched',
        'data'    => $user
    ]);
}

    // 6️⃣ Logout (CUSTOM JWT)
    //old he ye
    // public function logout(Request $request)
    // {
    //     // Since JWT is stateless, logout can be handled on client side
    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Logout successful',
    //         'data'    => null
    //     ]);
    // }

    public function logout(Request $request)
{
    // 🔐 TOKEN CHECK
    $authHeader = $request->header('Authorization');

    if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
        return response()->json([
            'status' => false,
            'message' => 'Token missing',
            'data' => null
        ], 401);
    }

    return response()->json([
        'status' => true,
        'message' => 'Logout successful',
        'data' => null
    ]);
}

}

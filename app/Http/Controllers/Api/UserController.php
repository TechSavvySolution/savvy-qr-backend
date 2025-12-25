<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    // 1️⃣ Check unique username
    public function isUniqueUser($username)
    {
        return response()->json([
            'unique' => !User::where('username', $username)->exists()
        ]);
    }

    // 2️⃣ Register user
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users,username',
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8'
        ]);

        $user = User::create([
            'username' => $request->username,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'uid' => $user->id
        ]);
    }

    // 3️⃣ Complete profile
    //old complete profile api
    //  public function completeProfile(Request $request)
    // {
    //     $uid = $request->header('uid');

    //     if (!$uid) {
    //         return response()->json(['message' => 'UID required'], 400);
    //     }

    //     $request->validate([
    //         'phone' => 'required|string|max:255',
    //         'dob' => 'required |date',
    //         'gender' => 'required|in:Male,Female',
    //         'city' => 'required|string|max:255',
    //         'bio' => 'nullable|string|max:255',
    //         'profile_pic' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    //     ]);

    //     $user = User::find($uid);

    //     if (!$user) {
    //         return response()->json(['message' => 'User not found'], 404);
    //     }

    //     $user->update($request->all());

    //     return response()->json(['message' => 'Profile completed']);
    // }

    public function completeProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'phone' => 'required |string|max:255',
            'dob' => 'required |date',
            'gender' => 'required|in:Male,Female',
            'city' => 'required |string|max:255',
            'bio' => 'nullable |string|max:255',
            'profile_pic' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        $user->update($request->all());

        return response()->json(['message' => 'Profile updated']);
    }

    // 4️⃣ Login
    //old login api
    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email',
    //         'password' => 'required|min:8'
    //     ]);

    //     $user = User::where('email', $request->email)->first();

    //     if (!$user || !Hash::check($request->password, $user->password)) {
    //         return response()->json(['message' => 'Invalid credentials'], 401);
    //     }

    //     return response()->json([
    //         'message' => 'Login successful',
    //         'uid' => $user->id
    //     ]);
    // }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    // 5️⃣ Get user
    // public function getUser($uid)
    // {
    //     $user = User::find($uid);

    //     if (!$user) {
    //         return response()->json(['message' => 'User not found'], 404);
    //     }

    //     return response()->json($user);
    // }

    // 5️⃣ Get logged-in user (JWT based)
    public function getProfile()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        return response()->json($user);
    }
}

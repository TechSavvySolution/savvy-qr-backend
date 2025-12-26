<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // 1️⃣ Check unique username
    public function isUniqueUser($username)
    {
        $exists = User::where('username', $username)->exists();

        return response()->json([
            'status'  => !$exists,
            'message' => $exists ? 'Username already exists' : 'Username is available',
            'data'    => ['unique' => !$exists]
        ]);
    }

    // 2️⃣ Register
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users,username',
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8'
        ]);

        $user = User::create([
            'username' => $request->username,
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'User registered successfully',
            'data'    => [
                'username' => $user->username,
                'email'    => $user->email
            ]
        ], 201);
    }

    // 3️⃣ Login (JWT)
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:8',
        ]);

        if (! $token = auth()->attempt($credentials)) {
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
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    // 4️⃣ Complete profile (JWT)
    public function completeProfile(Request $request)
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
                'data' => null
            ], 401);
        }

        $request->validate([
            'phone'  => 'required|string|max:255',
            'dob'    => 'required|date',
            'gender' => 'required|in:Male,Female',
            'city'   => 'required|string|max:255',
            'bio'    => 'nullable|string|max:255',
            'profile_pic' => 'nullable|string|max:255' // image handling baad me “We store only the image path in database; actual file upload is handled separately.”
        ]);

        $user->update($request->only([
            'phone','dob','gender','city','bio','profile_pic'
        ]));

        return response()->json([
            'status'  => true,
            'message' => 'Profile updated successfully',
            'data'    => $user
        ]);
    }



       //5️⃣ Get logged-in user (JWT)
    
    public function getProfile()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated',
                'data'    => null
            ], 401);
        }

        return response()->json([
            'status'  => true,
            'message' => 'User profile fetched',
            'data'    => $user
        ]);
    }

    // 6️⃣ Logout
    public function logout()
    {
        auth()->logout();

        return response()->json([
            'status'  => true,
            'message' => 'Logout successful',
            'data'    => null
        ]);
    }
}

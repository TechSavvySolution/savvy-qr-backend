<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth; // 🟢 Import JWT Facade

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validate Input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 400);
        }

        // 2. Check User
        $user = User::where('email', $request->email)->first();

        // 3. Check Password manually
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false, 
                'message' => 'Invalid credentials'
            ], 401);
        }

        // 4. Check Role (Security)
        if ($user->role !== 'admin') { 
            return response()->json([
                'status' => false, 
                'message' => 'Access Denied: You are not an Admin.'
            ], 403);
        }

        // 🟢 5. Generate Token using JWT (The "Manual" Way)
        // Instead of $user->createToken(), we use JWTAuth::fromUser
        try {
            if (! $token = JWTAuth::fromUser($user)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Welcome back, Admin!',
            'data' => [
                'user' => $user,
                'token' => $token, // This is your JWT token
                'type' => 'bearer',
            ]
        ]);
    }
}
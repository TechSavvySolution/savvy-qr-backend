<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Helpers\TokenHelper; 

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validate Input
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 400);
        }

        // 2. Check User
        $user = User::where('email', $request->email)->first();

        // 3. Check Password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => false, 
                'message' => 'Invalid credentials'
            ], 401);
        }

        // 4. Check Admin Permissions
        // 🟢 OPTION 1: Database Role Check (Best Practice)
        // Make sure your 'users' table has a 'role' column!
        if ($user->role !== 'admin') { 
             return response()->json([
                'status'  => false, 
                'message' => 'Access Denied: You do not have admin permissions.'
            ], 403);
        }

        /* // 🟡 OPTION 2: Hardcoded Email Check (If you don't have a role column yet)
        // Uncomment this block and delete the block above if you want a quick fix.
        
        $adminEmails = ['admin@savvyqr.com', 'sharik@gmail.com']; // Add your email here
        if (!in_array($user->email, $adminEmails)) {
            return response()->json([
                'status'  => false, 
                'message' => 'Access Denied: You are not an Admin.'
            ], 403);
        }
        */

        // 5. Generate Token
        $token = TokenHelper::encode($user);

        return response()->json([
            'status'  => true,
            'message' => 'Welcome back, Admin!',
            'data'    => [
                'user'       => $user,
                'token'      => $token,
                'token_type' => 'Bearer' // 🟢 Added for Frontend compatibility
            ]
        ]);
    }
}
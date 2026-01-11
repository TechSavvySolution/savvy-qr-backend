<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Helpers\TokenHelper;

class UserController extends Controller
{
    // 1️⃣ Check Unique Username
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
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users,username',
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 400);
        }

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
                'id'       => $user->id,
                'username' => $user->username,
                'email'    => $user->email
            ]
        ], 201);
    }

    // 3️⃣ Login
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
                'token' => TokenHelper::encode($user),
                'user'  => $user
            ]
        ]);
    }

    // 4️⃣ Get Profile
    public function getProfile(Request $request)
    {
        return response()->json([
            'status'  => true,
            'message' => 'User profile fetched',
            'data'    => $request->auth_user
        ]);
    }

    // 5️⃣ Complete Profile (Strict Validation)
    public function completeProfile(Request $request)
    {
        if (!$request->auth_user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        // 🟢 FIXED: Fetch Fresh User Model to ensure save() works
        $user = User::find($request->auth_user->id);

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        $request->validate([
            'phone'       => 'required|digits_between:10,15',
            'dob'         => 'required|date|before:today',
            'gender'      => 'required|in:Male,Female',
            'city'        => 'required|string|max:255',
            'bio'         => 'nullable|string|max:500',
            'profile_pic' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120'
        ]);

        // Handle Image
        if ($request->hasFile('profile_pic')) {
            // Delete old if exists
            if ($user->profile_pic) {
                $oldPath = str_replace(asset('storage/'), '', $user->profile_pic);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('profile_pic')->store('profile', 'public');
            $user->profile_pic = asset('storage/' . $path); // 🟢 Standardized to full URL
        }

        $user->phone  = $request->phone;
        $user->dob    = $request->dob;
        $user->gender = $request->gender;
        $user->city   = $request->city;
        $user->bio    = $request->bio;
        
        $user->save();
        
        return response()->json([
            'status'  => true,
            'message' => 'Profile completed successfully',
            'data'    => $user
        ]);
    }

    // 6️⃣ Get User By ID
    public function getUserById($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'User found',
            'data'    => $user
        ]);
    }

    // 7️⃣ Logout
    public function logout()
    {
        return response()->json([
            'status'  => true,
            'message' => 'Logout successful',
            'data'    => null
        ]);
    }

    // 8️⃣ Update Profile (Flexible Update)
    public function updateProfile(Request $request)
    {
        // 🟢 FIXED: Ensure we have the Model, not just the Middleware Object
        $user = User::find($request->auth_user->id);

        if (!$user) return response()->json(['status' => false, 'message' => 'User not found'], 404);

        $validator = Validator::make($request->all(), [
            'name'        => 'nullable|string|max:255',
            'phone'       => 'nullable|string|max:15',
            'bio'         => 'nullable|string|max:500',
            'email'       => 'nullable|email|unique:users,email,' . $user->id,
            'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048' // 🟢 Changed 'avatar' to 'profile_pic'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 400);
        }

        // Handle Image Upload
        if ($request->hasFile('profile_pic')) {
             if ($user->profile_pic) {
                // Try to clean up URL to get relative path for deletion
                $oldPath = str_replace(asset('storage/'), '', $user->profile_pic); 
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('profile_pic')->store('profile', 'public');
            $user->profile_pic = asset('storage/' . $path); // 🟢 Standardized to 'profile_pic'
        }

        if ($request->has('name'))  $user->name = $request->name;
        if ($request->has('phone')) $user->phone = $request->phone;
        if ($request->has('bio'))   $user->bio = $request->bio;
        if ($request->has('email')) $user->email = $request->email;

        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'Profile updated successfully!',
            'data'    => $user
        ]);
    }
}
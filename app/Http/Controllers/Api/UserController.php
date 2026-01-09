<?php
 
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Helpers\TokenHelper;

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
            'name'     => 'required |string|max:255',
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
            'data'    => 
             [
                'id'       => $user->id,
                'username' => $user->username,
                'email'    => $user->email
              ]
        ] ,201 );
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

        // 🟢 HARDCODED ADMIN CHECK
        // If the email is YOUR admin email, they get the 'admin' role.
        // Everyone else gets 'user'.
        // $role = ($user->email === 'admin@gmail.com') ? 'admin' : 'user';

        // $role = 'user'; // Default
        // if ($user->email === 'admin@gmail.com') {
        //     $role = 'admin';
        // }

        // return response()->json([
        //     'status'  => true,
        //     'message' => 'Login successful',
        //     'token'   => TokenHelper::encode($user),
        //     'user'    => [
        //         'id'          => $user->id,
        //         'name'        => $user->name,
        //         'email'       => $user->email,
        //         'role'        => $role, // 👈 Sending the calculated role
        //         'profile_pic' => $user->profile_pic
        //     ]
        // ]);

        return response()->json([
            'status'  => true,
            'message' => 'Login successful',
            'data'    => [
                'token' => TokenHelper::encode($user),
                'user'  => $user
            ]
        ]);
    }

     /* ===============================
       4️⃣ Get logged-in user profile (PROTECTED)
       Middleware already verified token
    =============================== */
    public function getProfile(Request $request)
    {
        return response()->json([
            'status'  => true,
            'message' => 'User profile fetched',
            'data'    => $request->auth_user
        ]);
    }


     /* ===============================
       5️⃣ Complete / Update profile (PROTECTED)
       Image update + old delete
    =============================== */

  public function completeProfile(Request $request)
    {
        // auth_user already verified by middleware
        // $user = $request->auth_user;
        // 1. Check if auth_user exists from middleware
        if (!$request->auth_user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized: User not found or token invalid',
                'data' => null
            ], 401);
        }

        // 2. Ensure we have a real User Model (required for ->save())
        // If middleware passed an ID or array, we find the model here.
        $userId = $request->auth_user->id ?? $request->auth_user['id'] ?? null;
        $user = User::find($userId);


        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User account not found in database',
                'data' => null 
            ], 404);
        }
        $request->validate([
            'phone'       => 'required|digits_between:10,15',
            'dob'         => 'required|date|before:today',
            'gender'      => 'required|in:Male,Female',
            'city'        => 'required|string|max:255',
            'bio'         => 'nullable|string|max:500',
            'profile_pic' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120'
        ]);

        // 🖼 Image upload + old delete
        if ($request->hasFile('profile_pic')) {

            if ($user->profile_pic && Storage::disk('public')->exists(
                str_replace('storage/', '', $user->profile_pic)
            )) {
                Storage::disk('public')->delete(
                    str_replace('storage/', '', $user->profile_pic)
                );
            }

            $image = $request->file('profile_pic');
            $fileName = 'user_' . $user->id . '_' . time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('profile', $fileName, 'public');

            $user->profile_pic = 'storage/profile/' . $fileName;
        }

        // Other fields
        $user->phone  = $request->phone;
        $user->dob    = $request->dob;
        $user->gender = $request->gender;
        $user->city   = $request->city;
        $user->bio    = $request->bio;
        
        $user->save();
        
        return response()->json([
            'status'  => true,
            'message' => 'Profile updated successfully',
            'data'    => $user
        ]);
    }

       /* ===============================
       6️⃣ Find user by ID (PROTECTED)
       Why protected? Security.
    =============================== */
    public function getUserById(Request $request, $id)
    {
        // auth_user already verified by middleware
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not found',
                'data'    => null
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'User found',
            'data'    => $user
        ]);
    }


    /* ===============================
       7️⃣ Logout (PROTECTED)
       Stateless token → client side logout
    =============================== */
    public function logout(Request $request)
    {
        return response()->json([
            'status'  => true,
            'message' => 'Logout successful',
            'data'    => null
        ]);
    }

    // 8 UPDATE PROFILE (Handle Name, Bio, Phone, Email & Avatar)
    public function updateProfile(Request $request)
    {
        $user = $request->auth_user; // Get User from Middleware

        // 1. Validation
        $validator = Validator::make($request->all(), [
            'name'  => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:15',
            'bio'   => 'nullable|string|max:500',
            // Special Rule: Check unique email, but IGNORE the current user's own email
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'avatar'=> 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 400);
        }

        // 2. Handle Image Upload (If a new photo is sent)
        if ($request->hasFile('avatar')) {
            // A. (Optional) You could delete the old image here to save space
            
            // B. Upload new image
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = asset('storage/' . $path);
        }

        // 3. Update Text Fields (Only if they are sent)
        if ($request->has('name'))  $user->name = $request->name;
        if ($request->has('phone')) $user->phone = $request->phone;
        if ($request->has('bio'))   $user->bio = $request->bio;
        if ($request->has('email')) $user->email = $request->email;

        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully!',
            'data' => $user
        ]);
    }
}

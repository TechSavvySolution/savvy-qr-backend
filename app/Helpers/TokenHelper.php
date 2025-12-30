<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;

class TokenHelper
{
    // ✅ ENCODE: We now encrypt the data so no one can read or change it
    public static function encode($user)
    {
        $payload = [
            'user_id' => $user->id,
            'exp'     => time() + 3600 // 1 hour
        ];

        // Using Laravel's built-in Encryption (AES-256-CBC)
        // This generates a secure, unreadable string.
        return Crypt::encrypt($payload);
    }

    // ✅ DECODE: We decrypt it securely
    public static function decode($token)
    {
        try {
            // 1. Try to decrypt. If tampered, this throws an exception.
            $data = Crypt::decrypt($token);

            // 2. Check Expiry
            if ($data['exp'] < time()) {
                return [    
                    'status'  => false,
                    'message' => 'Token expired'
                ];
            }

            // 3. Find User
            $user = User::find($data['user_id']);

            if (!$user) {
                return [
                    'status'  => false,
                    'message' => 'User not found'
                ];
            }

            return [
                'status' => true,
                'data'   => $user
            ];

        } catch (\Exception $e) {
            // If someone changed the token, Decrypt fails instantly.
            return [
                'status'  => false,
                'message' => 'Invalid token'
            ];
        }
    }
}
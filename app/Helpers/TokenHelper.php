<?php

namespace App\Helpers;

use App\Models\User;

class TokenHelper
{
    public static function encode($user)
    {
        $payload = [
            'user_id' => $user->id,
            'exp' => time() + 3600 // 1 hour
        ];

        return base64_encode(json_encode($payload));
    }

    public static function decode($token)
    {
        $data = json_decode(base64_decode($token), true);

        if (!$data) {
            return [
                'status' => false,
                'message' => 'Invalid token format'
            ];
        }

        if ($data['exp'] < time()) {
            return [
                'status' => false,
                'message' => 'Token expired'
            ];
        }

        $user = User::find($data['user_id']);

        if (!$user) {
            return [
                'status' => false,
                'message' => 'User not found'
            ];
        }

        return [
            'status' => true,
            'data' => $user
        ];
    }
}

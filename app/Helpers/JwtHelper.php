<?php

namespace App\Helpers;

use App\Models\User;

// class JwtHelper
// {
//     public static function generateToken(User $user)
//     {
//         $header = base64_encode(json_encode([
//             'alg' => 'HS256',
//             'typ' => 'JWT'
//         ]));

//         $payload = base64_encode(json_encode([
//             'user_id' => $user->id,
//             'email'   => $user->email,
//             'iat'     => time(),
//             'exp'     => time() + (60 * 60) // 1 hour
//         ]));

//         $signature = hash_hmac(
//             'sha256',
//             $header . "." . $payload,
//             env('JWT_SECRET'),
//             true
//         );

//         return $header . "." . $payload . "." . base64_encode($signature);
//     }

//     public static function verifyToken($token)
//     {
//         try {
//             [$header, $payload, $signature] = explode('.', $token);

//             $expected = base64_encode(hash_hmac(
//                 'sha256',
//                 $header . "." . $payload,
//                 env('JWT_SECRET'),
//                 true
//             ));

//             if (!hash_equals($expected, $signature)) {
//                 return null;
//             }

//             $data = json_decode(base64_decode($payload), true);

//             if ($data['exp'] < time()) {
//                 return null;
//             }

//             return User::find($data['user_id']);
//         } catch (\Exception $e) {
//             return null;
//         }
//     }
// }

class JwtHelper
{
    public static function generateToken($user)
    {
        $payload = [
            'user_id' => $user->id,
            'exp' => time() + 3600
        ];

        return base64_encode(json_encode($payload));
    }

    public static function verifyToken($token)
    {
        $data = json_decode(base64_decode($token), true);

        if (!$data || !isset($data['exp']) || $data['exp'] < time()) {
            return null;
        }

        return User::find($data['user_id']);
    }
}
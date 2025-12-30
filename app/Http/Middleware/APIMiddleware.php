<?php

namespace App\Http\Middleware;

use App\Helpers\TokenHelper;
use Closure;
use Illuminate\Http\Request;

class APIMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // 1️⃣ Login / register ko skip
        if ($request->is('api/user/login') || $request->is('api/user/register')) {
            return $next($request);
        }

        // 2️⃣ Authorization header
        $token = $request->header('Authorization');
        $token = str_replace('Bearer ', '', $token ?? '');

        if (!$token) {
            return response()->json([
                'status' => false,
                'message' => 'Token missing',
                'data' => null
            ], 401);
        }

        // 3️⃣ Token verify
        $result = TokenHelper::decode($token);

        if (!$result['status']) {
            return response()->json([
                'status' => false,
                'message' => $result['message'],
                'data' => null
            ], 401);
        }

        // 4️⃣ Auth user attach
        $request->merge(['auth_user' => $result['data']]);

        return $next($request);
    }
}

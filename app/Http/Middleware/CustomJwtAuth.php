<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\JwtHelper;

class CustomJwtAuth
{
    public function handle(Request $request, Closure $next)
    {
        $auth = $request->header('Authorization');

        if (!$auth || !str_starts_with($auth, 'Bearer ')) {
            return response()->json([
                'status'  => false,
                'message' => 'Token missing',
                'data'    => null
            ], 401);
        }

        $token = str_replace('Bearer ', '', $auth);
        $user  = JwtHelper::verifyToken($token);

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid or expired token',
                'data'    => null
            ], 401);
        }

        // attach user to request (CUSTOM way)
        $request->attributes->set('auth_user', $user);

        return $next($request);
    }
}

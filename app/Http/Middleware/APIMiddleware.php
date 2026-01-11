<?php

namespace App\Http\Middleware;

use App\Helpers\TokenHelper;
use App\Models\User; // 🟢 Import User Model
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // 🟢 Import Auth Facade

class APIMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Optional: Set timezone (Usually done in config/app.php, but fine here)
        date_default_timezone_set('Asia/Kolkata');

        /* 1️⃣ SKIP LOGIN/REGISTER 
           (Technically not needed if you apply middleware in api.php groups, 
           but we keep it just in case you apply it globally)
        */
        if ($request->is('api/user/login') || $request->is('api/user/register')) {
            return $next($request);
        }

        // 2️⃣ Get Token
        $token = $request->header('Authorization');
        $token = str_replace('Bearer ', '', $token ?? '');

        if (!$token) {
            return response()->json([
                'status'  => false,
                'message' => 'Token missing (Authorization Header required)',
                'data'    => null
            ], 401);
        }

        // 3️⃣ Verify Token
        $result = TokenHelper::decode($token);

        if (!$result['status']) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid or Expired Token',
                'data'    => null
            ], 401);
        }

        // 🟢 4️⃣ CRITICAL FIX: Load Real User & Login via Auth Facade
        // This ensures auth()->id() works in your Controllers
        
        $userId = $result['data']->id ?? $result['data']['id'] ?? null;
        
        if ($userId) {
            // Fetch fresh user from DB (Secure: ensures user wasn't deleted)
            $user = User::find($userId); 

            if ($user) {
                // A. Enable auth()->user() and auth()->id() helpers
                Auth::login($user); 

                // B. Keep your custom request variable (for backward compatibility)
                $request->merge(['auth_user' => $user]);
            } else {
                return response()->json(['status' => false, 'message' => 'User not found'], 401);
            }
        } else {
             return response()->json(['status' => false, 'message' => 'Invalid Token Data'], 401);
        }

        return $next($request);
    }
}
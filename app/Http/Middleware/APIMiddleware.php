<?php

namespace App\Http\Middleware;

use App\Helpers\TokenHelper;
use App\Models\User; // ✅ Added: To find the real user
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log; // ✅ Added: For Senior's Logs
use Illuminate\Support\Facades\Auth; // ✅ Added: To make Auth::user() work

class APIMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1️⃣ Set Timezone
        date_default_timezone_set('Asia/Kolkata');

        // 2️⃣ Skip Authentication for Public Routes
        // I combined your specific routes with the Senior's generic ones.
        if ($request->is('api/user/login') || 
            $request->is('api/user/register') || 
            $request->is('api/admin/login') || 
            $request->is('api/auth/*')) {
            return $next($request);
        }

        // 3️⃣ Extract Token
        $token = $request->header('Authorization');
        $token = str_replace('Bearer ', '', $token ?? '');

        // Check if token exists
        if (!$token || empty(trim($token))) {
            return response()->json([
                'status' => false,
                'message' => 'Authentication token not provided',
                'data' => null
            ], 401);
        }

        try {
            // 4️⃣ Decode and Validate Token
            $result = TokenHelper::decode($token);

            // Check if token is valid (Senior's Logging Logic)
            if (!$result['status']) {
                Log::warning('Invalid token attempt', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'message' => $result['message'] ?? 'Unknown error'
                ]);

                return response()->json([
                    'status' => false,
                    'message' => $result['message'] ?? 'Invalid or expired token',
                    'data' => null
                ], 401);
            }

            // 5️⃣ LOAD REAL USER (Crucial Fix for your App)
            // We take the data from the token, find the user in DB, and set them as "Logged In" for this request.
            
            $data = $result['data'];
            // Handle if data is Array or Object
            $userId = is_object($data) ? $data->id : ($data['id'] ?? null);

            if ($userId) {
                $user = User::find($userId);

                if ($user) {
                    // ✅ THIS IS THE MAGIC LINE:
                    // It lets you use auth()->user() in your controllers.
                    Auth::setUser($user);

                    // Senior's requirement: Merge user into request
                    $request->merge(['auth_user' => $user]);

                    // Log successful authentication (Senior's Debug Logic)
                    if (config('app.debug')) {
                        Log::info('Authenticated request', [
                            'user_id' => $user->id,
                            'endpoint' => $request->path()
                        ]);
                    }
                } else {
                    return response()->json(['status' => false, 'message' => 'User not found'], 401);
                }
            } else {
                 return response()->json(['status' => false, 'message' => 'Invalid Token Data'], 401);
            }

            return $next($request);

        } catch (\Exception $e) {
            // 6️⃣ Error Handling (Senior's Logic)
            Log::error('Authentication middleware error', [
                'message' => $e->getMessage(),
                'ip' => $request->ip(),
                'endpoint' => $request->path()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Authentication failed',
                'data' => null,
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
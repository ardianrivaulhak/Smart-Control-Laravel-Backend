<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }
    public function handle($request, Closure $next, ...$guard)
    {
        try {
            //code...
            $token = Str::replaceFirst('Bearer ', '', $request->header('Authorization'));

            if (!$token) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            //throw $th;
            return response()->json([
                'message' => "Unauthorized",
                "error" => $e->getMessage()
            ], 401);
        }
        return $next($request);
    }
}

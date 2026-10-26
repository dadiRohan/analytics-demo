<?php

namespace App\Http\Middleware;

use Closure;

class VerifyAnalyticsToken
{
    public function handle($request, Closure $next, string $type)
    {
        $token = $request->bearerToken() ?? $request->query('token');
        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($type === 'ingest') {
            $valid = hash_equals((string) config('analytics.ingest_token'), $token)
                  || hash_equals((string) config('analytics.public_token'), $token);
        } elseif ($type === 'read') {
            $valid = hash_equals((string) config('analytics.read_token'), $token);
        } else {
            $valid = false;
        }

        return $valid ? $next($request) : response()->json(['error' => 'Unauthorized'], 401);
    }
}
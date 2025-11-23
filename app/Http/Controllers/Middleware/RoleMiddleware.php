<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $required)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $map = ['admin' => 1, 'shop' => 2];
        $expected = $map[$required] ?? null;

        if ($expected === null || (int)$user->type_user !== (int)$expected) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
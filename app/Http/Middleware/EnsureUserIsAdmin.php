<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle($request, \Closure $next)
    {
        $user = $request->user();
        $isAdmin = $user && $user->roles()->where('name', 'admin')->exists();

        if (!$isAdmin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        return $next($request);
    }
}

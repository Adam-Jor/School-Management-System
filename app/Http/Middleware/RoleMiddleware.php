<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request and verify user role (admin / teacher / student).
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! empty($roles) && ! in_array($user->role, $roles, true)) {
            abort(403, 'Akses ditolak. Hanya pengguna dengan role '.implode(', ', $roles).' yang diizinkan melakukan aksi ini.');
        }

        return $next($request);
    }
}

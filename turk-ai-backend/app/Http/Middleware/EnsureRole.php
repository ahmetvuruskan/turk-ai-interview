<?php

namespace App\Http\Middleware;

use App\Exceptions\DomainException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();
        $roles = explode('|', $role);

        if (!$user || !in_array( $user->role?->value, $roles, true)) {
            throw new DomainException('general.forbidden', Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PaginationParameters
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */

    private const DEFAULT_PAGE_SIZE = 10;
    private const MAX_PAGE_SIZE = 100;

    public function handle(Request $request, Closure $next)
    {
        $page = max(1, (int)$request->query('page', 1));
        $perPage = (int)$request->query('perPage', self::DEFAULT_PAGE_SIZE);
        $perPage = max(self::DEFAULT_PAGE_SIZE, min($perPage, self::MAX_PAGE_SIZE));
        $search = $request->query('search');

        $request->merge([
            'page' => $page,
            'perPage' => $perPage,
            'search' => $search
        ]);

        return $next($request);
    }
}

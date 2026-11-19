<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SearchVisibility
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        if ($request->isMethod('get') && !$request->routeIs('admin.*') && $response instanceof \Illuminate\Http\Response && $response->original instanceof \Illuminate\View\View) {
            foreach ($response->original->getData() as $value) {
                if ($value instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                    abort_if($request->integer('page', 1) < 1 || $value->currentPage() > $value->lastPage(), 404);
                }
            }
        }
        if ($request->routeIs('admin.*') || $response->getStatusCode() >= 400) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }
        if ($request->routeIs('admin.*')) $response->headers->set('Cache-Control', 'private, no-store');
        return $response;
    }
}

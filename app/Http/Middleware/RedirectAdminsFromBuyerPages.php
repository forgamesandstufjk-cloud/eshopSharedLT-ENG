<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectAdminsFromBuyerPages
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->role === 'admin') {
            return redirect()->route('admin.reported-listings.index');
        }

        return $next($request);
    }
}
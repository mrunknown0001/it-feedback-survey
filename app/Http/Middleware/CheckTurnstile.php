<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTurnstile
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('turnstile_verified')) {
            return redirect()->route('turnstile.challenge');
        }

        return $next($request);
    }
}

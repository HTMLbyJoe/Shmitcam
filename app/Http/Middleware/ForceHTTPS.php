<?php

namespace App\Http\Middleware;

use Closure;

class ForceHTTPS
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->secure() && env('FORCE_HTTPS')) {
            $url = $request->fullUrl();
            $secure_url = preg_replace('/^http:/i', 'https:', $url);

           return redirect($secure_url);
        }

        return $next($request);
    }
}

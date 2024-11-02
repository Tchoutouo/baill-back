<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    // /**
    //  * Handle an incoming request.
    //  *
    //  * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
    //  * @return mixed
    //  */
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // return $next($request);
        return $next($request)
                ->headers('Access-Control-Allow-Origin', '*')
                ->headers('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->headers('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}

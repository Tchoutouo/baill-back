<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class DashboardAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::user()->profil_id == "2" || Auth::user()->profil_id == "1") {
            return $next($request);
        }
        else{
            // dd("bien");
            return response()->json([
                "success"=>false,
                "message"=>"Access non autorisé...",
            ]);
        }
    }
    
}

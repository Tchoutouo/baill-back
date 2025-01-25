<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class DashboardAdvertiser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::user()->profil_id == "3") {
            return $next($request);
        }
        else{
            // dd('bieeeee');
            return response()->json([
                "success"=>false,
                "message"=>"Access non autorisé...",
            ]);
        }
    }
}

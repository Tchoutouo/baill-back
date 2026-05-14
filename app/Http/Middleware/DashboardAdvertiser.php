<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Enums\ProfilCode;

class DashboardAdvertiser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ((int) Auth::user()->profil_id === ProfilCode::Advertiser->value) {
            return $next($request);
        }
        else{
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé.',
            ], 403);
        }
    }
}

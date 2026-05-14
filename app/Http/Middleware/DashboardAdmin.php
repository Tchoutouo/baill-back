<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Enums\ProfilCode;

class DashboardAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $profilId = (int) Auth::user()->profil_id;
        if ($profilId === ProfilCode::Admin->value || $profilId === ProfilCode::SuperAdmin->value) {
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

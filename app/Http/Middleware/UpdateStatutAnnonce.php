<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Annonce;

class UpdateStatutAnnonce
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $annonces = Annonce::get();

        foreach ($annonces as $annonce) {
            if( $annonce->updated_at )
            {
                $annonce->status = 1;
                $annonce->save();
            }
        }
        return $next($request);
    }
}

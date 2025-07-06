<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Annonce;
use Illuminate\Support\Carbon;

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
            if( $annonce->expiration_date)
            {
                if(now()->gt($annonce->expiration_date))
                {
                    $annonce->status = 0;
                    $annonce->save();
                }
            }
        }
        return $next($request);
    }
}

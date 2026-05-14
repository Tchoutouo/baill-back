<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyFreemopaySignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('services.freemopay.webhook_secret');

        if ($secret) {
            $signature = $request->header('X-Freemopay-Signature', '');
            $expected  = hash_hmac('sha256', $request->getContent(), $secret);

            if (!hash_equals($expected, $signature)) {
                return response()->json(['error' => 'Invalid webhook signature.'], 401);
            }
        }

        return $next($request);
    }
}

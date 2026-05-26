<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class EmailVerificationController extends Controller
{
    /**
     * Activate an account by verifying the email token.
     * Token is stored in remember_token; email is a path parameter.
     */
    public function verify(string $token, string $email): JsonResponse
    {
        try {
            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json(['success' => false, 'reason' => 'not_found'], 404);
            }

            // Already verified
            if (!is_null($user->email_verified_at)) {
                return response()->json(['success' => true, 'already_verified' => true]);
            }

            if (!Hash::check($token, $user->remember_token)) {
                return response()->json(['success' => false, 'reason' => 'invalid_token'], 400);
            }

            // Token expires after 24 hours — check user creation date
            if (Carbon::parse($user->created_at)->addHours(24)->isPast()) {
                return response()->json(['success' => false, 'reason' => 'expired'], 410);
            }

            $user->email_verified_at = now();
            $user->status            = 1;
            $user->remember_token    = null;
            $user->save();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('EmailVerificationController::verify — ' . $e->getMessage());
            return response()->json(['success' => false, 'reason' => 'server_error'], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class NewPasswordController extends Controller
{
    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'token' => ['required'],
                'email' => ['required', 'email'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);
            // Here we will attempt to reset the user's password. If it is successful we
            // will update the password on an actual user model and persist it to the
            // database. Otherwise we will parse the error and return the response.
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user) use ($request) {
                    $user->forceFill([
                        'password' => Hash::make($request->password),
                        'remember_token' => Str::random(60),
                    ])->save();
    
                    event(new PasswordReset($user));
                }
            );
    
            if ($status != Password::PASSWORD_RESET) {
                throw ValidationException::withMessages([
                    'email' => [__($status)],
                    'success' => false,
                    'message' => "Réinitialisation du mot de passe échoué...",
                ]);
            }
    
            return response()->json([
                'success' => true,
                'message' => "Réinitialisation du mot de passe réussi...",
                'status' => __($status),
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de forget password : ' . $e->getMessage());
            return response()->json([
                    'success' => false,
                    'message' => "Erreur lors de la verification",
                ]
            );
        }
    }

    public function verifyToken($token, $email)
    {

        try {
            $record = DB::table('password_reset_tokens')
                ->where('email', $email)
                ->first();
    
            if (!$record || !Hash::check($token, $record->token)) {
                return response()->json(['error' => 'Token invalide.'], 404);
            }
    
            if (Carbon::parse($record->created_at)->addMinutes(config('auth.passwords.users.expire'))->isPast()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Token expiré.',
                ], 410);
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Token valide.'
            ], 200);

        } catch (\Exception $e) {
            
            Log::error('Erreur lors de la verification : ' . $e->getMessage());
            return response()->json([
                    'success' => false,
                    'message' => "Erreur lors de la verification",
                ]
            );
        }
    }
}

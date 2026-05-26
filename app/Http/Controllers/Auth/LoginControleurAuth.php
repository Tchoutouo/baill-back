<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Enums\ProfilCode;
use App\Models\Profil;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Exception;
use Hamcrest\Type\IsBoolean;

class LoginControleurAuth extends Controller
{
    //
    public function verifylog($user){
        if ($user->status) {
            // connecter ce user
            Auth::login($user);
            // Création du token d'API pour l'utilisateur
            $token = $user->createToken('token_name')->plainTextToken;

            $profil = Profil::where('id',$user->profil_id)->first();
            $user->profil_name = $profil->name;
            $user->profil_code = $profil->code;

            $redirectUrl = (int) $user->profil_id === ProfilCode::Admin->value
                ? route('dashboard_admin', ['id' => $user])
                : route('dashboard_advertiser', ['id' => $user]);

            return response()->json([
                'success'      => true,
                'data'         => $user,
                'redirect_url' => $redirectUrl,
            ])->cookie(
                'auth_token',
                $token,
                60 * 24 * 7,
                '/',
                null,
                config('session.secure'),
                true,
                false,
                'Lax'
            );
        } elseif (is_null($user->email_verified_at)) {
            return response()->json([
                'success' => false,
                'reason'  => 'email_not_verified',
                'message' => "Veuillez vérifier votre adresse e-mail pour activer votre compte.",
            ]);
        } else {
            return response()->json([
                'success' => false,
                'reason'  => 'account_blocked',
                'message' => "Ce compte a été bloqué. Contactez le support.",
            ]);
        }
    }


    public function login(Request $request){

        try {
            $request->validate([
                'identifiant' => 'required',
                'password' => 'required|string|min:8'
            ],
            [
                'error' => 'Erreur...',
            ]);
            
            $user = User::where('email', $request->identifiant)->first();

            if (($user && Hash::check($request->password, $user->password))) {
                return $this->verifylog($user);
            }

            $user = User::where('whatsapp_number', $request->identifiant)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                return $this->verifylog($user);
            }

            return response()->json([
                'success' => false,
                'message' => 'Les informations d\'identification ne sont pas correctes.',
            ]);
           
        } catch (ValidationException $e) {
            // Récupérer les erreurs
            $errors = $e->validator->errors();

            // Retourner les erreurs en réponse JSON ou autre objet
            return response()->json([
                'success' => false,
                'message' => 'Erreur de connexion',
                'errors' => $errors
            ], 422);
        }
    }
}

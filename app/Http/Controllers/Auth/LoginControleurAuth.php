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

            
            //redirection en fonction du profil
            if ((int) $user->profil_id === ProfilCode::Admin->value) {
                
                return response()->json([
                    'success' => true,
                    'data' => $user,
                    'token' => $token,
                    'redirect_url' => route('dashboard_admin', ['id' => $user])
                ]);
            }else{
                return response()->json([
                    'success' => true,
                    'data' => $user,
                    'token' => $token,
                    'redirect_url' => route('dashboard_advertiser', ['id' => $user])
                ]);
            }
        }else{
            return response()->json([
                'success' => false,
                'message' => "Ce compte a été bloqué...",
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

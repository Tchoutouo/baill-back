<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Exception;

class LoginControleurAuth extends Controller
{
    //
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
                // connecter ce user
                Auth::login($user);
                // Création du token d'API pour l'utilisateur
                $token = $user->createToken('token_name')->plainTextToken;

                //redirection en fonction du profil
                if ($user->profil_id == "2") {
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
            }

            $user = User::where('whatsapp_number', $request->identifiant)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                Auth::login($user);
                // Création du token d'API pour l'utilisateur
                $token = $user->createToken('token_name')->plainTextToken;
                //redirection en fonction du profil
                if ($user->profil_id == "2") {
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

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
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
                return response()->json([
                    'success' => true,
                    'data' => $user,
                    'token' => $token,
                    'redirect_url' => route('dashboard', ['id' => $user->id])
                ]);
            }

            $user = User::where('whatsapp_number', $request->identifiant)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                Auth::login($user);
                // Création du token d'API pour l'utilisateur
                $token = $user->createToken('token_name')->plainTextToken;
                return response()->json([
                    'success' => true,
                    'data' => $user,
                    'token' => $token,
                    'redirect_url' => route('dashboard', ['id' => $user->id])
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Les informations d\'identification ne sont pas correctes.',
                'token' => 'Les informations d\'identification ne sont pas correctes.',
            ]);
           
        } catch (Exception $e) {
            return $e;
        }
    }
}

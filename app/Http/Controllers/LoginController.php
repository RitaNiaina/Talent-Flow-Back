<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'email_utilisateur' => 'required|email',
            'mot_passe' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Vérifier l'utilisateur
        $user = User::where('email_utilisateur', $request->email_utilisateur)->first();

        if (!$user || !Hash::check($request->mot_passe, $user->mot_passe)) {
            return response()->json(['message' => 'Email ou mot de passe incorrect'], 401);
        }

        // Générer un token d'API (Laravel Sanctum)
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UtilisateurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(
            User::with('role')->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Valider les données
        $validator = Validator::make($request->all(), [
            'nom_utilisateur' => 'required|string|max:255',
            'email_utilisateur' => 'required|email|unique:users,email_utilisateur',
            'mot_passe' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id',
           
        ]);

        // 2. Si la validation échoue, renvoyer les erreurs
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // 3. Créer l'utilisateur (mot de passe hashé automatiquement par le mutator)
        $utilisateur = User::create($validator->validated());

        return response()->json($utilisateur, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $utilisateur = User::with('role')->find($id);

        if (!$utilisateur) {
            return response()->json(['message' => 'utilisateur n existe pas'], 404);
        }

        return response()->json($utilisateur);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $utilisateur = User::find($id);

        if (!$utilisateur) {
            return response()->json(['message' => 'utilisateur introuvable'], 404);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'nom_utilisateur' => 'required|string|max:255',
            'email_utilisateur' => 'required|email',
            'role_id' => 'required|exists:roles,id',
            
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $utilisateur->update($validator->validated());

        return response()->json(['message' => 'utilisateur mis à jour avec succès', 'utilisateur' => $utilisateur]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $utilisateur = User::find($id);

        if (!$utilisateur) {
            return response()->json([
                'message' => 'utilisateur introuvable'
            ], 404);
        }

        $utilisateur->delete();

        return response()->json([
            'message' => 'utilisateur supprimé avec succès'
        ], 200);
    }

    public function changerMotDePasse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ancien_mot_passe' => 'required|string',
            'nouveau_mot_passe' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $utilisateur = auth()->user();

        if (!$utilisateur) {
            return response()->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        if (!Hash::check($request->ancien_mot_passe, $utilisateur->mot_passe)) {
            return response()->json(['message' => 'Ancien mot de passe incorrect'], 403);
        }

        $utilisateur->mot_passe = Hash::make($request->nouveau_mot_passe);
        $utilisateur->save();

        return response()->json(['message' => 'Mot de passe mis à jour avec succès']);
    }

    /**
     * Register method
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom_utilisateur' => 'required|string|max:255',
            'email_utilisateur' => 'required|email|unique:users,email_utilisateur',
            'mot_passe' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id',
            
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'nom_utilisateur' => $request->nom_utilisateur,
            'email_utilisateur' => $request->email_utilisateur,
            'mot_passe' => $request->mot_passe,
            'role_id' => $request->role_id,
            
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Utilisateur enregistré avec succès',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * Login method
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_utilisateur' => 'required|email',
            'mot_passe' => 'required|string|min:6',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
    
        $user = User::with('role')->where('email_utilisateur', $request->email_utilisateur)->first();
    
        if (!$user || !Hash::check($request->mot_passe, $user->mot_passe)) {
            return response()->json([
                'status' => false,
                'message' => 'Identifiants invalides',
            ], 401);
        }
    
        // Vérifier le rôle
        $role = $user->role->type_role ?? null;
    
        if (!in_array($role, ['Administrateur', 'Recruteur'])) {
            return response()->json([
                'status' => false,
                'message' => 'Vous devez être Administrateur ou Recruteur',
            ], 403); // 403 Forbidden
        }
    
        // Générer le token
        $token = $user->createToken('auth_token')->plainTextToken;
    
        // Redirection dynamique selon le rôle
        $redirectUrl = $role === 'Administrateur' ? '/dashboard' : '/recruteur/dashboard';
    
        return response()->json([
            'status' => true,
            'message' => 'Connexion réussie',
            'user' => [
                'id' => $user->id,
                'nom_utilisateur' => $user->nom_utilisateur,
                'email_utilisateur' => $user->email_utilisateur,
                'role' => $role,
            ],
            'redirect' => $redirectUrl,
            'token' => $token,
        ], 200);
    }
    
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie'
        ]);
    }


    public function loginCandidat(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email_utilisateur' => 'required|email',
        'mot_passe' => 'required|string|min:6',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    $user = User::with('role')->where('email_utilisateur', $request->email_utilisateur)->first();

    if (!$user || !Hash::check($request->mot_passe, $user->mot_passe)) {
        return response()->json([
            'status' => false,
            'message' => 'Identifiants invalides',
        ], 401);
    }

    // Vérifie bien que le rôle est "Candidat"
    if ($user->role->type_role !== 'Candidat') {
        return response()->json([
            'status' => false,
            'message' => 'Vous devez être un candidat pour accéder à cet espace.',
        ], 403);
    }

    // Création du token d'authentification
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'status' => true,
        'message' => 'Connexion réussie.',
        'user' => [
            'id' => $user->id,
            'nom_utilisateur' => $user->nom_utilisateur,
            'email_utilisateur' => $user->email_utilisateur,
            'role' => $user->role->type_role,
        ],
        'token' => $token,
    ], 200);
}

}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
class UtilisateurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return User::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Valider les données
       $validator = Validator::make($request->all(), [

            'nom_utilisateur'=> 'required|string|max:255',
            'email_utilisateur'=> 'required|email|unique:users,email_utilisateur',
            'mot_passe'=> 'required|string|min:6',
            'role_id'=> 'required|exists:roles,id',
    ]);

    // 2. Si la validation échoue, renvoyer les erreurs
    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors()
        ], 422);
    }

    // 3. Créer l'utilisateur si tout est bon
    $validated = $validator->validated();
    $validated['mot_passe'] = bcrypt($validated['mot_passe']);
    $utilisateur = User::create($validated);

    return response()->json($utilisateur, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $utilisateur = User::find($id);

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
            'nom_utilisateur'=> 'required|string|max:255',
            'email_utilisateur'=> 'required|email',
            'role_id'=> 'required|exists:roles,id',
        
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Mise à jour
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
        // 1. Validation des champs
        $validator = Validator::make($request->all(), [
            'ancien_mot_passe' => 'required|string',
            'nouveau_mot_passe' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Récupérer l'utilisateur connecté
        $utilisateur = auth()->user();

        if (!$utilisateur) {
            return response()->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        // 3. Vérifier si l'ancien mot de passe est correct
        if (!Hash::check($request->ancien_mot_passe, $utilisateur->mot_passe)) {
            return response()->json(['message' => 'Ancien mot de passe incorrect'], 403);
        }

        // 4. Mettre à jour le mot de passe
        $utilisateur->mot_passe = Hash::make($request->nouveau_mot_passe);
        $utilisateur->save();

        return response()->json(['message' => 'Mot de passe mis à jour avec succès']);
    }
}

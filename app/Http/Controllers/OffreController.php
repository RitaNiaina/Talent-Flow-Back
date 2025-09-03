<?php

namespace App\Http\Controllers;

use App\Models\Offre;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class OffreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(
            Offre::with(['recruteur', 'candidatures', 'tests'])->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Valider les données
       $validator = Validator::make($request->all(), [
            
            'titre_offre' => 'required|string|max:255',
            'description_offre' => 'required|string',
            'date_publication' => 'required|date',
            'date_expiration' => 'nullable|date|after_or_equal:date_publication',
            'statut_offre' => 'in:ouvert,fermé,en_attente',
            'recruteur_id' => 'required|exists:users,id',
    ]);

    // 2. Si la validation échoue, renvoyer les erreurs
    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors()
        ], 422);
    }

    // 3. Créer l'offre si tout est bon
    $offre = Offre::create($validator->validated());

    return response()->json($offre, 201);

    // Vérifier que le recruteur_id correspond bien à un utilisateur avec le rôle "recruteur"
    $utilisateur = User::find($request->recruteur_id);
    if ($utilisateur->role->nom !== 'recruteur') {
        return response()->json(['error' => 'Cet utilisateur n\'est pas un recruteur'], 403);
    }

    $offre = Offre::create($request->all());

    return response()->json($offre, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $offre = Offre::with(['recruteur', 'candidatures', 'tests'])->find($id);

        if (!$offre) {
        return response()->json(['message' => 'offre n existe pas'], 404);
        }

        return response()->json($offre);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $offre = Offre::find($id);

        if (!$offre) {
            return response()->json(['message' => 'Offre introuvable'], 404);
        }
    
        // Validation
        $validator = Validator::make($request->all(), [
            'titre_offre' => 'sometimes|required|string|max:255',
            'description_offre' => 'sometimes|required|string',
            'date_publication' => 'sometimes|required|date',
            'date_expiration' => 'nullable|date|after_or_equal:date_publication',
            'statut_offre' => 'in:ouvert,fermé,en_attente',
            
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // Mise à jour
        $offre->update($validator->validated());
    
        return response()->json(['message' => 'Offre mis à jour avec succès', 'offre' => $offre]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $offre = Offre::find($id);

    if (!$offre) {
        return response()->json([
            'message' => 'Offre introuvable'
        ], 404);
    }

    $offre->delete();

    return response()->json([
        'message' => 'Offre supprimé avec succès'
    ], 200);

    }
}

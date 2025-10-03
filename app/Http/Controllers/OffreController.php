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
            Offre::with(['recruteur', 'candidatures', 'tests', 'competences'])->get()
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
            'type_offre' => 'required|string|max:255',   
            'lieu_offre' => 'required|string|max:255',   
            'competences' => 'array', 
            'competences.*' => 'exists:competences,id' 
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Vérifier que le recruteur est bien un recruteur
        $utilisateur = User::find($data['recruteur_id']);
        if (!$utilisateur || $utilisateur->role->nom !== 'recruteur') {
            return response()->json(['error' => 'Cet utilisateur n\'est pas un recruteur'], 403);
        }

        // Créer l'offre
        $offre = Offre::create($data);

        // Attacher les compétences si fournies
        if (isset($data['competences'])) {
            $offre->competences()->attach($data['competences']);
        }

        return response()->json($offre->load('competences'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $offre = Offre::with(['recruteur', 'candidatures', 'tests', 'competences'])->find($id);

        if (!$offre) {
            return response()->json(['message' => 'Offre introuvable'], 404);
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

        $validator = Validator::make($request->all(), [
            'titre_offre' => 'sometimes|required|string|max:255',
            'description_offre' => 'sometimes|required|string',
            'date_publication' => 'sometimes|required|date',
            'date_expiration' => 'nullable|date|after_or_equal:date_publication',
            'statut_offre' => 'in:ouvert,fermé,en_attente',
            'type_offre' => 'sometimes|required|string|max:255',
            'lieu_offre' => 'sometimes|required|string|max:255',
            'competences' => 'array',
            'competences.*' => 'exists:competences,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Mise à jour de l'offre
        $offre->update($data);

        // Mise à jour des compétences si fournies
        if (isset($data['competences'])) {
            $offre->competences()->sync($data['competences']); 
        }

        return response()->json([
            'message' => 'Offre mise à jour avec succès',
            'offre' => $offre->load('competences')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $offre = Offre::find($id);

        if (!$offre) {
            return response()->json(['message' => 'Offre introuvable'], 404);
        }

        // Supprimer les relations pivot d'abord (facultatif car cascade)
        $offre->competences()->detach();

        $offre->delete();

        return response()->json(['message' => 'Offre supprimée avec succès'], 200);
    }
}

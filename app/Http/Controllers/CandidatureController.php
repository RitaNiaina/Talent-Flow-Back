<?php

namespace App\Http\Controllers;

use App\Models\Candidature;
use App\Models\User;
use App\Models\Offre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class CandidatureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $candidatures = Candidature::with([
        'candidat:id,nom_utilisateur',   // relation vers User modèle pour candidat
        'manager:id,nom_utilisateur',    // relation vers User modèle pour manager
        'offre:id,titre_offre'           // relation vers Offre modèle
    ])->get();

    // Transformer les données pour le front
    $result = $candidatures->map(function ($c) {
        return [
            'id' => $c->id,
            'date_postule' => $c->date_postule,
            'etat_candidature' => $c->etat_candidature,
            'note_candidature' => $c->note_candidature,
            'candidat_id' => $c->candidat_id,
            'candidat_nom' => $c->candidat->nom_utilisateur ?? null,
            'manager_id' => $c->manager_id,
            'manager_nom' => $c->manager->nom_utilisateur ?? null,
            'offre_id' => $c->offre_id,
            'offre_titre' => $c->offre->titre_offre ?? null,
        ];
    });
    return response()->json($result);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date_postule' => 'required|date',
            'etat_candidature' => 'required|in:en_attente,acceptee,refusee',
            'note_candidature' => 'nullable|integer',
            'candidat_id' => 'required|exists:users,id',
            'manager_id' => 'required|exists:users,id',
            'offre_id' => 'required|exists:offres,id',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $candidature = Candidature::create($validator->validated());
    
        // Charger aussi les relations pour renvoyer les infos complètes
        $candidature->load(['candidat', 'manager', 'offre']);
    
        return response()->json($candidature, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $candidature = Candidature::with([
            'candidat',
            'manager',
            'offre.recruteur',
            'offre.tests'
        ])->find($id);
    
        if (!$candidature) {
            return response()->json(['message' => 'candidature n existe pas'], 404);
        }
    
        return response()->json($candidature);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$id)
    {
        $candidature = Candidature::find($id);

        if (!$candidature) {
            return response()->json(['message' => 'candidature introuvable'], 404);
        }
    
        // Validation
        $validator = Validator::make($request->all(), [
            'date_postule' => 'sometimes|date',
            'etat_candidature' => 'sometimes|in:en_attente,acceptee,refusee',
            'note_candidature' => 'nullable|integer',
            'candidat_id' => 'sometimes|exists:users,id',
            'manager_id' => 'sometimes|exists:users,id',
            'offre_id' => 'sometimes|exists:offres,id',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // Mise à jour
        $candidature->update($validator->validated());
    
        return response()->json(['message' => 'candidature mis à jour avec succès', 'candidature' => $candidature]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $candidature = Candidature::find($id);

    if (!$candidature) {
        return response()->json([
            'message' => 'candidature introuvable'
        ], 404);
    }

    $candidature->delete();

    return response()->json([
        'message' => 'candidature supprimé avec succès'
    ], 200);
    
    
    }
}

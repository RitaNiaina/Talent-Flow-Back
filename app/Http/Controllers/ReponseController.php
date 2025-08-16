<?php

namespace App\Http\Controllers;

use App\Models\Reponse;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReponseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Reponse::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Valider les données
       $validator = Validator::make($request->all(), [
            
        'contenu_reponse' => 'required|string',
        'date_soumission' => 'required|date',
        'question_id' => 'required|exists:questions,id',
       ]);

      // 2. Si la validation échoue, renvoyer les erreurs
      if ($validator->fails()) {
      return response()->json([
        'errors' => $validator->errors()
      ], 422);
       }

       // 3. Créer l'offre si tout est bon
       $reponse = Test::create($validator->validated());

       return response()->json($reponse, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $reponse = Reponse::find($id);

        if (!$reponse) {
        return response()->json(['message' => 'reponse n existe pas'], 404);
        }
        return response()->json($reponse);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$id)
    {
        $reponse = Reponse::find($id);

        if (!$reponse) {
            return response()->json(['message' => 'reponse introuvable'], 404);
        }
    
        // Validation
        $validator = Validator::make($request->all(), [
            'contenu_reponse' => 'required|string',
            'date_soumission' => 'required|date',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // Mise à jour
        $reponse->update($validator->validated());
    
        return response()->json(['message' => 'reponse mis à jour avec succès', 'reponse' => $reponse]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $reponse = Reponse::find($id);

    if (!$reponse) {
        return response()->json([
            'message' => 'reponse introuvable'
        ], 404);
    }

    $reponse->delete();

    return response()->json([
        'message' => 'reponse supprimé avec succès'
    ], 200);
    
    }
}

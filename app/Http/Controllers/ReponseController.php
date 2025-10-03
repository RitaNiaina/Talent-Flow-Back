<?php

namespace App\Http\Controllers;

use App\Models\Reponse;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReponseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    try {
        $reponses = Reponse::with(['question', 'candidat'])->get(); 
        return response()->json($reponses);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'contenu_reponse' => 'required|string',
        'date_soumission' => 'required|date',
        'question_id' => 'required|exists:questions,id',
        'candidat_id' => 'nullable|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors()
        ], 422);
    }

    $reponse = Reponse::create($validator->validated());

    // Charger les relations question et candidat
    $reponse->load('question', 'candidat');

    return response()->json([
        'message' => 'Réponse créée avec succès',
        'reponse' => $reponse
    ], 201);
}



    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $reponse = Reponse::with('question')->find($id);

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
